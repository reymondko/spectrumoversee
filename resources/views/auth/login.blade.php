@extends('layouts.app')

@section('content')
<link href="{{ asset('css/login.css') }}" rel="stylesheet" type="text/css" >
<div class="container login-form-container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header login-header">
                    <span  class="login-title-1">SPECTRUM</span><span  class="login-title-2">OVERSEE</span> &nbsp; <span  class="login-title-1">Logiwa</span>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group row">
                            <div class="input-group mb-3 col-md-11 mx-auto">
                            <div class="input-group-append">
                                <span class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }} input-group-text login-icon-text" id="basic-addon2"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                            </div>
                                <input id="email" type="email" placeholder="Email Address" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }} login-form" name="email" value="{{ old('email') }}"  required autofocus >
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="input-group mb-3 col-md-11 mx-auto">
                                <div class="input-group-append">
                                    <span class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }} input-group-text login-icon-text" id="basic-addon2"><i class="fa fa-key" aria-hidden="true"></i></span>
                                </div>
                                <input id="password" type="password" placeholder="Password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }} login-form" name="password" required>
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-11 mx-auto">
                                <button  type="submit" class="btn btn-primary btn-block login-button">
                                    {{ __('LOGIN') }}
                                </button>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-5 mx-auto login-checkbox login-bottom-text-div">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label login-bottom-text" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-5 col-xs-12 mx-auto login-bottom-text-div">
                                @if (Route::has('password.request'))
                                    <a class="btn btn-link login-bottom-text" href="{{ route('password.request') }}">
                                        {{ __('Forgot Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
