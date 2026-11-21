@php
    $rutaActual = request()->route()?->getName() ?? '';
    $secciones = [
        ['noticias', 'Noticias', 'fa-newspaper', 'admin.portal.noticias'],
        ['documentos', 'Documentos', 'fa-file-lines', 'admin.portal.documentos'],
        ['reportes', 'Reportes financieros', 'fa-chart-column', 'admin.portal.reportes'],
        ['galeria', 'Galería', 'fa-images', 'admin.portal.galeria'],
    ];
@endphp

<nav class="portal-tabs" aria-label="Secciones del portal">
    @foreach ($secciones as [$clave, $etiqueta, $icono, $ruta])
        <a href="{{ route($ruta) }}" @class(['activa' => str_contains($rutaActual, '.'.$clave)])>
            <i class="fas {{ $icono }}"></i> {{ $etiqueta }}
        </a>
    @endforeach
</nav>
