@extends('layouts.modulo')

@section('content')
@php
    $nombreAlumno = fn ($alumno) => trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre);
@endphp

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Detalle de la clase</h2>
            <p class="docente-ayuda">Información de la materia, el grupo y los alumnos de esta asignación.</p>
        </div>
        <a href="{{ route('docente.horario.index') }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Volver a mi horario
        </a>
    </div>

    <div class="docente-detalle">
        <div><span>Materia</span><strong>{{ $clase->materia->clave }} · {{ $clase->materia->nombre }}</strong></div>
        <div><span>Grupo</span><strong>{{ $clase->grupo->etiqueta() }} · Semestre {{ $clase->grupo->semestre?->numero_semestre ?? '—' }}</strong></div>
        <div><span>Plan</span><strong>{{ $clase->grupo->plan?->etiqueta() ?? 'Plan vigente' }}</strong></div>
        <div><span>Docente</span><strong>{{ $docente->nombreCompleto() }}</strong></div>
        <div><span>Alumnos activos</span><strong>{{ $alumnos->count() }}</strong></div>
        <div><span>Bloques asignados</span><strong>{{ $clase->horarios->count() }}</strong></div>
    </div>

    @if ($horarioSeleccionado)
        <div class="docente-resalta">
            <i class="fas fa-clock"></i>
            Bloque seleccionado:
            <strong>{{ $horarioSeleccionado->dia_semana === 'Miercoles' ? 'Miércoles' : $horarioSeleccionado->dia_semana }}</strong>
            {{ substr($horarioSeleccionado->hora_inicio, 0, 5) }}–{{ substr($horarioSeleccionado->hora_fin, 0, 5) }}
            · {{ $horarioSeleccionado->salon?->nombre ?? 'Sin salón asignado' }}
        </div>
    @endif

    @if ($clase->horarios->isNotEmpty())
        <div class="docente-horarios">
            @foreach ($clase->horarios->sortBy(['dia_semana', 'hora_inicio']) as $horario)
                <a href="{{ route('docente.clases.show', $clase) }}?horario={{ $horario->id_horario }}"
                   class="docente-horario-chip {{ $horarioSeleccionado?->id_horario === $horario->id_horario ? 'activo' : '' }}">
                    {{ $horario->dia_semana === 'Miercoles' ? 'Miércoles' : $horario->dia_semana }}
                    {{ substr($horario->hora_inicio, 0, 5) }}–{{ substr($horario->hora_fin, 0, 5) }}
                    <small>{{ $horario->salon?->clave ?? 'Sin salón' }}</small>
                </a>
            @endforeach
        </div>
    @endif
</section>

<nav class="docente-nav" aria-label="Secciones de la clase">
    <a href="#asistencia"><i class="fas fa-calendar-check"></i> Asistencia</a>
    <a href="#evaluaciones"><i class="fas fa-calendar-days"></i> Evaluaciones</a>
    <a href="#criterios"><i class="fas fa-list-check"></i> Criterios</a>
    <a href="#calificaciones"><i class="fas fa-star-half-stroke"></i> Calificaciones</a>
    <a href="#reinco"><i class="fas fa-file-excel"></i> REINCO</a>
</nav>

