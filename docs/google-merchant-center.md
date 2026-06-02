# Google Merchant Center product feed

This app exposes a **live product feed** for [Google Merchant Center](https://merchants.google.com/). Google fetches the URL on its own schedule; operators do **not** need to export CSV files manually when catalog data changes in the admin.

## Feed URL

After deployment, register this URL in Merchant Center (replace the host with your production domain):

```text
https://{APP_URL}/feeds/google-merchant.xml
```

Example (local dev):

```text
http://localhost:8080/feeds/google-merchant.xml
```

## What the feed contains

- **Active products only** (`is_active = true`), same visibility rule as the public catalog and sitemap.
- **Required GMC fields:** `id`, `title`, `description`, `link`, `image_link`, `availability`, `price`, `condition`, `brand`.
- **Price:** customer-facing price after `discount_percent`, formatted as `NN.NN EUR` (prices are stored VAT-inclusive).
- **Availability:** `in stock` when `stock > 0`, otherwise `out of stock`.
- **MPN:** product `code` when present; otherwise `identifier_exists` is set to `false`.
- **Cache:** 6 hours (same cadence as `sitemap.xml`). Cache is cleared when a product is saved or deleted.

## One-time setup in Merchant Center

These steps are done **once** per shop domain (business / operations — not recurring dev work):

1. Create a [Merchant Center](https://merchants.google.com/) account (or use an existing one).
2. **Verify** the website domain (HTML tag, DNS, or Google Search Console link).
3. Add a **product data source** → **Scheduled fetch** (or “URL”) and paste the feed URL above.
4. Wait for Google to fetch and validate the feed. Use the [feed debugger](https://support.google.com/merchants/answer/7169157) if rows are rejected.

No weekly CSV upload is required. Product edits in the Laravel admin are reflected automatically after the feed cache refreshes or is invalidated.

## Notes

- The feed is public (catalog data only). Do not put secrets in product fields.
- Packs are **not** included in this MVP feed; only standalone products.
- `robots.txt` does not list the feed (optional by design); the URL is registered directly in GMC.
- JSON-LD on product pages is tracked separately; this feed satisfies GMC “primary product data” via URL fetch.

## Related code

- `app/Http/Controllers/GoogleMerchantFeedController.php`
- `resources/views/feeds/google-merchant.blade.php`
- `app/Http/Controllers/SpaShellController.php` — Open Graph / Twitter meta for link previews (WhatsApp, Facebook, X).
