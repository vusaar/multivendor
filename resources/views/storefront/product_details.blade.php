<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} | {{ $product->vendor->shop_name ?? 'Storefront' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --glass: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.4);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            line-height: 1.6;
            padding-bottom: 100px; /* Space for sticky button */
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        /* Image Gallery */
        .hero-image {
            width: 100%;
            height: 400px;
            background-position: center;
            background-size: cover;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Content Area */
        .content {
            padding: 24px;
            margin-top: -40px;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
        }

        .badge-row {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .badge {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 99px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-primary { background: #e0e7ff; color: #4338ca; }
        .badge-secondary { background: #f1f5f9; color: #475569; }

        h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .price-tag {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .price-tag span {
            font-size: 16px;
            color: var(--text-muted);
            font-weight: 400;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: block;
        }

        .description {
            font-size: 16px;
            color: #334155;
            margin-bottom: 30px;
            white-space: pre-line;
        }

        /* Vendor Card */
        .vendor-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: white;
            border-radius: 20px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .vendor-logo {
            width: 50px;
            height: 50px;
            background: #f1f5f9;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary);
            font-size: 20px;
            overflow: hidden;
        }

        .vendor-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vendor-info h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .vendor-info p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
        }

        /* Variation Table */
        .variations {
            margin-bottom: 30px;
        }

        .variation-pill {
            display: inline-block;
            padding: 8px 16px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-right: 8px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        /* Sticky Footer */
        .sticky-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: rgba(248, 250, 252, 0.8);
            backdrop-filter: blur(10px);
            z-index: 100;
        }

        .btn-whatsapp {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #25D366;
            color: white;
            text-decoration: none;
            padding: 18px;
            border-radius: 18px;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
            transition: transform 0.2s;
        }

        .btn-whatsapp:active {
            transform: scale(0.98);
        }

        .btn-whatsapp svg {
            width: 24px;
            height: 24px;
            fill: currentColor;
        }

        /* Utils */
        .text-center { text-align: center; }
        .mb-20 { margin-bottom: 20px; }

    </style>
</head>
<body>

    <div class="container">
        <!-- Hero Section -->
        @php
            $mainImage = $product->images->first() ? asset('storage/' . ($product->images->first()->image ?? $product->images->first()->image_path)) : asset('storage/placeholder.png');
        @endphp
        <div class="hero-image" style="background-image: url('{{ $mainImage }}')"></div>

        <div class="content">
            <div class="glass-card">
                <div class="badge-row">
                    @if($product->category)
                        <span class="badge badge-primary">{{ $product->category->name }}</span>
                    @endif
                    @if($product->brand)
                        <span class="badge badge-secondary">{{ $product->brand->name }}</span>
                    @endif
                </div>

                <h1>{{ $product->name }}</h1>
                
                <div class="price-tag">
                    ${{ number_format($product->price, 2) }}
                    <span>USD</span>
                </div>

                <div class="vendor-card">
                    <div class="vendor-logo">
                        @if($product->vendor && $product->vendor->logo)
                            <img src="{{ asset('storage/' . $product->vendor->logo) }}" alt="{{ $product->vendor->shop_name }}">
                        @else
                            {{ substr($product->vendor->shop_name ?? 'S', 0, 1) }}
                        @endif
                    </div>
                    <div class="vendor-info">
                        <h3>{{ $product->vendor->shop_name ?? 'Unknown Shop' }}</h3>
                        <p>{{ $product->vendor->address ?? 'Verified Vendor' }}</p>
                    </div>
                </div>

                <span class="section-title">About this item</span>
                <div class="description">
                    {{ $product->description }}
                </div>

                @if($product->variations->count() > 0)
                    <span class="section-title">Available Options</span>
                    <div class="variations">
                        @foreach($product->variations as $variation)
                            <div class="variation-pill">
                                @foreach($variation->attributeValues as $av)
                                    <strong>{{ $av->attribute->name }}:</strong> {{ $av->value }} 
                                @endforeach
                                — ${{ number_format($variation->price, 2) }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sticky WhatsApp Footer -->
    <div class="sticky-footer text-center">
        @php
            $phone = $product->vendor->phone ?? '';
            $message = urlencode("Hi " . ($product->vendor->shop_name ?? 'there') . "! I'm interested in your " . $product->name . " (Price: $" . number_format($product->price, 2) . "). Is it available?");
            $waLink = $phone ? "https://wa.me/" . preg_replace('/[^0-9]/', '', $phone) . "?text=" . $message : "#";
        @endphp
        
        @if($phone)
            <a href="{{ $waLink }}" class="btn-whatsapp">
                <svg viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.417-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.305 1.652zm6.599-3.835c1.474.875 3.044 1.335 4.661 1.335 5.246 0 9.513-4.269 9.515-9.517.002-5.247-4.266-9.515-9.514-9.515-5.248 0-9.516 4.268-9.519 9.515-.001 1.841.52 3.633 1.503 5.174l-.982 3.589 3.674-.963zm9.445-6.828c-.282-.141-1.669-.823-1.928-.917-.258-.094-.446-.141-.634.141-.188.281-.727.917-.891 1.104-.164.188-.329.212-.611.071-.282-.141-1.189-.438-2.264-1.396-.837-.747-1.401-1.67-1.565-1.952-.164-.282-.018-.435.122-.575.127-.125.282-.329.423-.494.141-.164.188-.282.282-.47.094-.188.047-.353-.023-.494-.071-.141-.634-1.528-.869-2.092-.23-.553-.464-.478-.634-.487-.164-.008-.352-.01-.54-.01s-.494.071-.752.353c-.258.282-.987.964-.987 2.351s1.011 2.727 1.22 3.009c.212.282 1.99 3.04 4.821 4.256 2.83 1.216 2.83.81 3.321.764.491-.046 1.669-.681 1.903-1.34s.23-1.222.164-1.34c-.066-.118-.258-.188-.54-.329z"/></svg>
                Chat with Shop
            </a>
        @else
            <p style="color: var(--text-muted)">Contact details unavailable for this vendor.</p>
        @endif
    </div>

</body>
</html>
