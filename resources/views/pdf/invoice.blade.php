<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('shop.invoice_title') }} {{ $order->reference }}</title>
    @php
        // ── Brand / issuer ───────────────────────────────────────────────────
        $envLogo = config('mail.brand.logo_url');
        $defaultPublic = (string) config('mail.brand.default_logo', 'images/serraller_solidaria_logo_key.png');
        $logoUrl = ($envLogo !== null && $envLogo !== '') ? (string) $envLogo : url($defaultPublic);
        $brandName = (string) config('mail.brand.display_name', __('shop.brand_name'));
        $footerContact = config('mail.brand.footer_contact');
        $issuerTaxId = (string) (config('mail.brand.tax_id') ?? '');
        $issuerFiscalAddress = (string) (config('mail.brand.fiscal_address') ?? '');

        // ── Recipient (client fiscal data) ──────────────────────────────────
        $primary = $order->client->contacts->where('is_primary', true)->first();
        $clientFullName = trim(($primary?->name ?? '').' '.($primary?->surname ?? ''));
        if ($clientFullName === '') {
            $clientFullName = (string) ($order->client->login_email ?? '');
        }
        $addrShipping = $order->addresses->where('type', \App\Models\OrderAddress::TYPE_SHIPPING)->first();
        $addrInstallation = $order->addresses->where('type', \App\Models\OrderAddress::TYPE_INSTALLATION)->first();

        // ── Numbers ─────────────────────────────────────────────────────────
        // Spanish factura numbering: per-year invoice number derived from order id and paid date.
        $successfulPayment = $order->payments->firstWhere(fn ($p) => $p->isSuccessful());
        $invoiceDate = $successfulPayment?->paid_at ?? $order->order_date ?? now();
        $invoiceYear = $invoiceDate ? $invoiceDate->format('Y') : now()->format('Y');
        $invoiceNumber = sprintf('FAC-%s-%06d', $invoiceYear, (int) $order->id);

        // ── Totals & VAT (IVA) ──────────────────────────────────────────────
        // Stored prices are VAT-inclusive (Spanish B2C default). Compute base + VAT from total.
        $vatRate = (float) (config('mail.brand.vat_rate_percent') ?? 21);
        $vatRateLabel = rtrim(rtrim(number_format($vatRate, 2, ',', '.'), '0'), ',');
        $linesSubtotal = (float) $order->lines->sum(fn ($l) => $l->line_total);
        $shippingFee = $order->kind === \App\Models\Order::KIND_ORDER
            ? (float) ($order->shipping_price ?? \App\Models\ShopSetting::shippingFlatEur())
            : 0.0;
        $installationFee = ($order->installation_requested
            && $order->installation_status === \App\Models\Order::INSTALLATION_PRICED
            && $order->installation_price !== null)
            ? (float) $order->installation_price
            : 0.0;
        $grandTotal = (float) $order->grand_total;
        $taxableBase = $vatRate > 0 ? round($grandTotal / (1 + $vatRate / 100), 2) : $grandTotal;
        $vatAmount = round($grandTotal - $taxableBase, 2);
    @endphp
    <style>
        :root {
            --ink: #18181b;
            --muted: #52525b;
            --line: #e4e4e7;
            --surface: #fafafa;
            --accent: #16a34a;
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
            margin: 0 0 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }
        .doc-meta .meta-row {
            margin: 0.15rem 0;
            font-size: 12.5px;
            color: var(--muted);
            display: flex;
            justify-content: flex-end;
            gap: 0.4rem;
        }
        .doc-meta .meta-row strong {
            color: var(--ink);
            font-weight: 600;
        }
        .paid-stamp {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.25rem 0.6rem;
            border: 2px solid var(--accent);
            color: var(--accent);
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.08em;
            transform: rotate(-2deg);
        }
        .section-label {
            margin: 0 0 0.4rem;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }
        .parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }
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
        table.lines td {
            padding: 0.6rem 0.65rem;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }
        table.lines td.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        table.lines tbody tr:last-child td { border-bottom: none; }
        .summary-wrap { display: flex; justify-content: flex-end; margin-top: 1.25rem; }
        table.summary {
            min-width: 280px;
            border-collapse: collapse;
            font-size: 13px;
            font-variant-numeric: tabular-nums;
        }
        table.summary td { padding: 0.3rem 0; vertical-align: baseline; }
        table.summary td:first-child { color: var(--muted); text-align: left; padding-right: 1.5rem; }
        table.summary td:last-child { text-align: right; font-weight: 500; }
        table.summary tr.subgroup td { padding-top: 0.5rem; border-top: 1px solid var(--line); }
        table.summary tr.grand td {
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--ink);
            padding-top: 0.65rem;
            border-top: 2px solid var(--ink);
        }
        .vat-note {
            margin-top: 0.4rem;
            font-size: 11.5px;
            color: var(--muted);
            text-align: right;
        }
        .payment-block {
            margin-top: 1.5rem;
            padding: 0.75rem 1rem;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 4px;
            font-size: 12.5px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.4rem 1rem;
        }
        .payment-block dt { font-weight: 600; color: var(--muted); }
        .payment-block dd { margin: 0; color: var(--ink); }
        .legal-notice {
            margin-top: 1.25rem;
            font-size: 11.5px;
            color: var(--muted);
            line-height: 1.45;
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
            .parties { grid-template-columns: 1fr; }
            .payment-block { grid-template-columns: 1fr; }
        }
        @media print {
            body { padding: 0.75rem; }
            .invoice { max-width: none; }
            .panel, .payment-block { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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
                @if($issuerFiscalAddress !== '')
                    <div class="meta-line">{{ $issuerFiscalAddress }}</div>
                @endif
                @if(is_string($footerContact) && $footerContact !== '')
                    <div class="meta-line">{{ $footerContact }}</div>
                @endif
            </div>
            <div class="doc-meta">
                <h1 class="doc-title">{{ __('shop.invoice_title') }}</h1>
                <p class="meta-row"><strong>{{ __('shop.invoice_number_label') }}:</strong> {{ $invoiceNumber }}</p>
                <p class="meta-row"><strong>{{ __('shop.invoice_issue_date') }}:</strong> {{ $invoiceDate?->format('d/m/Y') }}</p>
                <p class="meta-row"><strong>{{ __('shop.invoice_order_ref_label') }}:</strong> {{ $order->reference }}</p>
                @if($successfulPayment)
                    <span class="paid-stamp" aria-label="{{ __('shop.invoice_payment_status_paid') }}">{{ __('shop.invoice_payment_status_paid') }}</span>
                @endif
            </div>
        </header>

        <section class="parties" aria-label="{{ __('shop.invoice_summary') }}">
            <div>
                <h2 class="section-label">{{ __('shop.invoice_issuer_label') }}</h2>
                <div class="panel">
                    <p class="name">{{ $brandName }}</p>
                    @if($issuerTaxId !== '')
                        <p class="muted">{{ __('shop.invoice_tax_id') }}: {{ $issuerTaxId }}</p>
                    @endif
                    @if($issuerFiscalAddress !== '')
                        <div class="addr">{{ $issuerFiscalAddress }}</div>
                    @endif
                    @if(is_string($footerContact) && $footerContact !== '')
                        <p class="muted">{{ $footerContact }}</p>
                    @endif
                </div>
            </div>
            <div>
                <h2 class="section-label">{{ __('shop.invoice_bill_to') }}</h2>
                <div class="panel">
                    <p class="name">{{ $clientFullName !== '' ? $clientFullName : ($order->client->login_email ?? '') }}</p>
                    @if(!empty($order->client->identification))
                        <p class="muted">{{ __('shop.invoice_tax_id') }}: {{ $order->client->identification }}</p>
                    @endif
                    @if(!empty($order->client->login_email))
                        <p class="muted">{{ $order->client->login_email }}</p>
                    @endif
                    @if($primary?->phone)
                        <p class="muted">{{ $primary->phone }}</p>
                    @endif
                    @if($addrShipping)
                        <div class="addr">
                            <strong>{{ __('shop.invoice_shipping_address') }}</strong><br>
                            {{ $addrShipping->street }}<br>
                            {{ $addrShipping->postal_code }} {{ $addrShipping->city }}@if($addrShipping->province), {{ $addrShipping->province }}@endif
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
                            <td>{{ number_format($shippingFee, 2, ',', '.') }} €</td>
                        </tr>
                    @endif
                    @if($installationFee > 0)
                        <tr>
                            <td>{{ __('shop.invoice_installation_line') }}</td>
                            <td>{{ number_format($installationFee, 2, ',', '.') }} €</td>
                        </tr>
                    @endif
                    <tr class="subgroup">
                        <td>{{ __('shop.invoice_taxable_base') }}</td>
                        <td>{{ number_format($taxableBase, 2, ',', '.') }} €</td>
                    </tr>
                    <tr>
                        <td>{{ __('shop.invoice_vat_label', ['rate' => $vatRateLabel]) }}</td>
                        <td>{{ number_format($vatAmount, 2, ',', '.') }} €</td>
                    </tr>
                    <tr class="grand">
                        <td>{{ __('shop.invoice_total_with_vat') }}</td>
                        <td>{{ number_format($grandTotal, 2, ',', '.') }} €</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="vat-note">{{ __('shop.invoice_vat_included_note', ['rate' => $vatRateLabel]) }}</p>

        @if($successfulPayment)
            <h2 class="section-label" style="margin-top:1.25rem;">{{ __('shop.invoice_payment_label') }}</h2>
            <dl class="payment-block">
                <dt>{{ __('shop.invoice_payment_method') }}</dt>
                <dd>
                    @php
                        $methodKey = 'shop.invoice_payment_method.'.$successfulPayment->payment_method;
                        $methodLabel = __($methodKey);
                    @endphp
                    {{ $methodLabel !== $methodKey ? $methodLabel : ucfirst((string) $successfulPayment->payment_method) }}
                </dd>
                <dt>{{ __('shop.invoice_paid_at') }}</dt>
                <dd>{{ $successfulPayment->paid_at?->format('d/m/Y H:i') }}</dd>
                @if(!empty($successfulPayment->gateway_reference))
                    <dt>{{ __('shop.invoice_payment_reference') }}</dt>
                    <dd>{{ $successfulPayment->gateway_reference }}</dd>
                @endif
            </dl>
        @endif

        <p class="legal-notice">{{ __('shop.invoice_legal_notice') }}</p>

        <footer class="invoice-footer">
            {{ __('shop.invoice_footer_thanks') }}
        </footer>
    </article>
</body>
</html>
