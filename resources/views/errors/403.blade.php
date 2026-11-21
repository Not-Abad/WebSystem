<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado - Bachilleres Antonio MaRD</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:'Raleway',sans-serif;color:#24292e;">

    @include('partials.menu')

    <main style="max-width:760px;margin:70px auto;padding:0 20px;">
        <section style="background:#161b22;padding:50px 40px;border-radius:10px;border:1px solid #30363d;border-top:4px solid #dc3545;box-shadow:0 12px 32px rgba(0,0,0,.18);text-align:center;">
            <i class="fas fa-lock" style="color:#dc3545;font-size:3rem;margin-bottom:18px;"></i>
            <h1 style="color:#ffffff;font-size:2rem;margin:0 0 10px 0;">Acceso denegado</h1>
            <p style="color:#8b949e;line-height:1.7;margin:0 0 28px 0;">
                Tu cuenta no tiene permisos para ver esta sección. Si crees que se trata de un error,
                comunícate con el administrador del sistema.
            </p>
            <a href="{{ route('panel') }}" style="display:inline-block;background:#0066ff;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:700;">
                <i class="fas fa-arrow-left"></i> Volver a mi panel
            </a>
        </section>
    </main>

</body>
</html>
