<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Proyecto base del curso de desarrollo web con Laravel.">

    <title>
        @yield('title', config('app.name'))
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body>
    <!-- ============================================
         HEADER
         ============================================ -->
    <header class="site-header">
        <div class="site-header__content">
            <a class="brand" href="{{ route('home') }}">
                {{ config('app.name') }}
            </a>

            <span class="environment">
                Ambiente: {{ app()->environment() }}
            </span>
        </div>
    </header>

    <!-- ============================================
         CONTENIDO PRINCIPAL
         ============================================ -->
    <main class="container">
        @yield('content')
    </main>

    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer class="site-footer">
        <div class="site-footer__content">
            <span>TM4100 - Desarrollo de Aplicaciones Interactivas I</span>
            <span>Laravel {{ app()->version() }}</span>
        </div>
    </footer>
</body>
</html>