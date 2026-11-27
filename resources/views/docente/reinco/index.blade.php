@extends('layouts.modulo')

@section('content')
<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Formatos guardados</h2>
        </div>
        <a href="{{ route('docente.horario.index') }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Volver a mi horario
        </a>
    </div>

    @if ($exportaciones->isEmpty())
        <div class="docente-vacio">
            <i class="fas fa-file-excel"></i>
            <strong>Aún no hay formatos REINCO guardados.</strong>
        </div>
    @else
        <div class="tabla-wrap">
            <table class="tabla-docente-grid">
                <thead>
                    <tr>
                        <th>Evaluación</th>
                        <th>Materia y grupo</th>
                        <th>Archivo</th>
                        <th>Tamaño</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($exportaciones as $exportacion)
                        <tr>
                            <td>{{ $exportacion->evaluacion?->etiqueta() ?? 'Evaluación '.$exportacion->parcial }}</td>
                            <td>
                                <strong>{{ $exportacion->clase->materia->nombre }}</strong>
                                <small>{{ $exportacion->clase->grupo->etiqueta() }}</small>
                            </td>
                            <td><code>{{ $exportacion->archivo_ruta }}</code></td>
                            <td>{{ number_format($exportacion->tamano_bytes / 1024, 1) }} KB</td>
                            <td>{{ $exportacion->created_at->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('docente.reinco.descargar', $exportacion) }}"><i class="fas fa-download"></i> Descargar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
