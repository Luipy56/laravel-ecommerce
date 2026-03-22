<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('shop.invoice_title') }} #{{ $order->id }}</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 2rem auto; padding: 1rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .total { font-weight: bold; font-size: 1.1rem; }
    </style>
</head>
<body>
    <h1>{{ __('shop.invoice_title') }} #{{ $order->id }}</h1>
    <p>{{ __('shop.order_status') }}: {{ __('shop.status.' . $order->status) }}</p>
    <p>{{ __('shop.order_date') }}: {{ $order->order_date?->format('d/m/Y H:i') }}</p>

    @php
        $primary = $order->client->contacts->where('is_primary', true)->first();
        $addr = $order->addresses->where('type', 'shipping')->first();
    @endphp
    <h2>{{ __('shop.order_client') }}</h2>
    <p>{{ $primary?->name }} {{ $primary?->surname }}<br>
    {{ $order->client->login_email }}</p>
    @if($addr)
    <p>{{ $addr->street }}, {{ $addr->postal_code }} {{ $addr->city }}</p>
    @endif

    <h2>{{ __('shop.order_lines') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('shop.order_product_pack') }}</th>
                <th class="text-right">{{ __('shop.quantity') }}</th>
                <th class="text-right">{{ __('shop.price') }}</th>
                <th class="text-right">{{ __('shop.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->lines as $line)
            <tr>
                <td>{{ $line->product?->name ?? $line->pack?->name }}</td>
                <td class="text-right">{{ $line->quantity }}</td>
                <td class="text-right">{{ number_format($line->unit_price, 2, ',', '.') }} €</td>
                <td class="text-right">{{ number_format($line->line_total, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @php
        $linesSubtotal = $order->lines->sum(fn ($l) => $l->line_total);
        $grandTotal = $order->grand_total;
    @endphp
    <p class="text-right">{{ __('shop.subtotal_products') }}: {{ number_format($linesSubtotal, 2, ',', '.') }} €</p>
    @if($order->kind === \App\Models\Order::KIND_ORDER)
        <p class="text-right">{{ __('shop.invoice_shipping_line') }}: {{ number_format(\App\Models\Order::SHIPPING_FLAT_EUR, 2, ',', '.') }} €</p>
    @endif
    @if($order->installation_requested && $order->installation_status === \App\Models\Order::INSTALLATION_PRICED && $order->installation_price !== null)
        <p class="text-right">{{ __('shop.invoice_installation_line') }}: {{ number_format($order->installation_price, 2, ',', '.') }} €</p>
    @endif
    <p class="total text-right">{{ __('shop.total') }}: {{ number_format($grandTotal, 2, ',', '.') }} €</p>
</body>
</html>
