<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

      @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
    </style>

     <!-- =======================
         Script de inicialización de tema ANTES de los estilos
         para evitar parpadeo (igual que en app.blade.php)
    ======================== -->
    <script>
        // Inicializa el tema (oscuro/claro) ANTES de renderizar
        const storedTheme = localStorage.getItem('theme') || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (storedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>

</head>
<body class="min-h-screen bg-custom-light dark:bg-custom-dark text-custom-dark dark:text-custom-light overflow-hidden relative flex items-center justify-center transition-colors duration-300">
    
    <!-- Elementos decorativos -->
    <div class="absolute w-[900px] h-[900px] bg-custom-gold-light/40 rounded-full opacity-40 pointer-events-none top-[-470px] left-[-370px] animate-float" style="animation-delay: -4s;"></div>
    <div class="absolute w-[450px] h-[450px] bg-custom-gold-medium/40 rounded-full opacity-40 pointer-events-none bottom-[-150px] right-[-150px] animate-float" style="animation-delay: -3s;"></div>

    <main class="container relative z-10 text-center p-5 max-w-[600px] w-full">
        <!-- Badge de estado -->
        <div class="inline-block px-8 py-2 bg-neutral-200/90 dark:bg-custom-gold-dark/20 border-[2px] border-custom-gold-darkest dark:border-yellow-700 rounded-2xl text-custom-gold-darkest dark:text-yellow-600 text-xl font-semibold uppercase tracking-wide mb-10 transition-colors duration-300">
            Error @yield('code')
        </div>
        
        <!-- Código de error animado -->
        <div class="text-[140px] sm:text-[clamp(80px,15vw,200px)] font-black bg-gradient-to-br from-[#3E2723] via-[#4E342E] to-[#6D4C41] dark:from-[#3b220f] dark:via-[#61361d] dark:to-[#794606] bg-clip-text text-transparent mb-5 leading-none animate-float transition-colors duration-300" style="animation-delay: -6s;">
            @yield('code')
        </div>

        <!-- Título -->
        <h1 class="text-[clamp(24px,5vw,42px)] font-bold mb-4 tracking-tight text-custom-dark dark:text-custom-light transition-colors duration-300">
            @yield('message')
        </h1>

        <!-- Descripción personalizada por error -->
        <div class="text-[clamp(14px,2vw,18px)] text-custom-dark/70 dark:text-custom-light/70 mb-10 leading-relaxed transition-colors duration-300">
            @yield('description')
        </div>

        <!-- Botones de acción personalizados por error -->
        <div class="flex gap-4 flex-wrap justify-center mb-16">
            @yield('buttons')
        </div>
    </main>

</body>
</html>