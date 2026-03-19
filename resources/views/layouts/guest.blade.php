<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Eyami') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <link href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="{{ asset('css/premium-design.css') }}">

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])

        <style>
            body {
                background: radial-gradient(circle at top right, rgba(225, 29, 72, 0.03), transparent),
                            radial-gradient(circle at bottom left, rgba(225, 29, 72, 0.03), transparent),
                            #FFFFFF !important;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .auth-container {
                width: 100%;
                max-width: 440px;
                padding: 2rem;
            }
            .auth-logo {
                margin-bottom: 2.5rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
            }
            .auth-logo h1 {
                font-size: 1.75rem;
                font-weight: 800;
                letter-spacing: -0.025em;
                color: var(--midnight);
                margin: 0;
                text-transform: uppercase;
            }
            .auth-logo .dot {
                color: var(--primary);
            }
        </style>
    </head>
    <body>
        <div class="auth-container">
            <div class="auth-logo">
                <a href="/">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mb-2" style="height: 48px; width: auto;" />
                </a>
                <h1>EYAMI<span class="dot">.</span></h1>
            </div>

            <div class="glass-card p-4 p-md-5">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
