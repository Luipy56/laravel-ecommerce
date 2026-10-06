# TOOLS — contracts (v1)

Implement later as PHP services that wrap **existing** admin endpoints. The model only emits `name` + JSON `args`.

**Global caps for chat:** `limit` default 10, max 25. Truncate string fields to 240 chars in tool JSON. Never put CSV/HTML bodies in the tool result — `download` object instead.

```json
{
  "ok": true,
  "summary": "one line",
  "data": [],
  "meta": { "total": 0, "returned": 0 },
  "download": null
}
```

On error: `{ "ok": false, "error": "short reason" }`.

## explorer_schema

Args: none.  
Returns: table names + column names (no i18n blobs).

## explorer_query

Args:

- `table` (required, allowlisted)
- `q` (optional, max 200)
- `filters` (optional): `{column, op, value}[]` ops `= != < > <= >= like`
- `date_column`, `date_from`, `date_to`
- `sort_column`, `sort_direction` (`asc`|`desc`)
- `page`, `per_page` (chat max 25)

Wraps `POST /api/v1/admin/data-explorer/query`.

## explorer_aggregate

Args: `table`, `metric` (`count`|`sum`|`avg`), `group_by`, optional `value_column`, same `q`/filters/dates as query.  
Wraps `POST /api/v1/admin/data-explorer/aggregate`.

Use for “how many clients”, “orders by status”.

## explorer_export_csv

Same filter args as query.  
Wraps `POST /api/v1/admin/data-explorer/export`.  
Result: `{ "download": { "filename": "...csv", "content_type": "text/csv" } }` — runtime stores a short-lived admin-only blob or streams to the browser. **Do not** inline the CSV.

## catalog_search

Args: `kind` = `product` | `pack` | `category`, `q`, optional `is_active`, `limit`.  
Wraps admin index + `search` query param (`AdminProductController` / `AdminPackController` / categories).  
Return: `id`, `code` (products), `name`, `price`, `stock` (products), `is_active`.

## catalog_get

Args: `kind` = `product` | `pack` | `category`, `id` (int).  
Wraps admin `show`. Compact fields only (no full image payloads; image count + first url ok).

## order_search

Args: optional `q`, `status`, `kind`, `client_id`, `date_from`, `date_to`, `limit`.  
Wraps admin orders index. Compact: id, kind, status, client_id, order_date, **`lines_subtotal`**, **`amount_due`** (grand total), **`payments_sum`**.  
Use this for “últimos pedidos”, “resumen de facturas”, “respecto al dinero”.

## order_get

Args: `id`.  
Wraps `GET /api/v1/admin/orders/{order}`. Include line ids/qty/product_id/pack_id, address city/postal, payment status. No HTML.

## order_export_doc

Args: `id`, `doc` = `invoice` | `delivery_note`, optional `locale` (`ca`|`es`|`en`).  
Wraps GET invoice / delivery-note. Result download handle, `content_type: text/html`. If 404, say the record is not `kind=order`.

## client_search / client_get

Wraps `GET /api/v1/admin/clients` and `show`. Fields: id, type, identification, login_email, is_active. No consents dump unless already in show and then cap length.

## stats_get

Args: `which` = `postal_codes` | `sales_by_period` | `top_products` | `low_stock`, plus the query params those endpoints already accept.  
Wraps `GET /api/v1/admin/stats/*`.

## entity_get (optional catch-all read)

Args: `resource` in `faqs|reviews|return-requests|personalized-solutions|admins|variant-groups|key-colors|features`, `id` optional, `q` optional.  
**GET only.** If `id` missing, use index with `limit`.

## Not tools in v1

Anything POST/PUT/PATCH/DELETE except explorer `query`/`export`/`aggregate` (those are reads). No `settings` PUT. No mail. No image POST.
