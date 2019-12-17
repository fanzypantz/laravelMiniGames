@extends('layouts.layout')

@section('content')
<div class="container">
    <div>{{ __('Confirm Password') }}</div>

    <div>
        {{ __('Please confirm your password before continuing.') }}

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div>
                <label for="password">{{ __('Password') }}</label>

                <div class="col-md-6">
                    <input id="password" type="password" class="@error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                    @error('password')
                        <span role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div>
                <div>
                    <button type="submit">
                        {{ __('Confirm Password') }}
                    </button>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

</div>
@endsection
