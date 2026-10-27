{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    {{-- Static marketing site (former apex site, now under /web) --}}
    <url>
        <loc>{{ $baseUrl }}/web</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>

    {{-- Shop SPA (apex = storefront) --}}
    <url>
        <loc>{{ $baseUrl }}/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ $baseUrl }}/products</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ $baseUrl }}/faq</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ $baseUrl }}/games</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ $baseUrl }}/terms</loc>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>

    {{-- Active categories --}}
    @foreach ($categories as $category)
    <url>
        <loc>{{ $baseUrl }}/categories/{{ $category->id }}/products</loc>
        @if($category->updated_at)
        <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
        @endif
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- Active products --}}
    @foreach ($products as $product)
    <url>
        <loc>{{ $baseUrl }}/products/{{ $product->id }}</loc>
        @if($product->updated_at)
        <lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>
        @endif
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Active packs --}}
    @foreach ($packs as $pack)
    <url>
        <loc>{{ $baseUrl }}/packs/{{ $pack->id }}</loc>
        @if($pack->updated_at)
        <lastmod>{{ $pack->updated_at->toAtomString() }}</lastmod>
        @endif
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

</urlset>
