@extends('layouts.app')

@section('title', 'Inicio | ' . config('app.name'))

@section('content')
    <!-- ============================================
         SECCIÓN HERO
         ============================================ -->
    <section class="hero">
        <p class="eyebrow">
            Proyecto base del curso
        </p>

        <h1>
            Desarrollo backend con Laravel
        </h1>

        <p class="hero__description">
            Esta aplicación servirá como punto de partida para aprender arquitectura MVC,
            Blade, Eloquent, validación, pruebas, control de versiones y despliegue.
        </p>

        <span class="hero__status" data-app-status>
            Comprobando JavaScript y Vite…
        </span>
    </section>

    <!-- ============================================
         SECCIÓN FEATURES (CARACTERÍSTICAS)
         ============================================ -->
    <section class="features" aria-label="Características del proyecto">
        <article class="card">
            <h2>Entorno local</h2>
            <p>
                La aplicación se ejecuta en Laravel Herd con PHP 8.5.
            </p>
        </article>

        <article class="card">
            <h2>Buenas prácticas</h2>
            <p>
                El proyecto incorporará pruebas, formato de código, migraciones
                y revisión mediante Git.
            </p>
        </article>

        <article class="card">
            <h2>Publicación</h2>
            <p>
                Posteriormente se conectará con Neon, GitHub y Render.
            </p>
        </article>
    </section>
@endsection