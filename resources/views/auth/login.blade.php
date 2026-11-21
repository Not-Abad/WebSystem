<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal - Bachilleres Antonio MaRD</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="login-body">

    @include('partials.menu')

    <div class="login-shell">

        <!-- Panel de acceso: solo identidad del portal y lluvia de puntos -->
        <aside class="login-visual">
            <canvas id="login-particles"></canvas>

            <div class="login-visual-content">
                <span class="login-badge">
                    <i class="fas fa-shield-halved"></i> Acceso institucional
                </span>
                <h1>Bienvenido <span>de nuevo</span></h1>
            </div>
        </aside>

        <!-- Formulario -->
        <section class="login-form-side">
            <div class="login-card">

                <div class="login-card-header">
                    <h2>Iniciar sesión</h2>
                    <p>Ingresa tus credenciales para continuar.</p>
                </div>

                @if ($errors->any())
                    <div class="alerta alerta-error" role="alert">
                        <i class="fas fa-circle-exclamation"></i>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alerta alerta-ok" role="status">
                        <i class="fas fa-circle-check"></i> {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" autocomplete="on">
                    @csrf

                    <div class="form-group">
                        <label for="username">Usuario</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text"
                                   id="username"
                                   name="username"
                                   value="{{ old('username') }}"
                                   placeholder="Tu usuario institucional"
                                   autocomplete="username"
                                   required
                                   autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   placeholder="••••••••"
                                   autocomplete="current-password"
                                   required>
                            <button type="button" class="toggle-password" id="toggle-password" aria-label="Mostrar contraseña">
                                <i class="fas fa-eye" id="toggle-password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        Entrar al portal <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </section>
    </div>

    <script>
        // Lluvia de puntos (único elemento visual del panel de acceso)
        (function () {
            const canvas = document.getElementById('login-particles');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const ajustar = () => {
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
            };
            ajustar();
            window.addEventListener('resize', ajustar);

            const particulas = Array.from({ length: 45 }, () => ({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                velocidad: 0.4 + Math.random() * 1.1,
                opacidad: 0.15 + Math.random() * 0.55
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

        // Mostrar / ocultar contraseña
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggle-password-icon');

        document.getElementById('toggle-password').addEventListener('click', () => {
            const mostrar = passwordInput.type === 'password';
            passwordInput.type = mostrar ? 'text' : 'password';
            toggleIcon.classList.toggle('fa-eye-slash', mostrar);
            toggleIcon.classList.toggle('fa-eye', !mostrar);
        });
    </script>

</body>
</html>
