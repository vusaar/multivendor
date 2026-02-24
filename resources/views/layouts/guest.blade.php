<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', '') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css" rel="stylesheet">

        <!-- Global app.css styles -->
    <link rel="stylesheet" href="{{ config('app.url').'/resources/css/app.css' }}">

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="col-md-12 mx-auto" >
           
               <div class="col-md-12 text-center" style="margin-top: 5%;">
                   <a href="/">
                       <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 w-auto" />
                   </a>
               
                LOGO
               </div> 
           

            <div class="w-full sm:max-w-md mt-12 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
