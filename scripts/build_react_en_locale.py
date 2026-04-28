#!/usr/bin/env python3
"""
Build resources/js/locales/en.json from es.json.
Requires: pip install deep-translator
"""
from __future__ import annotations

import json
import re
import time
from pathlib import Path

from deep_translator import GoogleTranslator

ROOT = Path(__file__).resolve().parents[1]
ES_PATH = ROOT / "resources/js/locales/es.json"
EN_PATH = ROOT / "resources/js/locales/en.json"
BRAND = "Serralleria Solidària"

# Exact Spanish UI phrase → professional English
SP_TO_EN: dict[str, str] = {
    "Guardar": "Save",
    "Cancelar": "Cancel",
    "Eliminar": "Delete",
    "Editar": "Edit",
    "Crear": "Create",
    "Volver": "Back",
    "Detalle": "Details",
    "Buscar": "Search",
    "Confirmar": "Confirm",
    "Cerrar": "Close",
    "Sí": "Yes",
    "No": "No",
    "Menú": "Menu",
    "Ninguno": "None",
    "Aceptar": "Accept",
    "Recargar": "Reload",
    "Subir arriba": "Back to top",
    "Activo": "Active",
    "Tipo": "Type",
    "Cerrar sesión": "Log out",
    "Iniciar sesión": "Log in",
    "Registrarse": "Sign up",
    "Estado": "Status",
    "Correo electrónico": "Email",
    "Contraseña": "Password",
    "Carrito": "Cart",
    "Inicio": "Home",
    "Productos": "Products",
    "Categoría": "Category",
    "Categorías": "Categories",
    "Compras": "Purchases",
    "Pedido": "Order",
    "Pedidos": "Orders",
    "Pendiente": "Pending",
    "Anterior": "Previous",
    "Siguiente": "Next",
    "Página": "Page",
    "Todos": "All",
    "Limpiar": "Clear",
    "Aplicar": "Apply",
    "Desde": "From",
    "Hasta": "Until",
    "Descuento": "Discount",
    "Direcciones": "Addresses",
    "Dirección": "Address",
    "Instalación": "Installation",
    "Envío": "Shipping",
    "Calle": "Street",
    "Provincia": "Province",
    "Ciudad": "City",
    "Nombre": "Name",
    "Apellidos": "Surnames",
    "Teléfono": "Phone",
    "Persona": "Individual",
    "Empresa": "Company",
    "Contactos": "Contacts",
    "Código": "Code",
    "Código postal": "Postal code",
}

# When Spanish equals English brand word or ambiguous, fix per key
KEY_OVERRIDES: dict[str, str] = {
    "admin.nav.dashboard": "Dashboard",
    "admin.data_explorer.table": "Table",
    "admin.data_explorer.apply": "Run query",
    "admin.data_explorer.col_group_value": "Value",
    "admin.data_explorer.limits_label": "Limits",
    "admin.data_explorer.tables.order_addresses": "Order addresses",
    "admin.data_explorer.tables.generic": "Table",
    "admin.data_explorer.loading": "Loading…",
    "register.type_person": "Individual",
    "register.type_company": "Company",
    "profile.type_person": "Individual",
    "profile.type_company": "Company",
    "errors.back_home": "Back to home",
    "register.contact_name_hint": "Contact person",
    "checkout.payment": "Payment",
    "shop.order.payment_method": "Payment method",
    "shop.account": "My account",
}

POST_EN_FIXES: list[tuple[str, str]] = [
    (r"^Keep$", "Save"),
    (r"\bEliminate\b", "Delete"),
    (r"\bAsset\b", "Active"),
    (r"Mailing address", "Shipping address"),
    (r"^Locksmith Solidarity$", BRAND),
    (r"^Solidarity Locksmith$", BRAND),
    (r"Locksmith Solidarity", BRAND),
    (r"^Solidary Sawmill$", BRAND),
    (r"\bCharging\b", "Loading"),
    (r"\brecharge\b", "reload"),
    (r"^Directions$", "Addresses"),
    (r"\bFacility\b", "Installation"),
    (r"\bShipment\b", "Shipping"),
]


def translate_unique(values: set[str], cache: dict[str, str]) -> None:
    t = GoogleTranslator("es", "en")
    ordered = sorted(values, key=lambda s: (len(s), s))
    for i, s in enumerate(ordered):
        if s in SP_TO_EN:
            cache[s] = SP_TO_EN[s]
            continue
        cache[s] = t.translate(s)
        if (i + 1) % 50 == 0:
            time.sleep(0.25)
        else:
            time.sleep(0.04)


def fix_en_phrase(s: str) -> str:
    out = s
    for pat, repl in POST_EN_FIXES:
        out = re.sub(pat, repl, out)
    out = out.replace("Eliminate", "Delete")
    if out == "Keep" or out == " keep":
        out = "Save"
    return out


def sentence_case_label(s: str) -> str:
    if not s or "{{" in s or s.startswith("DNI") or s.startswith("JPG,") or s.startswith("http"):
        return s
    if s != s.lower() or s.isupper():
        return s
    return s[0].upper() + s[1:]


def main() -> None:
    es: dict[str, str] = json.loads(ES_PATH.read_text(encoding="utf-8"))
    unique: set[str] = {v for v in es.values() if isinstance(v, str)}
    cache: dict[str, str] = {}
    translate_unique(unique, cache)
    for s in unique:
        cache[s] = fix_en_phrase(cache[s])
    en: dict[str, str] = {}
    for k, s in es.items():
        v = fix_en_phrase(cache.get(s) or s or "")
        if k in ("shop.brand_name", "home.hero.title"):
            v = BRAND
        if k == "shop.brand_logo_alt":
            v = f"{BRAND} logo"
        if k == "footer.copyright":
            v = f"© {{{{year}}}} {BRAND}. All rights reserved."
        if k == "footer.legal_link":
            v = f"{BRAND} website"
        if k == "shop.product.image_n_of_m":
            v = "Image {{n}} of {{m}}"
        if k == "shop.status.pending" and s == "Pendiente":
            v = "Pending"
        if s == "Estado" and (".status" in k or "order_status" in k) and "timeline" not in k and "order_date" not in k:
            v = "Status"
        if s == "Factura" and k in ("shop.invoice",):
            v = "Invoice"
        if k == "admin.dashboard.units" or k.endswith("admin.dashboard.units"):
            v = "u."
        en[k] = v
    for k, v in list(en.items()):
        if (
            k.startswith("common.")
            or k in ("auth.password", "auth.remember", "cookies.accept", "errors.reload")
        ) and isinstance(v, str):
            en[k] = sentence_case_label(v)
    for k, v in KEY_OVERRIDES.items():
        if k in en:
            en[k] = v
    EN_PATH.write_text(json.dumps(en, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"Wrote {len(en)} keys to {EN_PATH}")


if __name__ == "__main__":
    main()
