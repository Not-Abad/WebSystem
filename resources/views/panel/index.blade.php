<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel {{ $rol }} - Bachilleres Antonio MaRD</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
</head>
<body class="panel-body" style="--role-color: {{ $colorRol }};">

    @include('partials.menu')

    @php
        $iniciales = collect(preg_split('/\s+/', trim($nombre)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($palabra) => mb_strtoupper(mb_substr($palabra, 0, 1)))
            ->implode('');
    @endphp

    <header class="panel-hero">
        <canvas id="panel-particles"></canvas>

        <div class="panel-hero-content">
            <span class="panel-badge">
                <i class="fas {{ $iconoRol }}"></i>
                Sesión activa · {{ $rol }}
            </span>

            <div class="panel-avatar">{{ $iniciales }}</div>

            <h1>Hola, <span class="panel-usuario">{{ $nombre }}</span></h1>
            <p class="panel-sub">
                Este es tu espacio de portal institucional de la AMRV (Antonio María de Rivera Vespertina).
            </p>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-cerrar">
                    <i class="fas fa-right-from-bracket"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </header>

    @if (session('status'))
        <div style="max-width: 1100px; margin: 24px auto 0; padding: 0 20px;">
            <div class="alerta alerta-ok" role="status" style="background:#eef5fc;border:1px solid #b6d4ff;border-left:4px solid #0066ff;color:#0047b3;border-radius:6px;padding:12px 14px;font-size:.87rem;">
                <i class="fas fa-circle-check"></i> {{ session('status') }}
            </div>
        </div>
    @endif

    <main class="panel-main">
        <h2 class="panel-section-title">Tus módulos disponibles</h2>
        <p class="panel-section-desc">
            Los accesos se muestran según el rol con el que iniciaste sesión.
        </p>

        <div class="panel-grid">
            @forelse ($modulos as $modulo)
                <article class="panel-card">
                    <i class="fas {{ $modulo['icono'] }}"></i>
                    <h3>{{ $modulo['titulo'] }}</h3>
                    <p>{{ $modulo['descripcion'] }}</p>
                    @if (! empty($modulo['ruta']))
                        <a class="panel-card-link" href="{{ route($modulo['ruta']) }}">
                            Acceder al módulo <i class="fas fa-arrow-right"></i>
                        </a>
                    @else
                        <span class="panel-card-link">Próximamente <i class="fas fa-arrow-right"></i></span>
                    @endif
                </article>
            @empty
                <article class="panel-card">
                    <i class="fas fa-circle-info"></i>
                    <h3>Sin módulos asignados</h3>
                    <p>Tu rol aún no tiene módulos configurados. Solicita soporte al administrador.</p>
                </article>
            @endforelse
        </div>

        <p class="panel-nota">
            Usuario: <strong>{{ $usuario->username }}</strong> ·
            Último acceso: <strong>{{ $usuario->ultimo_acceso?->format('d/m/Y H:i') ?? 'Primera sesión' }}</strong>
        </p>
    </main>

    <script>
        (function () {
            const canvas = document.getElementById('panel-particles');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const ajustar = () => {
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
            };
            ajustar();
            window.addEventListener('resize', ajustar);

            const particulas = Array.from({ length: 40 }, () => ({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                velocidad: 0.35 + Math.random() * 0.9,
                opacidad: 0.12 + Math.random() * 0.45
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
