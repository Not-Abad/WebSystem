@php
    $rutaActual = request()->route()?->getName() ?? '';
    $urlMaterias = isset($plan) && $plan
        ? route('admin.catalogos.materias.plan', $plan)
        : route('admin.catalogos.materias');
@endphp

<nav class="portal-tabs" aria-label="Secciones de catálogos">
    <a href="{{ route('admin.catalogos.index') }}" @class(['activa' => str_contains($rutaActual, 'catalogos.index') || str_contains($rutaActual, 'catalogos.create') || str_contains($rutaActual, 'catalogos.edit') || str_contains($rutaActual, 'catalogos.store') || str_contains($rutaActual, 'catalogos.update')])">
        <i class="fas fa-list-alt"></i> Planes de estudio
    </a>
    <a href="{{ $urlMaterias }}" @class(['activa' => str_contains($rutaActual, 'catalogos.materias')])>
        <i class="fas fa-book-bookmark"></i> Materias
    </a>
</nav>