<section class="modulo-card" id="asistencia">
    <div class="docente-cabecera">
        <div>
            <h2>Asistencia diaria</h2>
            <p class="docente-ayuda">Se registra por alumno, fecha y materia. Al volver a guardar se actualiza el registro.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('docente.clases.show', $clase) }}" class="docente-filtro">
        <input type="hidden" name="horario" value="{{ $horarioSeleccionado?->id_horario ?? '' }}">
        <label for="fecha">Fecha a consultar</label>
        <input type="date" id="fecha" name="fecha" value="{{ $fechaAsistencia }}" max="{{ now()->toDateString() }}">
        <button type="submit" class="btn-modulo btn-secundario"><i class="fas fa-magnifying-glass"></i> Consultar</button>
    </form>

    <form method="POST" action="{{ route('docente.clases.asistencia', $clase) }}">
        @csrf
        <input type="hidden" name="fecha" value="{{ $fechaAsistencia }}">
        <div class="tabla-wrap">
            <table class="tabla-docente-grid">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        @foreach ($estatusAsistencia as $estatus)
                            <th>{{ ucfirst($estatus) }}</th>
                        @endforeach
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $alumno)
                        @php $registro = $asistencias->get($alumno->id_alumno); @endphp
                        <tr>
                            <td>
                                <input type="hidden" name="asistencias[{{ $loop->index }}][id_alumno]" value="{{ $alumno->id_alumno }}">
                                <strong>{{ $nombreAlumno($alumno) }}</strong>
                            </td>
                            @foreach ($estatusAsistencia as $estatus)
                                <td>
                                    <input type="radio"
                                           name="asistencias[{{ $loop->parent->index }}][estatus]"
                                           value="{{ $estatus }}"
                                           id="asist-{{ $alumno->id_alumno }}-{{ $estatus }}"
                                           {{ ($registro?->estatus ?? \App\Models\Asistencia::PRESENTE) === $estatus ? 'checked' : '' }}>
                                    <label for="asist-{{ $alumno->id_alumno }}-{{ $estatus }}" class="sr-etiqueta">{{ ucfirst($estatus) }}</label>
                                </td>
                            @endforeach
                            <td>
                                <input type="text"
                                       name="asistencias[{{ $loop->index }}][observaciones]"
                                       maxlength="200"
                                       value="{{ old('asistencias.'.$loop->index.'.observaciones', $registro?->observaciones) }}"
                                       placeholder="Opcional">
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($estatusAsistencia) + 2 }}">No hay alumnos activos en este grupo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($alumnos->isNotEmpty())
            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario"><i class="fas fa-floppy-disk"></i> Guardar asistencia</button>
            </div>
        @endif
    </form>
</section>

