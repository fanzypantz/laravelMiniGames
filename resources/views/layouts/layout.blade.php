<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Minigames @yield('title')</title>

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
        @yield('script')

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        @yield('css')
    </head>
    <body>
        <img id="menu-button" class="menu-button" src="{{ asset('/images/icons/menu.svg') }}" alt="" onclick="layoutModule.toggleMenu()">

        <nav id="nav">
            <a class="nav-item btn" href="{{ route('index') }}">Home</a>
            @if(Route::current()->getName() == 'lobby')
                <a class="nav-item btn" onclick="layoutModule.copyURL()" >Copy Lobby URL</a>
                <a class="nav-item btn" onclick="layoutModule.stopGame()">Close Game</a>
            @endif
            @guest
                <a class="nav-item btn" href="{{ route('register') }}">

                    <strong>Sign up</strong>
                </a>
                <a class="nav-item btn" href="{{ route('login') }}">Log in</a>
            @else
                <a class="nav-item btn" href="" >{{ Auth::user()->name }}</a>
                <a class="nav-item btn" href="{{ route('logout') }}">{{ __('Logout') }}</a>
            @endguest
        </nav>

        <div id="app">
            @yield('content')
        </div>

        <div id="url-holder"></div>

        <div id="bg" class="area" >
            <ul class="circles">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
        </div >

    </body>
</html>

