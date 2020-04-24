@extends('layouts.layout')

@section('content')



    <div class="index-container">
        <h1>Welcome to my small Minigame site. It will have all sorts of minigames in the future!</h1>
        <p>This is a hobby project of mine to get better at Laravel and Vue.</p>
        <p>If the app does not work, you are probably using an adblocker. There are no advertisement on this website, don't worry.</p>
        @guest
            <div class="index-auth">
                <a class="btn" href="{{ route('register') }}">
                    <strong>Sign up</strong>
                </a>
                <a class="btn" href="{{ route('login') }}">Log in</a>
            </div>
        @else
            <form action="{{ route('startLobby') }}" method="POST">
                @csrf
                @method('POST')
                <button type="submit">
                    <img class="button-icon" src="{{asset('images/icons/sofa.svg')}}" alt="">
                    <span class="text">Start New Lobby</span>
                </button>
            </form>
        @endguest
    </div>


@endsection

