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
                @auth
                    <li><a href="{{ route('panel') }}">Mi panel</a></li>
                    <li class="dropdown">
                        <a href="#"><i class="fas fa-circle-user"></i> {{ Auth::user()->username }} <span>▾</span></a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ route('panel') }}"><i class="fas fa-gauge-high"></i> Mi panel</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" id="logout-form-menu">
                                    @csrf
                                </form>
                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-menu').submit();">
                                    <i class="fas fa-right-from-bracket"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                @else
                    <li><a href="{{ route('login') }}"><i class="fas fa-right-to-bracket"></i> Portal</a></li>
                @endauth
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
