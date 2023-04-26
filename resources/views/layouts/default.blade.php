<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Portal Àgora</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>

<body class="antialiased">
    <header>
        <nav class="navbar navbar-fixed-top container-fluid" id="navbar_top">
            <div class="container-fluid">
                <a href="https://educacio.gencat.cat/ca/inici/" target="_blank" class="brand departament hidden-phone">
                    <img src="{{ asset('images/departament.png') }}" alt="Departament d'Ensenyament" title=""/>
                </a>
                <a href="https://xtec.gencat.cat/ca/inici" target="_blank" class="brand xtec hidden-phone">
                    <img src="{{ asset('images/xtec.png') }}" alt="Xarxa Telemàtica Educativa de Catalunya" title=""/>
                </a>
                <a class="brand mainbrand" href="{{ route('home') }}">{{ __('layout.header') }}</a>
                <div class="pull-right">
                    Menú
                </div>
            </div>
        </nav>
    </header>

    <div id="maincontent" class="container-fluid">
        @yield('content')
    </div>

    <footer>
        <a href="https://xtec.gencat.cat/ca/inici" target="_blank">
            <img src="{{ asset('images/xtec.png') }}" alt="XTEC"/>
        </a>
    </footer>
</body>
</html>