<section class="modulo-card" id="evaluaciones">
    <div class="docente-cabecera">
        <div>
            <h2>Fechas de exámenes y entregas</h2>
            <p class="docente-ayuda">Configura las tres evaluaciones. El tipo determina qué fechas se capturan; serán visibles para los alumnos.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('docente.clases.evaluaciones', $clase) }}">
        @csrf
        <div class="docente-evaluaciones">
            @foreach ([1, 2, 3] as $parcial)
                @php
                    $evaluacion = $clase->evaluaciones->firstWhere('parcial', $parcial);
                    $prefijo = 'evaluaciones.'.$loop->index;
                    $nombre = 'evaluaciones['.$loop->index.']';
                    $tipo = old($prefijo.'.tipo', $evaluacion?->tipo ?? 'examen');
                    $conExamen = in_array($tipo, ['examen', 'ambos'], true);
                    $conProyecto = in_array($tipo, ['proyecto', 'ambos'], true);
                @endphp
                <div class="docente-evaluacion">
                    <h3>{{ ['Primera', 'Segunda', 'Tercera'][$parcial - 1] }} evaluación</h3>
                    <input type="hidden" name="{{ $nombre }}[parcial]" value="{{ $parcial }}">
                    <label>
                        Tipo
                        <select class="evaluacion-tipo" name="{{ $nombre }}[tipo]">
                            @foreach (['examen' => 'Examen', 'proyecto' => 'Proyecto', 'ambos' => 'Examen y proyecto'] as $valor => $etiqueta)
                                <option value="{{ $valor }}" {{ $tipo === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label data-fecha="examen" {{ $conExamen ? '' : 'hidden' }}>
                        Fecha de examen
                        <input type="date" name="{{ $nombre }}[fecha_examen]"
                               value="{{ old($prefijo.'.fecha_examen', $evaluacion?->fecha_examen?->format('Y-m-d')) }}">
                    </label>
                    <label data-fecha="proyecto" {{ $conProyecto ? '' : 'hidden' }}>
                        Fecha de entrega de proyecto
                        <input type="date" name="{{ $nombre }}[fecha_entrega_proyecto]"
                               value="{{ old($prefijo.'.fecha_entrega_proyecto', $evaluacion?->fecha_entrega_proyecto?->format('Y-m-d')) }}">
                    </label>
                </div>
            @endforeach
        </div>
        <div class="form-acciones">
            <button type="submit" class="btn-modulo btn-primario"><i class="fas fa-floppy-disk"></i> Guardar fechas</button>
        </div>
    </form>
</section>

<section class="modulo-card" id="criterios">
    <div class="docente-cabecera">
        <div>
            <h2>Banco de criterios de evaluación</h2>
            <p class="docente-ayuda">
                Selecciona simultáneamente los criterios que aplicarás (examen, tareas, asistencia, proyecto,
                actividades en clase y otros). Cada porcentaje mínimo es 1 y el total de la evaluación no puede ser mayor a 100%.
            </p>
        </div>
    </div>

    @foreach ([1, 2, 3] as $parcial)
        @php
            $evaluacion = $clase->evaluaciones->firstWhere('parcial', $parcial);
            $criteriosEvaluacion = $evaluacion?->criterios ?? collect();
        @endphp
        <div class="docente-bloque" data-criterios-parcial="{{ $parcial }}">
            <h3>{{ ['Primera', 'Segunda', 'Tercera'][$parcial - 1] }} evaluación</h3>
            @if (! $evaluacion)
                <p class="docente-ayuda">Guarda primero las fechas de evaluación para poder seleccionar los criterios.</p>
            @else
                <form method="POST" action="{{ route('docente.clases.criterios', [$clase, $evaluacion]) }}" class="formulario-criterios">
                    @csrf
                    <div class="banco-criterios">
                        @foreach ($bancoEvidencias as $indice => $evidencia)
                            @include('docente.clases._fila-criterio', [
                                'indice' => $indice,
                                'evidencia' => $evidencia,
                                'criterio' => $criteriosEvaluacion->firstWhere('tipo_evidencia', $evidencia),
                            ])
                        @endforeach
                    </div>
                    <div class="docente-criterios-pie">
                        <span class="criterios-total">Total seleccionado: <strong>0</strong> % / 100 %</span>
                        <button type="submit" class="btn-modulo btn-primario"><i class="fas fa-floppy-disk"></i> Guardar criterios</button>
                    </div>
                </form>
            @endif
        </div>
    @endforeach
</section>

<section class="modulo-card" id="calificaciones">
    <div class="docente-cabecera">
        <div>
            <h2>Calificaciones por criterio</h2>
            <p class="docente-ayuda">
                Captura el porcentaje obtenido por alumno (0–100). Cada evidencia se convierte a una nota de 0.00 a
                10.00 y la calificación del parcial es el promedio ponderado de esas notas.
            </p>
        </div>
    </div>

    @foreach ([1, 2, 3] as $parcial)
        @php
            $evaluacion = $clase->evaluaciones->firstWhere('parcial', $parcial);
            $criteriosEvaluacion = $evaluacion?->criterios ?? collect();
        @endphp
        <div class="docente-bloque">
            <h3>{{ ['Primera', 'Segunda', 'Tercera'][$parcial - 1] }} evaluación</h3>
            @if ($criteriosEvaluacion->isEmpty())
                <p class="docente-ayuda">Captura los criterios de esta evaluación para poder calificar.</p>
            @else
                <form method="POST" action="{{ route('docente.clases.calificaciones', [$clase, $evaluacion]) }}">
                    @csrf
                    <div class="tabla-wrap">
                        <table class="tabla-docente-grid">
                            <thead>
                                <tr>
                                    <th>Alumno</th>
                                    @foreach ($criteriosEvaluacion as $criterio)
                                        <th>{{ $criterio->nombre_rubro }}<small>{{ $criterio->porcentaje }} %</small></th>
                                    @endforeach
                                    <th>Calificación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $alumno)
                                    @php
                                        $calificacion = \App\Support\CalificacionService::calificacionParcial(
                                            $criteriosEvaluacion,
                                            $alumno
                                        )['calificacion'];
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $nombreAlumno($alumno) }}</strong></td>
                                        @foreach ($criteriosEvaluacion as $criterio)
                                            @php
                                                $nota = $criterio->calificaciones->firstWhere('id_alumno', $alumno->id_alumno);
                                                $campo = 'calificaciones.'.$alumno->id_alumno.'.'.$criterio->id_criterio;
                                            @endphp
                                            <td>
                                                <input type="number" min="0" max="100" step="0.01"
                                                       name="calificaciones[{{ $alumno->id_alumno }}][{{ $criterio->id_criterio }}]"
                                                       value="{{ old($campo, $nota?->porcentaje_obtenido) }}"
                                                       placeholder="0–100" required>
                                            </td>
                                        @endforeach
                                        <td><strong class="docente-nota">{{ number_format($calificacion, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($alumnos->isNotEmpty())
                        <div class="form-acciones">
                            <button type="submit" class="btn-modulo btn-primario"><i class="fas fa-floppy-disk"></i> Guardar calificaciones</button>
                        </div>
                    @endif
                </form>
            @endif
        </div>
    @endforeach
</section>

<section class="modulo-card" id="reinco">
    <div class="docente-cabecera">
        <div>
            <h2>Formatos REINCO</h2>
            <p class="docente-ayuda">
                Evaluación que aplican los docentes a los alumnos: Registran asistencias, evidencias y calificaciones
                de exámenes en el formato REINCO.
            </p>
            <p class="docente-ayuda">
                Genera el Excel con REINCO 205 (asistencia por parcial), 206-A (evidencias y porcentajes), su reverso
                y 206-B (calificación integrada y estatus). Incluye CURP, firmas para cotejo DGB y configuración de
                impresión. Puedes descargarlo o guardarlo en el gestor de archivos.
            </p>
        </div>
        <a href="{{ route('docente.reinco.index') }}" class="btn-modulo btn-secundario">
            <i class="fas fa-folder-open"></i> Ver formatos guardados
        </a>
    </div>

    @foreach ([1, 2, 3] as $parcial)
        @php
            $evaluacion = $clase->evaluaciones->firstWhere('parcial', $parcial);
        @endphp
        <div class="docente-bloque">
            <h3>{{ ['Primera', 'Segunda', 'Tercera'][$parcial - 1] }} evaluación</h3>
            @if (! $evaluacion || $evaluacion->criterios->isEmpty())
                <p class="docente-ayuda">Guarda las fechas y los criterios de esta evaluación para generar el formato.</p>
            @else
                <form method="POST" action="{{ route('docente.clases.reinco', [$clase, $evaluacion]) }}">
                    @csrf
                    @include('docente.clases._opciones-reinco')
                    <div class="docente-acciones">
                        <button type="submit" name="destino" value="descargar" class="btn-modulo btn-primario">
                            <i class="fas fa-download"></i> Descargar Excel
                        </button>
                        <button type="submit" name="destino" value="guardar" class="btn-modulo btn-secundario">
                            <i class="fas fa-folder-plus"></i> Guardar en el gestor
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endforeach

    @if ($clase->reincoExportaciones->isNotEmpty())
        <h3 class="docente-subtitulo">Formatos guardados de esta clase</h3>
        <div class="tabla-wrap">
            <table class="tabla-docente-grid">
                <thead><tr><th>Evaluación</th><th>Archivo</th><th>Fecha</th><th></th></tr></thead>
                <tbody>
                    @foreach ($clase->reincoExportaciones as $exportacion)
                        <tr>
                            <td>{{ $exportacion->etiqueta() }}</td>
                            <td>{{ basename($exportacion->archivo_ruta) }}</td>
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

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.docente-evaluacion').forEach((bloque) => {
        const tipo = bloque.querySelector('.evaluacion-tipo');
        const fechaExamen = bloque.querySelector('[data-fecha="examen"]');
        const fechaProyecto = bloque.querySelector('[data-fecha="proyecto"]');
        if (! tipo || ! fechaExamen || ! fechaProyecto) {
            return;
        }

        function actualizarFechas() {
            fechaExamen.hidden = ! ['examen', 'ambos'].includes(tipo.value);
            fechaProyecto.hidden = ! ['proyecto', 'ambos'].includes(tipo.value);
        }

        tipo.addEventListener('change', actualizarFechas);
        actualizarFechas();
    });

    document.querySelectorAll('[data-criterios-parcial]').forEach((contenedor) => {
        const banco = contenedor.querySelector('.banco-criterios');
        if (! banco) {
            return;
        }

        const total = contenedor.querySelector('.criterios-total');
        const valorTotal = total.querySelector('strong');
        const botonGuardar = contenedor.querySelector('button[type="submit"]');

        function recalcular() {
            let suma = 0;
            banco.querySelectorAll('[data-criterio-opcion]').forEach((opcion) => {
                const activa = opcion.querySelector('.criterio-check').checked;
                opcion.classList.toggle('seleccionado', activa);
                opcion.querySelectorAll('.criterio-porcentaje, .criterio-fecha').forEach((campo) => {
                    campo.disabled = ! activa;
                    if (! activa) campo.value = '';
                });
                if (activa) {
                    suma += parseFloat(opcion.querySelector('.criterio-porcentaje').value || '0');
                }
            });

            const excede = suma > 100;
            valorTotal.textContent = suma.toFixed(2).replace(/\.00$/, '');
            total.classList.toggle('es-invalido', excede);
            botonGuardar.disabled = excede;
            total.title = excede ? 'La suma de los porcentajes no puede ser mayor a 100.' : '';
        }

        banco.addEventListener('change', recalcular);
        banco.addEventListener('input', recalcular);
        recalcular();
    });
})();
</script>
@endpush
