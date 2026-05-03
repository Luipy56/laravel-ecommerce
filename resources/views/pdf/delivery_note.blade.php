<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('shop.delivery_note_title') }} {{ $order->reference }}</title>
    @php
        // ── Brand / issuer ───────────────────────────────────────────────────
        $envLogo = config('mail.brand.logo_url');
        $defaultPublic = (string) config('mail.brand.default_logo', 'images/serraller_solidaria_logo_key.png');
        $logoUrl = ($envLogo !== null && $envLogo !== '') ? (string) $envLogo : url($defaultPublic);
        $brandName = (string) config('mail.brand.display_name', __('shop.brand_name'));
        $footerContact = config('mail.brand.footer_contact');
        $issuerTaxId = (string) (config('mail.brand.tax_id') ?? '');

        // ── Recipient ───────────────────────────────────────────────────────
        $primary = $order->client->contacts->where('is_primary', true)->first();
        $clientFullName = trim(($primary?->name ?? '').' '.($primary?->surname ?? ''));
        if ($clientFullName === '') {
            $clientFullName = (string) ($order->client->login_email ?? '');
        }
        $addrShipping = $order->addresses->where('type', \App\Models\OrderAddress::TYPE_SHIPPING)->first();
        $addrInstallation = $order->addresses->where('type', \App\Models\OrderAddress::TYPE_INSTALLATION)->first();

        // ── Numbers / dates ─────────────────────────────────────────────────
        // Albarán number is issuance-side (when the document is rendered) since shipping
        // date may not be set yet; ALB- prefix differentiates from FAC- (invoice).
        $deliveryDate = $order->shipping_date ?? $order->order_date ?? now();
        $deliveryYear = $deliveryDate ? $deliveryDate->format('Y') : now()->format('Y');
        $deliveryNumber = sprintf('ALB-%s-%06d', $deliveryYear, (int) $order->id);

        $totalUnits = (int) $order->lines->sum(fn ($l) => (int) $l->quantity);
    @endphp
    <style>
        :root {
            --ink: #18181b;
            --muted: #52525b;
            --line: #e4e4e7;
            --surface: #fafafa;
            --warning: #b45309;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 1.5rem;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--ink);
            background: #fff;
        }
        .invoice { max-width: 720px; margin: 0 auto; }
        .invoice-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1.5rem;
            padding-bottom: 1.25rem;
            border-bottom: 2px solid var(--ink);
            margin-bottom: 1.5rem;
        }
        .issuer { max-width: 340px; }
        .issuer .logo {
            max-height: 52px;
            max-width: 220px;
            width: auto;
            height: auto;
            display: block;
            margin-bottom: 0.5rem;
        }
        .issuer .name { font-weight: 700; font-size: 1rem; letter-spacing: -0.02em; }
        .issuer .meta-line {
            margin-top: 0.35rem;
            font-size: 12.5px;
            color: var(--muted);
            white-space: pre-line;
        }
        .doc-meta { text-align: right; min-width: 220px; }
        .doc-meta .doc-title {
            margin: 0 0 0.25rem;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }
        .doc-meta .subtitle {
            margin: 0 0 0.5rem;
            font-size: 11.5px;
            color: var(--warning);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .doc-meta .meta-row {
            margin: 0.15rem 0;
            font-size: 12.5px;
            color: var(--muted);
            display: flex;
            justify-content: flex-end;
            gap: 0.4rem;
        }
        .doc-meta .meta-row strong { color: var(--ink); font-weight: 600; }
        .section-label {
            margin: 0 0 0.4rem;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }
        .ship-section { margin-bottom: 1.75rem; max-width: 460px; }
        .panel {
            padding: 0.85rem 1rem;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 4px;
            font-size: 13px;
        }
        .panel p { margin: 0; }
        .panel .name { font-weight: 600; }
        .panel .muted { color: var(--muted); font-size: 12.5px; margin-top: 0.2rem; }
        .panel .addr { margin-top: 0.45rem; font-size: 12.5px; line-height: 1.45; white-space: pre-line; }
        .lines-title { margin-bottom: 0.65rem; }
        table.lines {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        table.lines th {
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
            padding: 0.5rem 0.65rem;
            border-bottom: 2px solid var(--ink);
        }
        table.lines th.num { text-align: right; }
        table.lines th.qty { text-align: center; }
        table.lines td {
            padding: 0.65rem;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }
        table.lines td.qty {
            text-align: center;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            white-space: nowrap;
        }
        table.lines tfoot td {
            padding: 0.6rem 0.65rem;
            border-top: 2px solid var(--ink);
            font-weight: 600;
            font-size: 12.5px;
        }
        table.lines tfoot td.qty { text-align: center; }

        .receipt-grid {
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .receipt-box {
            border: 1px solid var(--line);
            border-radius: 4px;
            padding: 0.85rem 1rem;
            min-height: 130px;
            display: flex;
            flex-direction: column;
        }
        .receipt-box h3 {
            margin: 0 0 0.25rem;
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }
        .receipt-box .hint {
            font-size: 11.5px;
            color: var(--muted);
            margin-bottom: 0.6rem;
        }
        .receipt-box .signature-line {
            margin-top: auto;
            border-bottom: 1px solid var(--ink);
            height: 2.5rem;
        }
        .receipt-box .date-row {
            margin-top: 0.45rem;
            font-size: 11.5px;
            color: var(--muted);
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .no-fiscal-banner {
            margin-top: 1.5rem;
            padding: 0.65rem 0.85rem;
            background: #fef3c7;
            border: 1px solid #fcd34d;
            color: #92400e;
            border-radius: 4px;
            font-size: 12.5px;
            line-height: 1.5;
        }
        .invoice-footer {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--line);
            font-size: 12px;
            color: var(--muted);
            text-align: center;
        }
        @media (max-width: 560px) {
            .doc-meta { text-align: left; }
            .doc-meta .meta-row { justify-content: flex-start; }
            .receipt-grid { grid-template-columns: 1fr; }
        }
        @media print {
            body { padding: 0.75rem; }
            .invoice { max-width: none; }
            .panel, .receipt-box, .no-fiscal-banner {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <article class="invoice">
        <header class="invoice-header">
            <div class="issuer">
                <img class="logo" src="{{ $logoUrl }}" alt="{{ __('shop.brand_logo_alt') }}">
                <div class="name">{{ $brandName }}</div>
                @if($issuerTaxId !== '')
                    <div class="meta-line"><strong>{{ __('shop.invoice_tax_id') }}:</strong> {{ $issuerTaxId }}</div>
                @endif
                @if(is_string($footerContact) && $footerContact !== '')
                    <div class="meta-line">{{ $footerContact }}</div>
                @endif
            </div>
            <div class="doc-meta">
                <h1 class="doc-title">{{ __('shop.delivery_note_title') }}</h1>
                <p class="subtitle">{{ __('shop.delivery_note_subtitle') }}</p>
                <p class="meta-row"><strong>{{ __('shop.delivery_note_number_label') }}:</strong> {{ $deliveryNumber }}</p>
                <p class="meta-row"><strong>{{ __('shop.invoice_order_ref_label') }}:</strong> {{ $order->reference }}</p>
                <p class="meta-row"><strong>{{ __('shop.order_date') }}:</strong> {{ $order->order_date?->format('d/m/Y') }}</p>
            </div>
        </header>

        <section class="ship-section" aria-labelledby="delivery-to-heading">
            <h2 id="delivery-to-heading" class="section-label">{{ __('shop.delivery_note_ship_to') }}</h2>
            <div class="panel">
                <p class="name">{{ $clientFullName }}</p>
                @if(!empty($order->client->login_email))
                    <p class="muted">{{ $order->client->login_email }}</p>
                @endif
                @if($primary?->phone)
                    <p class="muted">{{ $primary->phone }}</p>
                @endif
                @if($addrShipping)
                    <div class="addr">
                        {{ $addrShipping->street }}<br>
                        {{ $addrShipping->postal_code }} {{ $addrShipping->city }}@if($addrShipping->province), {{ $addrShipping->province }}@endif
                        @if(!empty($addrShipping->note))
                            <br><span style="color:var(--muted);">{{ $addrShipping->note }}</span>
                        @endif
                    </div>
                @endif
                @if($addrInstallation && ($addrInstallation->street || $addrInstallation->city))
                    <div class="addr">
                        <strong>{{ __('shop.invoice_installation_address') }}</strong><br>
                        {{ $addrInstallation->street }}<br>
                        {{ $addrInstallation->postal_code }} {{ $addrInstallation->city }}@if($addrInstallation->province), {{ $addrInstallation->province }}@endif
                    </div>
                @endif
            </div>
        </section>

        <h2 class="section-label lines-title">{{ __('shop.order_lines') }}</h2>
        <table class="lines" role="table">
            <thead>
                <tr>
                    <th scope="col">{{ __('shop.order_product_pack') }}</th>
                    <th scope="col" class="qty">{{ __('shop.quantity') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->lines as $line)
                    <tr>
                        <td>{{ $line->product?->name ?? $line->pack?->name }}</td>
                        <td class="qty">{{ $line->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>{{ __('shop.delivery_note_total_units') }}</td>
                    <td class="qty">{{ $totalUnits }}</td>
                </tr>
            </tfoot>
        </table>

        <section class="receipt-grid">
            <div class="receipt-box">
                <h3>{{ __('shop.delivery_note_signature_label') }}</h3>
                <p class="hint">{{ __('shop.delivery_note_signature_hint') }}</p>
                <div class="signature-line" aria-hidden="true"></div>
                <div class="date-row">
                    <span>{{ __('shop.delivery_note_received_on') }}: __ / __ / ____</span>
                </div>
            </div>
            <div class="receipt-box">
                <h3>{{ __('shop.delivery_note_carrier_notes') }}</h3>
                <div class="signature-line" aria-hidden="true" style="height:5rem;border-bottom:none;border:1px dashed var(--line);"></div>
            </div>
        </section>

        <p class="no-fiscal-banner">{{ __('shop.delivery_note_no_fiscal_value') }}</p>

        <footer class="invoice-footer">
            {{ __('shop.delivery_note_footer') }}
        </footer>
    </article>
</body>
</html>
