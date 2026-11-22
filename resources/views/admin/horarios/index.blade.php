@extends('layouts.modulo')

@section('content')
    @php
        $hayGrupo = (bool) $grupo;
    @endphp

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-filter"></i> Selecciona el grupo</h2>
                <p>Primero elige el plan y después el grupo. Desde 5º semestre los grupos se identifican por área propedéutica.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.horarios.index') }}" class="filtros" id="formulario-horario">
            <div class="filtro">
                <label for="id_plan">Plan de estudios</label>
                <select id="id_plan" name="id_plan" onchange="document.getElementById('id_grupo').value=''; this.form.submit();">
                    <option value="">Selecciona el plan</option>
                    @foreach ($planes as $id => $nombre)
                        <option value="{{ $id }}" @selected(($filtros['id_plan'] ?? '') == $id)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filtro">
                <label for="id_grupo">Grupo</label>
                <select id="id_grupo" name="id_grupo" onchange="this.form.submit();" @disabled(! $hayGrupo && empty($grupos))>
                    <option value="">Selecciona el grupo</option>
                    @foreach ($grupos as $id => $nombre)
                        <option value="{{ $id }}" @selected(($filtros['id_grupo'] ?? '') == $id)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filtro">
                <button type="submit" class="btn-modulo btn-primario" style="width: 100%;">
                    <i class="fas fa-magnifying-glass"></i> Cargar horario
                </button>
            </div>
        </form>

        @if ($hayGrupo)
            <div class="detalle-lista" style="margin-top: 8px;">
                <div class="detalle-item">
                    <span>Plan</span>
                    <strong>{{ $grupo->plan?->clave ?? '—' }}</strong>
                </div>
                <div class="detalle-item">
                    <span>Grupo</span>
                    <strong>{{ $grupo->nombre }}</strong>
                </div>
                <div class="detalle-item">
                    <span>Semestre</span>
                    <strong>{{ $grupo->semestre?->numero_semestre ?? '—' }}</strong>
                </div>
                <div class="detalle-item">
                    <span>Alumnos activos</span>
                    <strong>{{ $alumnos_grupo }}</strong>
                </div>
                @if ($grupo->area)
                    <div class="detalle-item">
                        <span>Área propedéutica</span>
                        <strong>{{ $grupo->area->nombre }}</strong>
                    </div>
                @endif
            </div>
        @endif
    </section>

    <div class="horario-layout">
        <section class="modulo-card selector-panel {{ $hayGrupo ? '' : 'panel-bloqueado' }}">
            <div class="modulo-card-header">
                <div>
                    <h2><i class="fas fa-book-bookmark"></i> 1. Materia</h2>
                    <p>Solo materias del plan, semestre y área del grupo, más el tronco común.</p>
                </div>
            </div>

            @if ($hayGrupo)
                <input type="search" id="buscar-materia" placeholder="Buscar materia por nombre" autocomplete="off">
                <div class="selector-lista" id="lista-materias">
                    @forelse ($materias as $materia)
                        <button type="button" class="selector-item materia-item"
                                data-id="{{ $materia->id_materia }}"
                                data-horas="{{ $materia->horas_semana }}"
                                data-nombre="{{ $materia->nombre }}"
                                data-clave="{{ $materia->clave }}">
                            <strong>{{ $materia->nombre }}</strong>
                            <small>{{ $materia->clave }} · máximo {{ $materia->horas_semana }} h</small>
                            <small class="materia-contador">0 / {{ $materia->horas_semana }} h</small>
                        </button>
                    @empty
                        <p class="vacio">El plan no tiene materias disponibles para este semestre y área. Agrégalas en Catálogos.</p>
                    @endforelse
                </div>
            @else
                <p class="vacio">Selecciona un grupo para ver sus materias.</p>
            @endif
        </section>

        <section class="modulo-card selector-panel panel-bloqueado" id="panel-docente">
            <div class="modulo-card-header">
                <div>
                    <h2><i class="fas fa-chalkboard-user"></i> 2. Docente</h2>
                    <p>Se habilita al seleccionar una materia.</p>
                </div>
            </div>

            @if ($hayGrupo)
                <input type="search" id="buscar-docente" placeholder="Buscar docente por nombre" autocomplete="off" disabled>
                <div class="selector-lista" id="lista-docentes">
                    @forelse ($docentes as $docente)
                        <button type="button" class="selector-item docente-item" disabled
                                data-id="{{ $docente['id'] }}"
                                data-nombre="{{ $docente['nombre'] }}"
                                data-formacion="{{ $docente['formacion'] }}"
                                data-carga="{{ $docente['carga'] }}"
                                data-limite="{{ $docente['horas_semanales'] }}">
                            <strong>{{ $docente['nombre'] }}</strong>
                            <small class="selector-detalle">{{ $docente['formacion'] }}</small>
                            <small class="selector-carga">Carga actual: {{ $docente['carga'] }} / {{ $docente['horas_semanales'] }} h</small>
                        </button>
                    @empty
                        <p class="vacio">No hay docentes activos registrados.</p>
                    @endforelse
                </div>
            @else
                <p class="vacio">Selecciona una materia primero.</p>
            @endif
        </section>

        <section class="modulo-card selector-panel panel-bloqueado" id="panel-salon">
            <div class="modulo-card-header">
                <div>
                    <h2><i class="fas fa-door-open"></i> 3. Salón</h2>
                    <p>Se habilita al seleccionar un docente. La capacidad se valida contra los alumnos activos del grupo.</p>
                </div>
            </div>

            @if ($hayGrupo)
                <input type="search" id="buscar-salon" placeholder="Buscar salón por clave o nombre" autocomplete="off" disabled>
                <div class="selector-lista" id="lista-salones">
                    @forelse ($salones as $salon)
                        <button type="button" class="selector-item salon-item" disabled
                                data-id="{{ $salon['id'] }}"
                                data-nombre="{{ $salon['nombre'] }}">
                            <strong>{{ $salon['nombre'] }}</strong>
                            <small>{{ $salon['referencia'] }}</small>
                        </button>
                    @empty
                        <p class="vacio">No hay salones activos. Agrégalos en el módulo Salones.</p>
                    @endforelse
                </div>
            @else
                <p class="vacio">Selecciona un docente primero.</p>
            @endif
        </section>
    </div>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-calendar-day"></i> 4. Horario semanal</h2>
                <p>Selecciona materia, docente y salón, luego haz clic en la celda donde se impartirá la clase.</p>
            </div>
        </div>

        <div class="horario-resumen" id="horario-resumen">
            <span>Sin selección</span>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-horario" id="tabla-horario">
                <thead>
                    <tr>
                        <th>Hora</th>
                        @foreach ($dias as $dia)
                            <th>{{ $dia === 'Miercoles' ? 'Miércoles' : $dia }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bloques as $bloque)
                        @php
                            $rango = substr($bloque['inicio'], 0, 5).' a '.substr($bloque['fin'], 0, 5);
                        @endphp
                        @if ($bloque['tipo'] === 'receso')
                            <tr class="fila-receso">
                                <td>{{ $rango }}</td>
                                <td colspan="{{ count($dias) }}"><i class="fas fa-mug-hot"></i> {{ $bloque['etiqueta'] }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="hora-bloque">{{ $rango }}</td>
                                @foreach ($dias as $dia)
                                    <td class="celda-horario"
                                        data-dia="{{ $dia }}"
                                        data-inicio="{{ $bloque['inicio'] }}"
                                        data-fin="{{ $bloque['fin'] }}">
                                        <span class="celda-vacia">—</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="leyenda-horario">
            <i class="fas fa-lock"></i>
            Las celdas marcadas ya están ocupadas en otro horario: el docente o el salón seleccionado no puede
            usarse en ese día y hora. Cambia de docente, de salón o de bloque para evitar empalmes.
        </p>

        @if ($hayGrupo)
            <form method="POST" action="{{ route('admin.horarios.guardar') }}" id="formulario-guardar" style="margin-top: 22px;">
                @csrf
                <input type="hidden" name="id_plan" value="{{ $grupo->id_plan }}">
                <input type="hidden" name="id_grupo" value="{{ $grupo->id_grupo }}">
                <div id="celdas-container"></div>
                <div class="form-acciones">
                    <button type="submit" class="btn-modulo btn-primario">
                        <i class="fas fa-floppy-disk"></i> Guardar horario
                    </button>
                    <a href="{{ route('admin.grupos.index') }}" class="btn-modulo btn-secundario">Volver a grupos</a>
                </div>
            </form>
        @endif
    </section>

    <script>
        (function () {
            const hayGrupo = @json($hayGrupo);
            const asignaciones = @json($asignaciones);
            const ocupacionesDocentes = @json($ocupaciones_docentes);
            const ocupacionesSalones = @json($ocupaciones_salones);
            const container = document.getElementById('celdas-container');
            const resumen = document.getElementById('horario-resumen');
            const panelDocente = document.getElementById('panel-docente');
            const panelSalon = document.getElementById('panel-salon');
            const buscadorDocente = document.getElementById('buscar-docente');
            const buscadorSalon = document.getElementById('buscar-salon');
            const seleccion = { materia: null, docente: null, salon: null };

            function escapar(texto) {
                const contenedor = document.createElement('div');
                contenedor.textContent = texto == null ? '' : String(texto);
                return contenedor.innerHTML;
            }

            function claveCelda(dia, inicio, fin) {
                return dia + '|' + inicio + '|' + fin;
            }

            function horasUsadas(idMateria) {
                return Object.values(asignaciones).filter(function (asignacion) {
                    return Number(asignacion.id_materia) === Number(idMateria);
                }).length;
            }

            function celdasOcupadas(mapa, id) {
                if (!id) return [];
                return mapa[String(id)] || [];
            }

            function motivoBloqueo(celda) {
                const clave = claveCelda(celda.dataset.dia, celda.dataset.inicio, celda.dataset.fin);

                if (seleccion.docente && celdasOcupadas(ocupacionesDocentes, seleccion.docente.dataset.id).includes(clave)) {
                    return 'El docente ya tiene clase en ese día y hora en otro horario.';
                }

                if (seleccion.salon && celdasOcupadas(ocupacionesSalones, seleccion.salon.dataset.id).includes(clave)) {
                    return 'El salón ya está ocupado en ese día y hora en otro horario.';
                }

                return null;
            }

            function actualizarDisponibilidadSelector() {
                const total = document.querySelectorAll('.celda-horario').length;

                document.querySelectorAll('.docente-item').forEach(function (item) {
                    const agotado = celdasOcupadas(ocupacionesDocentes, item.dataset.id).length >= total;
                    item.disabled = agotado;
                    item.classList.toggle('selector-agotado', agotado);
                });

                document.querySelectorAll('.salon-item').forEach(function (item) {
                    const agotado = celdasOcupadas(ocupacionesSalones, item.dataset.id).length >= total;
                    item.disabled = agotado;
                    item.classList.toggle('selector-agotado', agotado);
                });
            }

            function actualizarContadores() {
                document.querySelectorAll('.materia-item').forEach(function (item) {
                    const id = item.dataset.id;
                    const limite = Number(item.dataset.horas);
                    const usadas = horasUsadas(id);
                    const contador = item.querySelector('.materia-contador');

                    if (contador) {
                        contador.textContent = usadas + ' / ' + limite + ' h';
                    }
                    item.classList.toggle('materia-agotada', usadas >= limite);
                });
            }

            function actualizarResumen() {
                const materia = seleccion.materia ? seleccion.materia.dataset.nombre : 'Sin materia';
                const docente = seleccion.docente ? seleccion.docente.dataset.nombre : 'Sin docente';
                const salon = seleccion.salon ? seleccion.salon.dataset.nombre : 'Sin salón';
                const horas = seleccion.materia
                    ? horasUsadas(seleccion.materia.dataset.id) + ' / ' + seleccion.materia.dataset.horas + ' h'
                    : '—';

                const formacion = seleccion.docente ? seleccion.docente.dataset.formacion : '';
                const carga = seleccion.docente
                    ? seleccion.docente.dataset.carga + ' / ' + seleccion.docente.dataset.limite + ' h'
                    : '—';

                resumen.innerHTML = '<span><strong>Materia:</strong> ' + escapar(materia) + '</span>' +
                    '<span><strong>Docente:</strong> ' + escapar(docente) + '</span>' +
                    '<span><strong>Salón:</strong> ' + escapar(salon) + '</span>' +
                    '<span><strong>Horas materia:</strong> ' + escapar(horas) + '</span>' +
                    '<span><strong>Carga docente:</strong> ' + escapar(carga) + '</span>' +
                    (formacion
                        ? '<span class="resumen-formacion"><strong>Formación:</strong> ' + escapar(formacion) + '</span>'
                        : '');
            }

            function renderCeldas() {
                document.querySelectorAll('.celda-horario').forEach(function (celda) {
                    const clave = claveCelda(celda.dataset.dia, celda.dataset.inicio, celda.dataset.fin);
                    const asignacion = asignaciones[clave];

                    celda.classList.remove('celda-bloqueada');
                    delete celda.dataset.bloqueada;
                    celda.title = '';

                    if (!asignacion) {
                        const motivo = motivoBloqueo(celda);
                        celda.classList.remove('celda-asignada');

                        if (motivo) {
                            celda.classList.add('celda-bloqueada');
                            celda.dataset.bloqueada = '1';
                            celda.title = motivo;
                            celda.innerHTML = '<span class="celda-bloqueo"><i class="fas fa-lock"></i> Ocupado</span>';
                        } else {
                            celda.innerHTML = '<span class="celda-vacia">—</span>';
                        }
                        return;
                    }

                    celda.classList.add('celda-asignada');
                    celda.innerHTML = '<strong>' + escapar(asignacion.materia) + '</strong>' +
                        '<small>' + escapar(asignacion.docente) + ' · ' + escapar(asignacion.salon) + '</small>' +
                        '<button type="button" class="celda-quitar" title="Quitar asignación">×</button>';
                });
            }

            function renderOcultos() {
                if (!container) return;
                container.innerHTML = '';
                let indice = 0;

                Object.entries(asignaciones).forEach(function ([clave, asignacion]) {
                    const partes = clave.split('|');
                    const valores = {
                        dia: partes[0],
                        inicio: partes[1],
                        fin: partes[2],
                        id_materia: asignacion.id_materia,
                        id_docente: asignacion.id_docente,
                        id_salon: asignacion.id_salon
                    };

                    Object.entries(valores).forEach(function ([campo, valor]) {
                        const entrada = document.createElement('input');
                        entrada.type = 'hidden';
                        entrada.name = 'celdas[' + indice + '][' + campo + ']';
                        entrada.value = valor;
                        container.appendChild(entrada);
                    });

                    indice++;
                });
            }

            function configurarBuscador(id, selector) {
                const buscador = document.getElementById(id);
                if (!buscador) return;

                buscador.addEventListener('input', function () {
                    const texto = buscador.value.toLowerCase().trim();
                    document.querySelectorAll(selector).forEach(function (item) {
                        item.hidden = !item.textContent.toLowerCase().includes(texto);
                    });
                });
            }

            function habilitarDocentes() {
                if (buscadorDocente) buscadorDocente.disabled = false;
                panelDocente.classList.remove('panel-bloqueado');
                document.querySelectorAll('.docente-item').forEach(function (item) {
                    item.disabled = false;
                });
                actualizarDisponibilidadSelector();
            }

            function habilitarSalones() {
                if (buscadorSalon) buscadorSalon.disabled = false;
                panelSalon.classList.remove('panel-bloqueado');
                document.querySelectorAll('.salon-item').forEach(function (item) {
                    item.disabled = false;
                });
                actualizarDisponibilidadSelector();
            }

            function bloquearSalones() {
                if (buscadorSalon) {
                    buscadorSalon.disabled = true;
                    buscadorSalon.value = '';
                }
                panelSalon.classList.add('panel-bloqueado');
                document.querySelectorAll('.salon-item').forEach(function (item) {
                    item.disabled = true;
                    item.classList.remove('seleccion-activo');
                });
                seleccion.salon = null;
            }

            if (hayGrupo) {
                configurarBuscador('buscar-materia', '.materia-item');
                configurarBuscador('buscar-docente', '.docente-item');
                configurarBuscador('buscar-salon', '.salon-item');

                document.querySelectorAll('.materia-item').forEach(function (item) {
                    item.addEventListener('click', function () {
                        const limite = Number(item.dataset.horas);
                        if (horasUsadas(item.dataset.id) >= limite) {
                            resumen.innerHTML = '<span class="resumen-alerta">Esta materia ya alcanzó su límite de ' + limite + ' horas. Quita una celda para poder asignarla de nuevo.</span>';
                            return;
                        }

                        document.querySelectorAll('.materia-item').forEach(function (otro) {
                            otro.classList.remove('seleccion-activo');
                        });
                        item.classList.add('seleccion-activo');
                        seleccion.materia = item;
                        seleccion.docente = null;

                        document.querySelectorAll('.docente-item').forEach(function (docente) {
                            docente.disabled = false;
                            docente.classList.remove('seleccion-activo');
                        });
                        habilitarDocentes();
                        bloquearSalones();
                        renderCeldas();
                        actualizarResumen();
                    });
                });

                document.querySelectorAll('.docente-item').forEach(function (item) {
                    item.addEventListener('click', function () {
                        if (item.disabled || !seleccion.materia) return;
                        document.querySelectorAll('.docente-item').forEach(function (otro) {
                            otro.classList.remove('seleccion-activo');
                        });
                        item.classList.add('seleccion-activo');
                        seleccion.docente = item;
                        habilitarSalones();
                        renderCeldas();
                        actualizarResumen();
                    });
                });

                document.querySelectorAll('.salon-item').forEach(function (item) {
                    item.addEventListener('click', function () {
                        if (item.disabled || !seleccion.docente) return;
                        document.querySelectorAll('.salon-item').forEach(function (otro) {
                            otro.classList.remove('seleccion-activo');
                        });
                        item.classList.add('seleccion-activo');
                        seleccion.salon = item;
                        renderCeldas();
                        actualizarResumen();
                    });
                });

                document.querySelectorAll('.celda-horario').forEach(function (celda) {
                    celda.addEventListener('click', function (evento) {
                        const clave = claveCelda(celda.dataset.dia, celda.dataset.inicio, celda.dataset.fin);

                        if (evento.target.classList.contains('celda-quitar')) {
                            delete asignaciones[clave];
                            renderCeldas();
                            renderOcultos();
                            actualizarContadores();
                            actualizarResumen();
                            return;
                        }

                        if (celda.classList.contains('celda-bloqueada')) {
                            resumen.innerHTML = '<span class="resumen-alerta">' + escapar(celda.title) + '</span>';
                            return;
                        }

                        if (!seleccion.materia || !seleccion.docente || !seleccion.salon) {
                            resumen.innerHTML = '<span class="resumen-alerta">Selecciona primero una materia, un docente y un salón.</span>';
                            return;
                        }

                        const idMateria = Number(seleccion.materia.dataset.id);
                        const limite = Number(seleccion.materia.dataset.horas);
                        const existente = asignaciones[clave];
                        const mismaMateria = existente && Number(existente.id_materia) === idMateria;

                        if (!mismaMateria && horasUsadas(idMateria) >= limite) {
                            resumen.innerHTML = '<span class="resumen-alerta">Esta materia ya alcanzó su límite de ' + limite + ' horas.</span>';
                            return;
                        }

                        asignaciones[clave] = {
                            id_materia: idMateria,
                            id_docente: Number(seleccion.docente.dataset.id),
                            id_salon: Number(seleccion.salon.dataset.id),
                            materia: seleccion.materia.dataset.nombre,
                            docente: seleccion.docente.dataset.nombre,
                            salon: seleccion.salon.dataset.nombre
                        };

                        renderCeldas();
                        renderOcultos();
                        actualizarContadores();
                        actualizarResumen();
                    });
                });
            }

            actualizarResumen();
            actualizarContadores();
            renderCeldas();
            renderOcultos();
        })();
    </script>
@endsection
