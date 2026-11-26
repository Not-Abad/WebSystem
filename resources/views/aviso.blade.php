<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $aviso->titulo }} - Bachilleres Antonio MaRD</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index_principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal-publico.css') }}">
</head>
<body style="background-color: #ffffff; color: #24292e; font-family: 'Raleway', sans-serif; margin: 0; padding: 0;">

    @include('partials.menu')

    <header class="portal-hero">
        <span class="portal-eyebrow">Aviso institucional</span>
        <h1 style="font-size:2rem;">{{ $aviso->titulo }}</h1>
        <p><i class="fas fa-calendar-day"></i> Publicado el {{ $aviso->fecha_publicacion?->format('d/m/Y') }}</p>
    </header>

    <main class="portal-seccion">
        <div class="portal-contenedor" style="max-width:900px;">
            <article class="portal-nota">
                {!! nl2br(e($aviso->contenido)) !!}
            </article>

            <a class="portal-volver" href="{{ route('avisos') }}"><i class="fas fa-arrow-left"></i> Ver todos los avisos</a>
        </div>
    </main>

    @include('partials.footer')

</body>
</html>
