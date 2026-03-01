<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('shop.orders') }} #{{ $order->id }}</title>
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
    <h1>{{ __('shop.orders') }} #{{ $order->id }}</h1>
    <p>{{ __('shop.status.pending') }}: {{ $order->status }}</p>
    <p>{{ __('shop.orders') }}: {{ $order->order_date?->format('d/m/Y H:i') }}</p>

    @php
        $primary = $order->client->contacts->where('is_primary', true)->first();
        $addr = $order->addresses->where('type', 'shipping')->first();
    @endphp
    <h2>Client</h2>
    <p>{{ $primary?->name }} {{ $primary?->surname }}<br>
    {{ $order->client->login_email }}</p>
    @if($addr)
    <p>{{ $addr->street }}, {{ $addr->postal_code }} {{ $addr->city }}</p>
    @endif

    <h2>Línies</h2>
    <table>
        <thead>
            <tr>
                <th>Producte / Pack</th>
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
    <p class="total text-right">{{ __('shop.total') }}: {{ number_format($order->lines->sum(fn($l) => $l->line_total), 2, ',', '.') }} €</p>
</body>
</html>
