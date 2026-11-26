<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Bachilleres Antonio MaRD</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index_principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal-publico.css') }}">
</head>
<body>

    @include('partials.menu')

    <header class="hero-section">
        <canvas id="hero-particles"></canvas>
        <div class="hero-container">
            <div class="hero-text">
                <span class="badge-tag">Escuela de Bachilleres Antonio María Rivera Vespertina</span>
                <h1>Es tu momento</h1>
                <h2>¡Bienvenido a la AMRV!</h2>
                <p>Aquí, cada mañana es una oportunidad para aprender, participar y construir un futuro brillante.
                    Somos una comunidad educativa que fomenta la creatividad, el pensamiento crítico y la colaboración.</p>
            </div>
        </div>
    </header>

    <section class="features-section">
        <h2 class="section-title">HOLA, VENADO</h2>
        <div class="cards-grid">
            <div class="feature-card">
                <h3>¡Mensaje de Bienvenida!</h3>
                <p>Nos alegra recibir a quienes hoy llegan por primera vez
                    y a quienes regresan a continuar su formación académica en nuestra institución.
                    Aquí hay lugar para distintas historias, talentos, ideas y maneras de ver el mundo.
                </p>
            </div>

            <div class="feature-card">
                <h3>¡La música transforma!</h3>
                <p>La Universidad Gestalt de Diseño presenta "Sinfonía Gestalt", una colección de
                    relatos-cartel inspirados en figuras emblemáticas de la música, desarrollada por
                    estudiantes de Diseño Gráfico y Comunicación Visual.</p>
            </div>

            <div class="feature-card">
                <h3>Banner</h3>
                <p>Aquí solo publica con material oficial de la SEV.</p>
            </div>
        </div>
    </section>

    <section class="portal-seccion">
        <div class="portal-contenedor">
            <div class="portal-encabezado">
                <span class="portal-eyebrow">Nuestra escuela</span>
                <h2 class="portal-titulo">La Antonio en cifras</h2>
                <p class="portal-bajada">Una comunidad educativa del turno vespertino en Xalapa, comprometida con el acompañamiento cercano y la formación de cada estudiante.</p>
            </div>
            <div class="portal-cifras">
                @foreach ($cifras as $cifra)
                    <div class="portal-cifra">
                        <i class="fas {{ $cifra['icono'] }} portal-icono"></i>
                        <strong>{{ $cifra['numero'] }}</strong>
                        <span>{{ $cifra['etiqueta'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="portal-seccion portal-seccion--alterno">
        <div class="portal-contenedor">
            <div class="portal-encabezado">
                <span class="portal-eyebrow">Lo más reciente</span>
                <h2 class="portal-titulo">Avisos para la comunidad</h2>
                <p class="portal-bajada">Convocatorias, actividades escolares y fechas importantes publicadas por la administración del plantel.</p>
            </div>
            <div class="portal-grid">
                @forelse ($avisos as $aviso)
                    <article class="portal-tarjeta">
                        <span class="portal-chip">{{ $aviso->fecha_publicacion?->format('d/m/Y') }}</span>
                        <h3>{{ $aviso->titulo }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $aviso->contenido), 160) }}</p>
                        <a class="portal-enlace" href="{{ route('avisos.show', $aviso) }}">Leer aviso completo <i class="fas fa-arrow-right"></i></a>
                    </article>
                @empty
                    <div class="portal-vacio">No hay avisos publicados en este momento.</div>
                @endforelse
            </div>
            <a class="portal-boton" href="{{ route('avisos') }}" style="margin-top:28px;">Ver todos los avisos</a>
        </div>
    </section>

    <section class="portal-seccion">
        <div class="portal-contenedor">
            <div class="portal-encabezado">
                <span class="portal-eyebrow">Trámites y recursos</span>
                <h2 class="portal-titulo">Documentos que probablemente necesitas</h2>
                <p class="portal-bajada">Formatos, convocatorias y normatividad disponibles para estudiantes, familias y personal escolar.</p>
            </div>
            <div class="portal-listado">
                @forelse ($documentos as $documento)
                    <div class="portal-fila">
                        <div class="portal-fila-texto">
                            <span class="portal-chip">{{ $documento['categoria'] }}</span>
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
                    <div class="portal-vacio">Aún no hay documentos publicados.</div>
                @endforelse
            </div>
            <a class="portal-boton" href="{{ route('documentos') }}" style="margin-top:28px;">Ir a documentos</a>
        </div>
    </section>

    <section class="portal-seccion portal-seccion--alterno">
        <div class="portal-contenedor">
            <div class="portal-encabezado">
                <span class="portal-eyebrow">Formación integral</span>
                <h2 class="portal-titulo">Así trabajamos con nuestros estudiantes</h2>
                <p class="portal-bajada">Un plan de estudios gradual, tutorías personalizadas y evaluación continua con evidencias.</p>
            </div>
            <div class="portal-grid">
                <article class="portal-tarjeta">
                    <i class="fas fa-layer-group portal-icono"></i>
                    <h3>Tronco común (semestres I–IV)</h3>
                    <p>Las primeras cuatro etapas consolidan los conocimientos fundamentales y las habilidades de lectura, escritura y pensamiento matemático.</p>
                </article>
                <article class="portal-tarjeta">
                    <i class="fas fa-compass portal-icono"></i>
                    <h3>Área propedéutica (semestres V–VI)</h3>
                    <p>En los últimos semestres, cada estudiante elige un área que lo acerca a su futuro universitario y a sus intereses profesionales.</p>
                </article>
                <article class="portal-tarjeta">
                    <i class="fas fa-user-tie portal-icono"></i>
                    <h3>Tutoría cercana</h3>
                    <p>Cada estudiante cuenta con un tutor que acompaña su trayectoria escolar, revisa su avance y orienta sus decisiones académicas.</p>
                </article>
                <article class="portal-tarjeta">
                    <i class="fas fa-file-signature portal-icono"></i>
                    <h3>Evaluación con evidencias</h3>
                    <p>Asistencia, trabajos y exámenes se registran en el formato REINCO para dar seguimiento claro y transparente del aprendizaje.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="portal-seccion">
        <div class="portal-contenedor">
            <div class="portal-encabezado">
                <span class="portal-eyebrow">Conócenos mejor</span>
                <h2 class="portal-titulo">Explora nuestra escuela</h2>
                <p class="portal-bajada">Descubre de dónde venimos, hacia dónde vamos y cómo nos organizamos para acompañarte.</p>
            </div>
            <div class="portal-grid">
                <article class="portal-tarjeta">
                    <i class="fas fa-landmark portal-icono"></i>
                    <h3>Nuestra historia</h3>
                    <p>Más de seis décadas formando a generaciones de xalapeños en el corazón de la Zona Universitaria.</p>
                    <a class="portal-enlace" href="{{ route('historia') }}">Leer la historia <i class="fas fa-arrow-right"></i></a>
                </article>
                <article class="portal-tarjeta">
                    <i class="fas fa-bullseye portal-icono"></i>
                    <h3>Misión y visión</h3>
                    <p>El compromiso institucional con la educación media superior, la cultura y el servicio a la comunidad.</p>
                    <a class="portal-enlace" href="{{ route('mision.vision') }}">Conocer la misión <i class="fas fa-arrow-right"></i></a>
                </article>
                <article class="portal-tarjeta">
                    <i class="fas fa-newspaper portal-icono"></i>
                    <h3>Comunicación oficial</h3>
                    <p>Consulta los avisos vigentes y los documentos descargables publicados por el plantel.</p>
                    <a class="portal-enlace" href="{{ route('avisos') }}">Ver avisos <i class="fas fa-arrow-right"></i></a>
                    <a class="portal-enlace" href="{{ route('documentos') }}">Ver documentos <i class="fas fa-arrow-right"></i></a>
                </article>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <script>
        const canvas = document.getElementById('hero-particles');
        if (canvas) {
            const ctx = canvas.getContext('2d');

            function ajustarCanvas() {
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
            }
            ajustarCanvas();
            window.addEventListener('resize', ajustarCanvas);

            const particulas = [];
            const cantidad = 50;

            for (let i = 0; i < cantidad; i++) {
                particulas.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    velocidad: 0.5 + Math.random() * 1.2,
                    opacidad: 0.2 + Math.random() * 0.6
                });
            }

            function animar() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                particulas.forEach(p => {
                    p.y += p.velocidad;
                    if (p.y > canvas.height) {
                        p.y = 0;
                        p.x = Math.random() * canvas.width;
                    }
                    ctx.fillStyle = `rgba(0, 102, 255, ${p.opacidad})`;
                    ctx.fillRect(p.x, p.y, 2, 2);
                });

                requestAnimationFrame(animar);
            }
            animar();
        }
    </script>

</body>
</html>