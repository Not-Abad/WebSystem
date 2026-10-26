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
</head>
<body>

    @include('partials.menu')

    <header class="hero-section">
        <canvas id="hero-particles"></canvas>
        <div class="hero-container">
            <div class="hero-text">
                <span class="badge-tag">Escuela de Bachilleres Antonio María Rivera Diurna</span>
                <h1>Es tu momento</h1>
                <h2>¡Bienvenido a la AMRD!</h2>
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