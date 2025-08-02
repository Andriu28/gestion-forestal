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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @vite(['resources/css/styleDas.css', 'resources/js/DashFunctions.js'])
        

        <!-- esto se debe de mudar luego a local para la platilla -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    </head>
   <body class="bg-gray-50">
         <!-- Mobile sidebar overlay -->
        <div id="sidebarOverlay" class="sidebar-overlay"></div>

        <!-- Mobile dark mode toggle button -->
        <button id="mobileDarkToggle" class="mobile-dark-toggle">
            <i id="mobileDarkIcon" data-lucide="sun" class="w-6 h-6 text-white"></i>
        </button>

         <div class="flex h-screen">
            
        
                @include('layouts.navigation')

            
                <main>
                    {{ $slot }}
                </main>

            </div>
        </div>





    <script src="{{ asset('js/DashFunctions.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    
    </body>
</html>
