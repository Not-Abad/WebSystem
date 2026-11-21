<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $titulo ?? 'Portal') - Bachilleres Antonio MaRD</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modulo.css') }}">
</head>
<body class="modulo-body">

    @include('partials.menu')

    <header class="modulo-hero">
        <canvas id="modulo-particles"></canvas>

        <div class="modulo-hero-content">
            <span class="modulo-badge">
                <i class="fas {{ $icono ?? 'fa-shield-halved' }}"></i>
                {{ $etiqueta ?? 'Portal institucional' }}
            </span>
            <h1>{{ $titulo ?? 'Módulo' }}</h1>

            @if (isset($subtitulo))
                <p class="modulo-subtitulo">{{ $subtitulo }}</p>
            @endif

            @yield('hero-actions')
        </div>
    </header>

    <main class="modulo-main">
        @if (session('status'))
            <div class="modulo-alerta modulo-alerta-ok" role="status">
                <i class="fas fa-circle-check"></i> {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="modulo-alerta modulo-alerta-error" role="alert">
                <i class="fas fa-circle-exclamation"></i>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        (function () {
            const canvas = document.getElementById('modulo-particles');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const ajustar = () => {
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
            };
            ajustar();
            window.addEventListener('resize', ajustar);

            const particulas = Array.from({ length: 35 }, () => ({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                velocidad: 0.3 + Math.random() * 0.8,
                opacidad: 0.12 + Math.random() * 0.4
            }));

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
        })();
    </script>

</body>
</html>
