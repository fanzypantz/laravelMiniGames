@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="auth">
        @if (session('status'))
            <div role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div>
                <label for="email">{{ __('E-Mail Address') }}</label>

                <div>
                    <input id="email" type="email" class="@error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                    @error('email')
                        <span role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div>
                <div>
                    <button type="submit">
                        {{ __('Send Password Reset Link') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
