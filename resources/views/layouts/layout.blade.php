<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Game Of Ladders @yield('title')</title>

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
        <img id="menu-button" class="menu-button" src="{{ asset('/images/icons/menu.svg') }}" alt="" onclick="toggleMenu()">
        <img id="logo" class="logo" src="{{ asset('/images/icons/logo.svg') }}" alt="" >
        <nav id="nav">
            @guest
                <a class="nav-item btn" href="{{ route('register') }}">

                    <strong>Sign up</strong>
                </a>
                <a class="nav-item btn" href="{{ route('login') }}">Log in</a>
            @else
                <a class="nav-item btn" href="" >
                    <img class="button-icon" src="{{asset('images/icons/player.svg')}}" alt="">
                    <span class="text">{{ Auth::user()->name }}
                            </span></a>
                <a class="nav-item btn" href="{{ route('logout') }}">{{ __('Logout') }}</a>
            @endguest
            @if(Route::current()->getName() == 'lobby')
                <button class="nav-item btn" onclick="copyURL()" >
                    <img class="button-icon" src="{{asset('images/icons/link.svg')}}" alt="">
                    <span class="text">Copy Game URL</span>
                </button>
            @endif
        </nav>
        <div id="app">



            @yield('content')

        </div>
        <div id="url-holder"></div>
        <script>
            // Source: https://techoverflow.net/2018/03/30/copying-strings-to-the-clipboard-using-pure-javascript/
            // Copy the URL to the clipboard by doing some weird workaround with the clipboard command
            function copyURL() {
                let element = document.createElement('textarea');
                element.value = window.location.href;
                // Make element readonly
                element.setAttribute('readonly', '');
                // Hide the element
                element.style = {
                    display: "none",
                    position: "absolute",
                    top: "-500px",
                };
                document.body.appendChild(element);
                element.select();
                document.execCommand('copy');
                document.body.removeChild(element);
            }

            function toggleMenu () {
                let button = document.querySelector('#menu-button');
                let nav = document.querySelector('#nav');
                if (button.classList.contains('open')) {
                    nav.style.opacity = '0';
                    setTimeout(() => {
                        nav.style.display = 'flex';
                        button.classList.remove('open');
                    }, 250);
                } else {
                    nav.style.display = 'flex';
                    nav.style.opacity = '1';
                    setTimeout(() => {
                        button.classList.add('open');
                    }, 250)
                }
            }

            window.addEventListener('resize', function(event){
                if (window.innerWidth > 766) {
                    let button = document.querySelector('#menu-button');
                    let nav = document.querySelector('#nav');
                    button.style.opacity = '0';
                    button.style.display = 'none';
                    nav.style.opacity = '1';
                    nav.style.display = 'flex';
                } else {
                    let button = document.querySelector('#menu-button');
                    let nav = document.querySelector('#nav');
                    button.style.opacity = '1';
                    button.style.display = 'block';
                    nav.style.opacity = '0';
                    nav.style.display = 'none';
                }
            });

        </script>
    </body>
</html>

