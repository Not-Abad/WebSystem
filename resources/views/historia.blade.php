<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historia - Bachilleres Antonio MaRD</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index_principal.css') }}">
</head>
<body style="background-color: #ffffff; color: #24292e; font-family: 'Raleway', sans-serif; margin: 0; padding: 0;">

    @include('partials.menu')

    <header style="padding: 50px 20px; background-color: #161b22; border-bottom: 2px solid #0066ff; text-align: center;">
        <div style="max-width: 1000px; margin: 0 auto;">
            <span style="background: rgba(0, 102, 255, 0.2); color: #0066ff; padding: 6px 14px; border-radius: 20px; font-weight: 600; font-size: 0.85rem;">Legado y Tradición</span>
            <h1 style="color: #ffffff; font-size: 2.5rem; margin: 15px 0 10px 0; font-weight: 700;">Historia y Fundación</h1>
            <p style="color: #8b949e; font-size: 1.1rem; max-width: 700px; margin: 0 auto;">El Origen de una Tradición Xalapeña</p>
        </div>
    </header>

    <main style="max-width: 900px; margin: 50px auto; padding: 0 20px; display: flex; flex-direction: column; gap: 30px;">
        
        <!-- Fundación -->
        <section style="background: #161b22; padding: 35px; border-radius: 10px; border: 1px solid #30363d; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 2px solid #30363d; padding-bottom: 12px;">
                <i class="fas fa-landmark" style="color: #0066ff; font-size: 1.5rem;"></i>
                <h2 style="color: #0066ff; font-size: 1.5rem; margin: 0;">Historia y Fundación: El Origen de una Tradición Xalapeña</h2>
            </div>
            <p style="color: #c9d1d9; line-height: 1.7; font-size: 1rem; margin-bottom: 15px;">
                La Escuela de Bachilleres Lic. Antonio María de Rivera, conocida con mucho cariño por los xalapeños simplemente como "La Antonio", es uno de los pilares que sostienen el prestigio de Xalapa como la "Atenas Veracruzana". Su historia arranca oficialmente en 1956, una época de gran expansión educativa y cultural en la ciudad.
            </p>
            <ul style="color: #8b949e; line-height: 1.7; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; margin: 0;">
                <li><strong style="color: #e6edf3;">Ubicación estratégica:</strong> Se estableció en el corazón de la Zona Universitaria, sobre el Circuito Gonzalo Aguirre Beltrán. Esta cercanía geográfica con las facultades de la Universidad Veracruzana (UV) no es casualidad; desde sus inicios, la escuela ha respirado un ambiente académico y universitario que inspira a sus estudiantes.</li>
                <li><strong style="color: #e6edf3;">Orígenes como Secundaria:</strong> Es un dato poco conocido que "La Antonio" no nació siendo una preparatoria. Durante sus primeras décadas de existencia, funcionó exclusivamente como una escuela secundaria, formando a generaciones de adolescentes xalapeños en su educación básica.</li>
                <li><strong style="color: #e6edf3;">Identidad y orgullo:</strong> Desde aquellos años se forjó su identidad visual y deportiva. La adopción del venado como mascota oficial se convirtió en un símbolo de agilidad, nobleza y orgullo, figura que sigue dominando su escudo, sus uniformes y el espíritu de sus equipos deportivos (los "Venados de la Antonio").</li>
            </ul>
        </section>

        <!-- Transición a Bachillerato -->
        <section style="background: #161b22; padding: 35px; border-radius: 10px; border: 1px solid #30363d; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 2px solid #30363d; padding-bottom: 12px;">
                <i class="fas fa-graduation-cap" style="color: #0066ff; font-size: 1.5rem;"></i>
                <h2 style="color: #0066ff; font-size: 1.5rem; margin: 0;">Transición a Bachillerato: El Gran Salto</h2>
            </div>
            <p style="color: #c9d1d9; line-height: 1.7; font-size: 1rem; margin-bottom: 15px;">
                El parteaguas en la historia de la institución llegó en la década de los años 80. Para ese entonces, la ciudad de Xalapa experimentaba un crecimiento demográfico importante y una demanda cada vez mayor de educación media superior que sirviera como puente hacia la universidad.
            </p>
            <ul style="color: #8b949e; line-height: 1.7; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; margin: 0;">
                <li><strong style="color: #e6edf3;">Evolución académica:</strong> "La Antonio" dejó atrás el modelo de educación básica para transformarse oficialmente en una Escuela de Bachilleres. Este cambio implicó una reestructuración total de su plan de estudios, adoptando el rigor científico y humanístico necesario para preparar a los jóvenes para la educación superior.</li>
                <li><strong style="color: #e6edf3;">Consolidación de prestigio:</strong> Esta transición le permitió posicionarse rápidamente al nivel de otras instituciones históricas de la ciudad (como la Prepa Juárez o la Artículo 3ro). Hoy en día, es una de las escuelas públicas con mayor matrícula, operando bajo los subsistemas de la Dirección General de Bachillerato (DGB) y siendo una de las opciones más solicitadas en los exámenes de ingreso a nivel medio superior.</li>
            </ul>
        </section>

        <!-- El Personaje -->
        <section style="background: #161b22; padding: 35px; border-radius: 10px; border: 1px solid #30363d; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 2px solid #30363d; padding-bottom: 12px;">
                <i class="fas fa-user-tie" style="color: #0066ff; font-size: 1.5rem;"></i>
                <h2 style="color: #0066ff; font-size: 1.5rem; margin: 0;">El Personaje: ¿Quién fue el Lic. Antonio María de Rivera?</h2>
            </div>
            <p style="color: #c9d1d9; line-height: 1.7; font-size: 1rem; margin-bottom: 15px;">
                Llevar el nombre de Antonio María de Rivera (1801-1874) no es un adorno; es cargar con el legado del padre de la educación preparatoria en Veracruz. Fue un ilustre abogado, político, filántropo y un auténtico visionario nacido en Xalapa.
            </p>
            <ul style="color: #8b949e; line-height: 1.7; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; margin: 0;">
                <li><strong style="color: #e6edf3;">Fundador de la educación superior:</strong> Su máxima hazaña, y por la cual está grabado en letras de oro en la historia del estado, ocurrió el 16 de septiembre de 1843. Ese día fundó el histórico Colegio Nacional de Xalapa, la institución que hoy todos conocemos como el Colegio Preparatorio de Xalapa ("Prepa Juárez"). Logró esto gestionando personalmente el apoyo del entonces presidente Antonio López de Santa Anna.</li>
                <li><strong style="color: #e6edf3;">Rector y líder:</strong> No solo lo fundó, sino que fue su rector durante 25 años (hasta 1868), sosteniendo muchas veces la escuela con sus propios recursos durante épocas de crisis política.</li>
                <li><strong style="color: #e6edf3;">Impacto urbano y social:</strong> Además de su labor educativa, fue alcalde de Xalapa. Durante su gestión, impulsó el desarrollo urbano, mandó construir importantes caminos, paseos y puentes que mejoraron la infraestructura de la ciudad en el siglo XIX. Nombrar a esta escuela en su honor es un tributo directo a su inalcanzable labor por la juventud y la ciudad.</li>
            </ul>
        </section>

        <!-- Turno Vespertino -->
        <section style="background: #161b22; padding: 35px; border-radius: 10px; border: 1px solid #30363d; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 2px solid #30363d; padding-bottom: 12px;">
                <i class="fas fa-moon" style="color: #0066ff; font-size: 1.5rem;"></i>
                <h2 style="color: #0066ff; font-size: 1.5rem; margin: 0;">Turno Vespertino: Proactividad y Comunidad</h2>
            </div>
            <p style="color: #c9d1d9; line-height: 1.7; font-size: 1rem; margin-bottom: 15px;">
                Dentro de "La Antonio", el Turno Vespertino ha cultivado una identidad propia, caracterizada por la resiliencia, la camaradería y un nivel de dinamismo impresionante por parte de su comunidad estudiantil.
            </p>
            <ul style="color: #8b949e; line-height: 1.7; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; margin: 0;">
                <li><strong style="color: #e6edf3;">Rigor y Paraescolares:</strong> Los alumnos de la tarde cubren exactamente el mismo mapa curricular que el turno matutino, enfrentando materias complejas de ciencias exactas, experimentales y humanidades, pero complementan su formación con una fuerte presencia en actividades paraescolares (deportes, danza, música y teatro), dándole mucha vida a las instalaciones hasta caer la noche.</li>
                <li><strong style="color: #e6edf3;">Conciencia y Activismo Social:</strong> Los estudiantes de este turno destacan frecuentemente por su iniciativa frente a problemas sociales contemporáneos. Un ejemplo reciente y muy destacado de esta proactividad ocurrió en abril de 2026, cuando los alumnos del turno vespertino tomaron las riendas de la salud mental y emocional de su comunidad al organizar, producir y lanzar campañas audiovisuales dirigidas a concientizar y erradicar el acoso escolar (bullying) dentro y fuera de las aulas, demostrando que son una generación altamente comprometida con su entorno.</li>
            </ul>
        </section>

    </main>

    @include('partials.footer')

</body>
</html>