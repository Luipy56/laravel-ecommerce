<?php

namespace App\Services\AdminChat;

class AdminChatHeuristic
{
    /**
     * @return array{name: string, args: array<string, mixed>}|array{deny: string}|null
     */
    public function suggest(string $message): ?array
    {
        $t = mb_strtolower(trim($message));
        if ($t === '') {
            return null;
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
        if (preg_match('/cu[aá]nt[oa]s?.{0,20}productos/u', $t)) {
            return ['name' => 'explorer_aggregate', 'args' => ['table' => 'products', 'metric' => 'count', 'group_by' => 'is_active']];
        }
        if (preg_match('/cu[aá]nt[oa]s?.{0,20}pedidos/u', $t)) {
            return ['name' => 'explorer_aggregate', 'args' => ['table' => 'orders', 'metric' => 'count', 'group_by' => 'kind']];
        }

        if (preg_match('/\b(pedido|order|comanda)\b.{0,12}(\d{1,9})/u', $t, $m)) {
            return ['name' => 'order_get', 'args' => ['id' => (int) $m[2]]];
        }
        if (preg_match('/#(\d{1,9})\b/u', $t, $m)) {
            return ['name' => 'order_get', 'args' => ['id' => (int) $m[1]]];
        }

        if (preg_match('/\bpacks?\b|lote/u', $t)) {
            return ['name' => 'catalog_search', 'args' => ['kind' => 'pack', 'q' => $this->searchNeedle($t)]];
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

        return ['name' => 'catalog_search', 'args' => ['kind' => 'product', 'q' => $this->searchNeedle($t)]];
    }

    private function searchNeedle(string $t): string
    {
        $stripped = preg_replace('/\b(existe|hay|tienes|tenemos|busca|buscar|quiero|saber|si|un|una|el|la|los|las|de|del|producto|pack|categor[ií]a|tipo|cu[aá]ntos|cu[aá]ntas|pedidos?|clientes?)\b/u', ' ', $t) ?? $t;
        $stripped = trim(preg_replace('/\s+/', ' ', $stripped) ?? $stripped);

        return mb_substr($stripped !== '' ? $stripped : $t, 0, 80);
    }
}
