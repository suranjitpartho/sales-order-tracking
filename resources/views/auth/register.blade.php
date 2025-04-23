@extends('layouts.guest')
@section('title', 'Register')
@section('content')
    
    <h2 class="section-title">Register</h2>

    <form method="POST" action="{{ route('register') }}" class="form-wrapper"> 
        @csrf

        <!-- FORM FIELDS -->

        <!-- Name -->
        <div class="form-group">
            <input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Name">
        </div>

        <!-- Email -->
        <div class="form-group">
            <input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Email">
            @if ($errors->has('email'))
                <span class="error">{{ $errors->first('email') }}</span>
            @endif 
        </div>

        <!-- Password -->
        <div class="form-group">
            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Password">
            @if ($errors->has('password'))
                <span class="error">{{ $errors->first('password') }}</span>
            @endif            
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm Password">
            @if ($errors->has('password_confirmation'))
                <span class="error">{{ $errors->first('password_confirmation') }}</span>
            @endif   
        </div>

        <!-- BUTTONS -->
        <div class="form-actions">
            <button type="submit" class="btn">{{ __('Register') }}</button>
            <a class="btn" href="{{ route('login') }}"">{{ __('Already registered?') }}</a>
        </div>
    </form>

@endsection
