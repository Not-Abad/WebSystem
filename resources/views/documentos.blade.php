<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos - Bachilleres Antonio MaRD</title>

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
        <span class="portal-eyebrow">Descargas oficiales</span>
        <h1>Documentos y formatos</h1>
        <p>Convocatorias, formatos escolares, manuales y normatividad del plantel.</p>
    </header>

    <main class="portal-seccion">
        <div class="portal-contenedor">
            <div class="portal-barra">
                <div>
                    <h2 class="portal-titulo" style="font-size:1.5rem;">Archivos publicados</h2>
                    <p class="portal-bajada">Total de documentos visibles: {{ $documentos->total() }}</p>
                </div>
                <form class="portal-busqueda" method="GET" action="{{ route('documentos') }}">
                    @if ($categoria !== '')
                        <input type="hidden" name="categoria" value="{{ $categoria }}">
                    @endif
                    <input type="search" name="q" value="{{ $busqueda }}" placeholder="Buscar documento..." aria-label="Buscar documento">
                    <button type="submit"><i class="fas fa-magnifying-glass"></i> Buscar</button>
                </form>
            </div>

            <div class="portal-filtros">
                <a class="portal-filtro {{ $categoria === '' ? 'portal-filtro--activo' : '' }}" href="{{ route('documentos') }}">Todos</a>
                @foreach ($categorias as $opcion)
                    <a class="portal-filtro {{ $categoria === $opcion ? 'portal-filtro--activo' : '' }}" href="{{ route('documentos', ['categoria' => $opcion]) }}">{{ $opcion }}</a>
                @endforeach
            </div>

            <div class="portal-listado">
                @forelse ($documentos as $documento)
                    <div class="portal-fila">
                        <div class="portal-fila-texto">
                            <span class="portal-chip"><i class="fas fa-file-lines"></i> {{ $documento['categoria'] }}</span>
                            <h3>{{ $documento['titulo'] }}</h3>
                            <p>{{ $documento['nombre_archivo'] }} · {{ $documento['fecha_subida']?->format('d/m/Y') }}</p>
                        </div>
                        <div class="portal-fila-accion">
                            @if ($documento['disponible'])
                                <a class="portal-enlace" href="{{ $documento['url'] }}"><i class="fas fa-download"></i> Descargar</a>
                            @else
                                <span class="portal-chip">Sin archivo adjunto</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="portal-vacio">No se encontraron documentos con los criterios seleccionados.</div>
                @endforelse
            </div>

            <div class="portal-paginacion">{{ $documentos->links() }}</div>

            <div id="financiero" style="margin-top:56px;">
                <div class="portal-encabezado">
                    <span class="portal-eyebrow">Transparencia</span>
                    <h2 class="portal-titulo" style="font-size:1.5rem;">Reportes financieros</h2>
                    <p class="portal-bajada">Informes mensuales publicados por la administración del plantel.</p>
                </div>
                <div class="portal-listado">
                    @forelse ($reportes as $reporte)
                        <div class="portal-fila">
                            <div class="portal-fila-texto">
                                <span class="portal-chip"><i class="fas fa-chart-column"></i> {{ $reporte['fecha_publicacion']?->format('d/m/Y') }}</span>
                                <h3>{{ $reporte['titulo'] }}</h3>
                                <p>{{ $reporte['nombre_archivo'] }}</p>
                            </div>
                            <div class="portal-fila-accion">
                                @if ($reporte['disponible'])
                                    <a class="portal-enlace" href="{{ $reporte['url'] }}"><i class="fas fa-download"></i> Descargar</a>
                                @else
                                    <span class="portal-chip">Sin archivo adjunto</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="portal-vacio">Aún no hay reportes financieros publicados.</div>
                    @endforelse
                </div>
            </div>

            <a class="portal-volver" href="{{ route('home') }}"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
        </div>
    </main>

    @include('partials.footer')

</body>
</html>
