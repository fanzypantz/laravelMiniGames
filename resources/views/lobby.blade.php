@extends('layouts.layout')

@section('content')

    @auth
        <lobby-component
            :user="{{ Auth::user() }}"
            :lobby="{{ $lobby }}"
        ></lobby-component>
    @endauth

@endsection

