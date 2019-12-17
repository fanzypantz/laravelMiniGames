@extends('layouts.layout')

@section('content')

    @auth
        <div class="index-container">
            <h1>Welcome to the Game Of Thrones inspired Snakes and Ladders game</h1>
            <p>I own no rights to the GOT franchise, this is a school project. Please don't sue me.</p>
            <form action="{{ route('startLobby') }}" method="POST">
                @csrf
                @method('POST')
                <button type="submit">
                    <img class="button-icon" src="{{asset('images/icons/sofa.svg')}}" alt="">
                    <span class="text">Start New Lobby</span>
                </button>
            </form>
        </div>
    @endauth


@endsection

