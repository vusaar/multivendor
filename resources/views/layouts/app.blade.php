<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])


        <script>

               function toggle_side_menu(){
                  
                 var sidebarNode = document.querySelector('#side_bar_menu')
                   var sidebar = coreui.Sidebar.getInstance(sidebarNode)
                   sidebar.toggle()

               }
        </script>
    </head>
    <body >
        <div class="container-fluid min-vh-100 bg-light">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <div class="bg-white shadow mb-3">
                    <div class="container-fluid py-4 px-3">
                        {{ $header }}
                    </div>
           </div>
            @endif

            <div class="row" style="display: flex;">
                <div style="flex:0">
                    
                    @if (View::exists('components.sidebar'))
                        @include('components.sidebar')
                    @endif
                </div>
                <div style="flex:1">
                    <!-- Page Content -->
                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
