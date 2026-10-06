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

        // Identity/demo only when the message is NOT also a data question.
        // Multi-intent ("hola, cómo te llamas y resume pedidos…") must reach the LLM.
        if (! $this->hasDataIntent($t)) {
            if ($this->isDemoRequest($t)) {
                return ['reply' => 'demo'];
            }
            if ($this->isIdentityOrHelp($t)) {
                return ['reply' => 'identity'];
            }
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

        // Recent invoices / last N orders / money summary (confirmed kind=order).
        if (
            (preg_match('/\b(factura|facturas|invoice|invoices)\b/u', $t)
                && preg_match('/\b(\d{1,2}|diez|últim|ultim|recient|list|resumen|mostrar|muestra|dame)\b/u', $t))
            || (preg_match('/\b(pedidos?|orders?)\b/u', $t)
                && preg_match('/\b(últim|ultim|recient|resumen|dinero|importe|total|euros?|€)\b/u', $t))
        ) {
            $limit = 10;
            if (preg_match('/\b(\d{1,2})\b/u', $t, $m)) {
                $limit = max(1, min(20, (int) $m[1]));
            } elseif (preg_match('/\bdiez\b/u', $t)) {
                $limit = 10;
            }

            return ['name' => 'order_search', 'args' => ['kind' => 'order', 'limit' => $limit]];
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
        if (preg_match('/cu[aá]nt[oa]s?.{0,20}packs?\b/u', $t) || preg_match('/\bpacks?\b.{0,20}hay/u', $t)) {
            return ['name' => 'catalog_search', 'args' => ['kind' => 'pack', 'q' => '', 'limit' => 20]];
        }

        if (preg_match('/\b(pedido|order|comanda)\b.{0,12}(\d{1,9})/u', $t, $m)) {
            return ['name' => 'order_get', 'args' => ['id' => (int) $m[2]]];
        }
        if (preg_match('/#(\d{1,9})\b/u', $t, $m)) {
            return ['name' => 'order_get', 'args' => ['id' => (int) $m[1]]];
        }

        if (preg_match('/categor|tipo de product/u', $t)) {
            $q = $this->isListAllIntent($t) ? '' : $this->searchNeedle($t);

            return ['name' => 'catalog_search', 'args' => ['kind' => 'category', 'q' => $q, 'limit' => 30]];
        }
        if (preg_match('/\bpacks?\b|lote/u', $t)) {
            $q = $this->isListAllIntent($t) ? '' : $this->searchNeedle($t);

            return ['name' => 'catalog_search', 'args' => ['kind' => 'pack', 'q' => $q]];
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

        return null;
    }

    private function hasDataIntent(string $t): bool
    {
        return (bool) preg_match(
            '/\b(pedido|pedidos|factura|facturas|producto|productos|pack|packs|categor|cliente|clientes|pago|pagos|dinero|importe|total|stock|csv|export|albar[aá]n|invoice|order|sku|c[oó]digo|resumen|cu[aá]nt)\b/u',
            $t
        );
    }

    private function isIdentityOrHelp(string $t): bool
    {
        if (preg_match('/^(hola|hello|hi|bon dia|buenas|hey)[!?.\s]*$/u', $t)) {
            return true;
        }

        return (bool) preg_match(
            '/\b(c[oó]mo te llamas|qui[eé]n eres|quien eres|tu nombre|your name|qu[eé] eres|qu[eé] haces|qu[eé] puedes|qu[eé] sabes|cu[aá]l es tu funci[oó]n|what (?:are|do) you|help|ayuda|capacidades|en qu[eé] puedes)\b/u',
            $t
        );
    }

    private function isDemoRequest(string $t): bool
    {
        return (bool) preg_match('/\b(demostr\w*|ens[eé][nñ]ame|enséñame|muéstrame|muestrame|hazme una demo|pru[eé]bate)\b/u', $t);
    }

    private function isListAllIntent(string $t): bool
    {
        return (bool) preg_match(
            '/\b(cu[aá]nt|list|lista|tod[oa]s|nombre|nombres|c[oó]mo se llama|cu[aá]les|hay|existen|mostrar|muestra|dame)\b/u',
            $t
        ) && ! preg_match('/\b(busca|buscar|filtra|con c[oó]digo)\b/u', $t);
    }

    private function searchNeedle(string $t): string
    {
        $stripped = preg_replace(
            '/\b(hola|existe|existen|hay|tienes|tenemos|busca|buscar|quiero|saber|si|un|una|el|la|los|las|de|del|producto|productos|pack|packs|categor[ií]as?|tipo|cu[aá]ntos|cu[aá]ntas|cu[aá]les|pedidos?|clientes?|c[oó]digo|sku|por favor|me puedes|puedes|dime|decir|c[oó]mo|se|llaman|llamáis|nombres?|lista|listar|todas|todos|resumen|últimas|ultimas|últimos|ultimos|recientes|facturas?|invoices?)\b/u',
            ' ',
            $t
        ) ?? $t;
        $stripped = trim(preg_replace('/[¿?¡!.,;:]+/u', ' ', $stripped) ?? $stripped);
        $stripped = trim(preg_replace('/\s+/', ' ', $stripped) ?? $stripped);

        return mb_substr($stripped, 0, 80);
    }
}
