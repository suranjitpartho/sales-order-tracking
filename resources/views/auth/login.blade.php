@extends('layouts.guest')
@section('title', 'Login')
@section('content')
    <h2 class="section-title">Login</h2>
    <form method="POST" action="{{ route('login') }}" class="form-wrapper">
        @csrf

        <!-- FORM FIELDS -->
        <div class="form-group">
            <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Email">
        </div>
        <div class="form-group">
            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Password">
        </div>
        <div class="form-group checkbox">
            <label>
                <input id="remember_me" type="checkbox" name="remember">
                Remember User
            </label>
        </div>

        <!-- BUTTONS -->
        <div class="form-actions">
            <button type="submit" class="btn">{{ __('Log in') }}</button>
            @if (Route::has('password.request'))
                <a class="btn" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>
    </form>
@endsection