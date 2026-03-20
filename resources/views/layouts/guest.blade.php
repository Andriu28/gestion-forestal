<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/styleDas.css',])

        <script>
        const storedTheme = localStorage.getItem('theme') || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
            if (storedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        </script>
    </head>
    <body class="min-h-screen bg-neutral-200 dark:bg-custom-dark transition-colors duration-300">
        

        <div class="min-h-screen flex items-center justify-center p-2 sm:p-3 md:p-4">
            
                {{ $slot }}
           
        </div>
        @vite(['resources/js/app.js'])
        
    </body>
</html>
