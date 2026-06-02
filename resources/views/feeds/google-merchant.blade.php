<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
    <channel>
        <title>{{ $storeName }}</title>
        <link>{{ $storeUrl }}</link>
        <description>{{ $storeName }} product feed</description>
@foreach ($products as $product)
@php
    $locale = 'ca';
    $title = $product->translatedName($locale) ?? $product->code ?? (string) $product->getKey();
    $description = $product->translatedDescription($locale) ?? $title;
    $description = \Illuminate\Support\Str::limit(trim(strip_tags($description)), 5000, '…');
    $link = $storeUrl.'/products/'.$product->getKey();
    $imagePath = $product->images->first()?->url ?? '/images/dummy.jpg';
    $imageLink = url($imagePath);
    $price = number_format($product->effectivePrice(), 2, '.', '').' EUR';
    $availability = ((int) $product->stock) > 0 ? 'in stock' : 'out of stock';
    $code = $product->code !== null ? trim((string) $product->code) : '';
@endphp
        <item>
            <g:id>{{ $product->getKey() }}</g:id>
            <g:title>{{ e($title) }}</g:title>
            <g:description>{{ e($description) }}</g:description>
            <g:link>{{ $link }}</g:link>
            <g:image_link>{{ $imageLink }}</g:image_link>
            <g:availability>{{ $availability }}</g:availability>
            <g:price>{{ $price }}</g:price>
            <g:condition>new</g:condition>
            <g:brand>{{ e($storeName) }}</g:brand>
@if($code !== '')
            <g:mpn>{{ e($code) }}</g:mpn>
@else
            <g:identifier_exists>false</g:identifier_exists>
@endif
        </item>
@endforeach
    </channel>
</rss>
