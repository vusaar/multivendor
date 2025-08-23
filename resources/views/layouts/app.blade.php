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

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jstree@3.3.15/dist/themes/default/style.min.css" />

    <!-- Global app.css styles -->
    <link rel="stylesheet" href="{{ config('app.url').'/resources/css/app.css' }}">

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
       
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>

               function toggle_side_menu(){
                  
                  console.log('toggle side menu clicked')
                   // toggle the sidebar
                  let sidebarNode = document.querySelector('#side_bar_menu')

                    if (!sidebarNode) {
                        console.log('Sidebar element not found');
                        return;
                    }
                   let sidebar = coreui.Sidebar.getInstance(sidebarNode)

                   if (!sidebar) {
                        console.log('Sidebar is null');
                        return;
                    }
                   sidebar.show()

               }
        </script>
        @stack('styles')
    </head>
    <body >
        <div class="container-fluid min-vh-100 bg-light">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                
            @endif

            <div class="row" style="display: flex;">
                <div class="py-3" style="flex:0">
                    
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
        

        
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>

        
        <script src="https://cdn.jsdelivr.net/npm/jstree@3.3.15/dist/jstree.min.js"></script>

    </body>
</html>
