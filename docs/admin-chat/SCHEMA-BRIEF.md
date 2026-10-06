# SCHEMA-BRIEF — allowlist (v1)

Canonical column lists: `config/admin_data_explorer.php`. If this file and the config disagree, **config wins**.

Explorer limits (defaults): `per_page` ≤ 100, export ≤ 5000 rows, aggregate groups ≤ 200, query timeout ~25s.

## Explorer tables

| Table | Use for | Do not expect |
|-------|---------|----------------|
| `products` | SKU `code`, price, stock, flags, `category_id`, `security_level`, `search_text` | Packs; image blobs |
| `orders` | `kind` (`cart` / `order` / `like`), `status`, dates, shipping/install prices | Line items (see `order_lines`) |
| `order_lines` | qty, unit_price, product_id / pack_id, extra keys | Product name (join via id) |
| `order_addresses` | street, city, province, postal_code, type | Client profile addresses |
| `clients` | `login_email`, `identification`, `type`, `is_active` | Password; full GDPR dump |
| `payments` | amount, method, status, gateway, `paid_at` | Card PAN / secrets |
| `personalized_solutions` | request status, contact, problem text | File binaries |
| `admins` | `username`, `is_active`, `last_login_at` | Password hash |

## Order enums (models)

`Order` kind: `cart` | `order` | `like`  
Status: `pending`, `awaiting_payment`, `awaiting_installation_price`, `in_transit`, `sent`, `installation_pending`, `installation_confirmed`, `returned`

Invoice/delivery-note: **404 unless `kind=order`**.

## Not in explorer — GET tools only

| Resource | Model / notes |
|----------|----------------|
| `packs` | `Pack` + translations + `pack_items`; no explorer table in v1 |
| `product_categories` | Categories / “tipo de producto” |
| `features` / feature names | Product attributes |
| `product_variant_groups`, `key_colors` | Variants / key colours |
| `faqs`, `reviews` | Content |
| `return_requests` | RMA read |

## Identity map

- Shop customer = **`Client`**, not `User` (`User` is unused for auth).
- Staff = **`Admin`** (username + password, guard `admin`).
