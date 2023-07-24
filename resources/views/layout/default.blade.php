<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('home.title') }}</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ secure_asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/portal.css') }}">

    <!-- Scripts -->
    <script src="{{ secure_asset('js/jquery.min.js') }}"></script>
    <script src="{{ secure_asset('js/bootstrap.min.js') }}"></script>
</head>

<body class="antialiased">
<header>
    <nav class="navbar navbar-fixed-top" id="navbar_top">
        <div class="container-fluid">
            <a href="https://educacio.gencat.cat/ca/inici/" target="_blank" class="brand departament hidden-phone">
                <img src="{{ secure_asset('images/departament.png') }}" alt="{{ __('home.department') }}" title="{{ __('home.department') }}"/>
            </a>
            <a href="https://xtec.gencat.cat/ca/inici" target="_blank" class="brand xtec hidden-phone">
                <img src="{{ secure_asset('images/xtec.png') }}" alt="{{ __('home.xtec') }}" title="{{ __('home.xtec') }}"/>
            </a>
            <div class="pull-right">
                @include('menu.usermenu')
            </div>
        </div>
    </nav>
    <h1>
        <a class="brand mainbrand visible-md visible-lg" href="{{ route('home') }}">{{ __('layout.header') }}</a>
    </h1>
</header>

<div id="maincontent" class="container-fluid">
    @yield('content')
</div>

<footer>
    <a href="https://xtec.gencat.cat/ca/inici" target="_blank">
        <img src="{{ secure_asset('images/departament.png') }}" alt="{{ __('home.department') }}" title="{{ __('home.department') }}"/>
    </a>
</footer>
</body>
</html>
