<!-- resources/views/index.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Bachilleres Antonio MaRD</title>

    <!-- Fuentes Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Hojas de Estilos de Laravel usando helper asset() -->
    <link rel="stylesheet" href="{{ asset('css/menu_flotante.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index_principal.css') }}">
</head>
<body>

    <!-- Inclusión nativa del menú con Blade -->
    @include('partials.menu')

    <!-- Hero Section (Presentación Principal) -->
    <header class="hero-section">
        <div class="hero-container">
            <div class="hero-text">
                <span class="badge-tag">Escuela de Bachilleres Antonio María Rivera Diurna</span>
                <h1>Es tu momento</h1>
                <h2>¡Bienvenido a la AMRD!</h2>
                <p>Aqu&iacute;, cada mañana es una oportunidad para aprender, participar y construir un futuro brillante.
                    Somos una comunidad educativa que fomenta la creatividad, el pensamiento crítico y la colaboración.</p>
            </div>
        </div>
    </header>

    <!-- Sección de Características -->
    <section class="features-section">
        <h2 class="section-title">HOLA, VENADO</h2>
        <div class="cards-grid">
            <div class="feature-card">
                <h3>¡Mensaje de Bienvenida!</h3>
                <p>Nos alegra recibir a quien hoy con quienes hoy llegan por primera vez
                    y a quienes regresan a continuar su formación académica en nuestra institución.
                    Aq&iu&iacute; hay lugar para distintas historias, talentos, ideas y maneras de ver el mundo.
                </p>
            </div>

            <div class="feature-card">
                <h3>¡La m&uacute;sica transforma!</h3>
                <p>La Universidad Gestalt de Diseño presenta "Sinfon&iacute;a Gestalt", una colección de
                    relatos-cartel inspirados en figuras emblematicas de la m&uacute;sica, desarrollada por
                    estudiantes de Diseño Gr&aacute;fico y Comunicaci&oacute;n Visual.</p>
            </div>

            <div class="feature-card">
                <h3>Banner</h3>
                <p>Aquí solo publicacon material oficial de la SEV.</p>
            </div>
        </div>
    </section>

    <!-- Pie de Página -->
    <footer>
        <p><strong>Secretaria de Educación Publica</strong> Por la 4ta Transformación</p>
        <p>Gobierno de México</p>
        <p>Ingeniería en Sistemas Computacionales | Instituto Tecnológico Superior de Xalapa</p>
    </footer>

</body>
</html>