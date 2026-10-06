<?php

namespace App\Services\AdminChat;

class AdminChatHeuristic
{
    /**
     * @return array{name: string, args: array<string, mixed>}|array{deny: string}|array{reply: string}|null
     */
    public function suggest(string $message): ?array
    {
        $t = mb_strtolower(trim($message));
        if ($t === '') {
            return null;
        }

        if ($this->isIdentityOrHelp($t)) {
            return ['reply' => 'identity'];
        }

        if (preg_match('/\b(crea|crear|inserta|insertar|modifica|modificar|borra|borrar|elimina|eliminar|actualiza|actualizar|sube|subir|upload|png|jpg|edita|editar)\b/u', $t)
            && preg_match('/\b(producto|pack|pedido|cliente|admin|precio|stock|imagen)\b/u', $t)) {
            return ['deny' => 'v1_read_only'];
        }
        if (preg_match('/contrase[ñn]a|password|secret|\.env\b/u', $t)) {
            return ['deny' => 'secrets'];
        }

        if (preg_match('/(factura|invoice|albar[aá]n|delivery[ -]?note).{0,40}?(\d{1,9})/u', $t, $m)
            || preg_match('/(\d{1,9}).{0,20}(factura|invoice|albar[aá]n)/u', $t, $m2)) {
            $id = (int) ($m[2] ?? $m2[1] ?? 0);
            $doc = preg_match('/albar|delivery/u', $t) ? 'delivery_note' : 'invoice';

            return ['name' => 'order_export_doc', 'args' => ['id' => $id, 'doc' => $doc]];
        }

        if (preg_match('/\b(csv|exporta|exportar|descarga)\b/u', $t)) {
            $table = 'orders';
            if (preg_match('/pago|payment/u', $t)) {
                $table = 'payments';
            } elseif (preg_match('/cliente/u', $t)) {
                $table = 'clients';
            } elseif (preg_match('/producto/u', $t)) {
                $table = 'products';
            }

            return ['name' => 'explorer_export_csv', 'args' => ['table' => $table]];
        }

        if (preg_match('/cu[aá]nt[oa]s?.{0,20}(clientes|usuarios)/u', $t) || preg_match('/(clientes|usuarios).{0,20}hay/u', $t)) {
            return ['name' => 'explorer_aggregate', 'args' => ['table' => 'clients', 'metric' => 'count', 'group_by' => 'is_active']];
        }
        if (preg_match('/cu[aá]nt[oa]s?.{0,20}productos/u', $t) || preg_match('/productos.{0,20}hay/u', $t)) {
            return ['name' => 'explorer_aggregate', 'args' => ['table' => 'products', 'metric' => 'count', 'group_by' => 'is_active']];
        }
        if (preg_match('/cu[aá]nt[oa]s?.{0,20}pedidos/u', $t) || preg_match('/pedidos.{0,20}hay/u', $t)) {
            return ['name' => 'explorer_aggregate', 'args' => ['table' => 'orders', 'metric' => 'count', 'group_by' => 'kind']];
        }

        if (preg_match('/\b(pedido|order|comanda)\b.{0,12}(\d{1,9})/u', $t, $m)) {
            return ['name' => 'order_get', 'args' => ['id' => (int) $m[2]]];
        }
        if (preg_match('/#(\d{1,9})\b/u', $t, $m)) {
            return ['name' => 'order_get', 'args' => ['id' => (int) $m[1]]];
        }

        if (preg_match('/\bpacks?\b|lote/u', $t)) {
            $q = $this->searchNeedle($t);

            return ['name' => 'catalog_search', 'args' => ['kind' => 'pack', 'q' => $q]];
        }
        if (preg_match('/categor|tipo de product/u', $t)) {
            return ['name' => 'catalog_search', 'args' => ['kind' => 'category', 'q' => $this->searchNeedle($t)]];
        }
        if (preg_match('/cliente|email|nif|cif/u', $t) && preg_match('/@|\d{5,}/u', $t)) {
            return ['name' => 'client_search', 'args' => ['q' => $this->searchNeedle($t)]];
        }
        if (preg_match('/stock bajo|low stock/u', $t)) {
            return ['name' => 'stats_get', 'args' => ['which' => 'low_stock']];
        }

        if (preg_match('/\b(producto|sku|c[oó]digo|existe|buscar|busca)\b/u', $t)) {
            $q = $this->searchNeedle($t);
            if ($q === '' || mb_strlen($q) < 2) {
                return ['reply' => 'need_product_query'];
            }

            return ['name' => 'catalog_search', 'args' => ['kind' => 'product', 'q' => $q]];
        }

        // Do not invent a catalog search for chit-chat or unclear text.
        return null;
    }

    private function isIdentityOrHelp(string $t): bool
    {
        if (preg_match('/^(hola|hello|hi|bon dia|buenas|hey)[!?.\s]*$/u', $t)) {
            return true;
        }

        return (bool) preg_match(
            '/\b(c[oó]mo te llamas|qui[eé]n eres|quien eres|tu nombre|your name|qu[eé] eres|qu[eé] haces|cu[aá]l es tu funci[oó]n|what (?:are|do) you|help|ayuda|qui[eé]n soy)\b/u',
            $t
        );
    }

    private function searchNeedle(string $t): string
    {
        $stripped = preg_replace(
            '/\b(hola|existe|existen|hay|tienes|tenemos|busca|buscar|quiero|saber|si|un|una|el|la|los|las|de|del|producto|productos|pack|packs|categor[ií]a|tipo|cu[aá]ntos|cu[aá]ntas|pedidos?|clientes?|c[oó]digo|sku|por favor|me puedes|puedes|dime|decir)\b/u',
            ' ',
            $t
        ) ?? $t;
        $stripped = trim(preg_replace('/[¿?¡!.,;:]+/u', ' ', $stripped) ?? $stripped);
        $stripped = trim(preg_replace('/\s+/', ' ', $stripped) ?? $stripped);

        return mb_substr($stripped, 0, 80);
    }
}
