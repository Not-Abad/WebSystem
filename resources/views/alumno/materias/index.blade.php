@extends('layouts.modulo')

@section('content')
<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Mis materias</h2>
            <p class="docente-ayuda">
                Consulta los criterios, las fechas de evaluación y las calificaciones que tu docente ha publicado.
            </p>
        </div>
    </div>

    @if ($clases->isEmpty())
        <div class="docente-vacio">
            <i class="fas fa-book-open"></i>
            <strong>Aún no hay materias asignadas a tu grupo.</strong>
        </div>
    @else
        <div class="docente-tarjetas">
            @foreach ($clases as $clase)
                <a class="docente-tarjeta" href="{{ route('alumno.materias.show', $clase) }}">
                    <span class="docente-tarjeta-clave">{{ $clase->materia->clave }}</span>
                    <strong>{{ $clase->materia->nombre }}</strong>
                    <span>{{ $clase->grupo->etiqueta() }} · {{ $clase->docente?->nombreCompleto() ?? 'Docente por asignar' }}</span>
                    <span class="docente-tarjeta-pie">
                        {{ $clase->evaluaciones->count() }} evaluaciones
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</section>
@endsection
