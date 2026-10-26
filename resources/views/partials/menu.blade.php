<header class="site-header">
    <div class="header-container">
        <div class="header-logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('imagenes/logoAnotnio.png') }}" alt="Logo Escuela">
            </a>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="{{ url('/') }}">Inicio</a></li>
                <li class="dropdown">
                    <a href="#">Nosotros</a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('historia') }}">Historia</a></li>               
                        <li><a href="{{ route('mision.vision') }}">Misión y visión</a></li>
                        <li><a href="#">Galeria</a></li>
                        <li><a href="#">Personal</a></li>
                    </ul>
                </li>
                <li><a href="#">Avisos</a></li>
                <li><a href="#">Portal</a></li>
                <li class="dropdown">
                    <a href="#">Documentos</a>
                    <ul class="dropdown-menu">
                        <li><a href="#">Financiero</a></li>
                        <li><a href="#">Formatos</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</header>
