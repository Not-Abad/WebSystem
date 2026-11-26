<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avisos - Bachilleres Antonio MaRD</title>

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
        <span class="portal-eyebrow">Comunicación institucional</span>
        <h1>Avisos y noticias</h1>
        <p>Información oficial para estudiantes, familias y personal del plantel.</p>
    </header>

    <main class="portal-seccion">
        <div class="portal-contenedor">
            <div class="portal-barra">
                <div>
                    <h2 class="portal-titulo" style="font-size:1.5rem;">Publicaciones recientes</h2>
                    <p class="portal-bajada">Total de avisos visibles: {{ $avisos->total() }}</p>
                </div>
                <form class="portal-busqueda" method="GET" action="{{ route('avisos') }}">
                    <input type="search" name="q" value="{{ $busqueda }}" placeholder="Buscar aviso..." aria-label="Buscar aviso">
                    <button type="submit"><i class="fas fa-magnifying-glass"></i> Buscar</button>
                </form>
            </div>

            <div class="portal-grid">
                @forelse ($avisos as $aviso)
                    <article class="portal-tarjeta">
                        <span class="portal-chip"><i class="fas fa-calendar-day"></i> {{ $aviso->fecha_publicacion?->format('d/m/Y') }}</span>
                        <h3>{{ $aviso->titulo }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $aviso->contenido), 180) }}</p>
                        <a class="portal-enlace" href="{{ route('avisos.show', $aviso) }}">Leer aviso completo <i class="fas fa-arrow-right"></i></a>
                    </article>
                @empty
                    <div class="portal-vacio">No se encontraron avisos con los criterios seleccionados.</div>
                @endforelse
            </div>

            <div class="portal-paginacion">{{ $avisos->links() }}</div>

            <a class="portal-volver" href="{{ route('home') }}"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
        </div>
    </main>

    @include('partials.footer')

</body>
</html>
