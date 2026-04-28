<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('shop.delivery_note_title') }} {{ $order->reference }}</title>
    @php
        $envLogo = config('mail.brand.logo_url');
        $defaultPublic = (string) config('mail.brand.default_logo', 'images/serraller_solidaria_logo_key.png');
        $logoUrl = ($envLogo !== null && $envLogo !== '') ? (string) $envLogo : url($defaultPublic);
        $brandName = (string) config('mail.brand.display_name', __('shop.brand_name'));
        $footerContact = config('mail.brand.footer_contact');
        $primary = $order->client->contacts->where('is_primary', true)->first();
        $addrShipping = $order->addresses->where('type', \App\Models\OrderAddress::TYPE_SHIPPING)->first();
        $addrInstallation = $order->addresses->where('type', \App\Models\OrderAddress::TYPE_INSTALLATION)->first();
        $linesSubtotal = $order->lines->sum(fn ($l) => $l->line_total);
        $grandTotal = $order->grand_total;
    @endphp
    <style>
        :root {
            --ink: #18181b;
            --muted: #52525b;
            --line: #e4e4e7;
            --surface: #fafafa;
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
        .invoice {
            max-width: 720px;
            margin: 0 auto;
        }
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
        .issuer { max-width: 320px; }
        .issuer .logo {
            max-height: 52px;
            max-width: 220px;
            width: auto;
            height: auto;
            display: block;
            margin-bottom: 0.5rem;
        }
        .issuer .name {
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: -0.02em;
        }
        .issuer .contact-line {
            margin-top: 0.35rem;
            font-size: 13px;
            color: var(--muted);
        }
        .doc-meta { text-align: right; min-width: 200px; }
        .doc-meta .doc-title {
            margin: 0 0 0.35rem;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .doc-meta p {
            margin: 0.15rem 0;
            font-size: 13px;
            color: var(--muted);
        }
        .doc-meta .ref {
            color: var(--ink);
            font-weight: 600;
        }
        .section-label {
            margin: 0 0 0.4rem;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }
        @media (max-width: 560px) {
            .doc-meta { text-align: left; }
        }
        .bill-section { margin-bottom: 1.75rem; max-width: 420px; }
        .panel {
            padding: 1rem 1rem 1rem;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 4px;
        }
        .panel p { margin: 0; }
        .panel .muted { color: var(--muted); font-size: 13px; margin-top: 0.35rem; }
        .addr-block { margin-top: 0.65rem; font-size: 13px; line-height: 1.45; }
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
        table.lines td {
            padding: 0.55rem 0.65rem;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }
        table.lines td.num { text-align: right; white-space: nowrap; }
        .summary-wrap { margin-top: 1.25rem; display: flex; justify-content: flex-end; }
        table.summary { border-collapse: collapse; font-size: 13px; min-width: 260px; }
        table.summary td { padding: 0.35rem 0; }
        table.summary td:last-child { text-align: right; font-variant-numeric: tabular-nums; padding-left: 2rem; }
        table.summary tr.grand td { font-weight: 700; padding-top: 0.5rem; border-top: 2px solid var(--ink); }
        .invoice-footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid var(--line);
            font-size: 12px;
            color: var(--muted);
            text-align: center;
        }
        @media print {
            body { padding: 0.75rem; }
            .invoice { max-width: none; }
            .panel { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <article class="invoice">
        <header class="invoice-header">
            <div class="issuer">
                <img class="logo" src="{{ $logoUrl }}" alt="{{ __('shop.brand_logo_alt') }}">
                <div class="name">{{ $brandName }}</div>
                @if(is_string($footerContact) && $footerContact !== '')
                    <div class="contact-line">{{ $footerContact }}</div>
                @endif
            </div>
            <div class="doc-meta">
                <h1 class="doc-title">{{ __('shop.delivery_note_title') }}</h1>
                <p class="ref">{{ __('shop.invoice_order_ref_label') }}: {{ $order->reference }}</p>
                <p>{{ __('shop.order_date') }}: {{ $order->order_date?->format('d/m/Y H:i') }}</p>
                <p>{{ __('shop.order_status') }}: {{ __('shop.status.' . $order->status) }}</p>
            </div>
        </header>

        <section class="bill-section" aria-labelledby="delivery-to-heading">
            <h2 id="delivery-to-heading" class="section-label">{{ __('shop.delivery_note_ship_to') }}</h2>
            <div class="panel">
                <p><strong>{{ trim(($primary?->name ?? '').' '.($primary?->surname ?? '')) }}</strong></p>
                <p class="muted">{{ $order->client->login_email }}</p>
                @if($addrShipping)
                    <div class="addr-block">
                        <strong>{{ __('shop.invoice_shipping_address') }}</strong><br>
                        {{ $addrShipping->street }}<br>
                        {{ $addrShipping->postal_code }} {{ $addrShipping->city }}
                        @if($addrShipping->province)
                            <br>{{ $addrShipping->province }}
                        @endif
                    </div>
                @endif
                @if($addrInstallation && ($addrInstallation->street || $addrInstallation->city))
                    <div class="addr-block" style="margin-top: 0.75rem;">
                        <strong>{{ __('shop.invoice_installation_address') }}</strong><br>
                        {{ $addrInstallation->street }}<br>
                        {{ $addrInstallation->postal_code }} {{ $addrInstallation->city }}
                        @if($addrInstallation->province)
                            <br>{{ $addrInstallation->province }}
                        @endif
                    </div>
                @endif
            </div>
        </section>

        <h2 class="section-label lines-title">{{ __('shop.order_lines') }}</h2>
        <table class="lines" role="table">
            <thead>
                <tr>
                    <th scope="col">{{ __('shop.order_product_pack') }}</th>
                    <th scope="col" class="num">{{ __('shop.quantity') }}</th>
                    <th scope="col" class="num">{{ __('shop.invoice_unit_price') }}</th>
                    <th scope="col" class="num">{{ __('shop.invoice_line_amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->lines as $line)
                    <tr>
                        <td>{{ $line->product?->name ?? $line->pack?->name }}</td>
                        <td class="num">{{ $line->quantity }}</td>
                        <td class="num">{{ number_format($line->unit_price, 2, ',', '.') }} €</td>
                        <td class="num">{{ number_format($line->line_total, 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-wrap">
            <table class="summary" aria-label="{{ __('shop.invoice_summary') }}">
                <tbody>
                    <tr>
                        <td>{{ __('shop.subtotal_products') }}</td>
                        <td>{{ number_format($linesSubtotal, 2, ',', '.') }} €</td>
                    </tr>
                    @if($order->kind === \App\Models\Order::KIND_ORDER)
                        <tr>
                            <td>{{ __('shop.invoice_shipping_line') }}</td>
                            <td>{{ number_format(\App\Models\Order::SHIPPING_FLAT_EUR, 2, ',', '.') }} €</td>
                        </tr>
                    @endif
                    @if($order->installation_requested && $order->installation_status === \App\Models\Order::INSTALLATION_PRICED && $order->installation_price !== null)
                        <tr>
                            <td>{{ __('shop.invoice_installation_line') }}</td>
                            <td>{{ number_format($order->installation_price, 2, ',', '.') }} €</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td>{{ __('shop.total') }}</td>
                        <td>{{ number_format($grandTotal, 2, ',', '.') }} €</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <footer class="invoice-footer">
            {{ __('shop.delivery_note_footer') }}
        </footer>
    </article>
</body>
</html>
