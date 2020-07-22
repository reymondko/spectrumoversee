@extends('layouts.app')

@section('content')
<link href="{{ asset('css/forgotpassword.css') }}" rel="stylesheet" type="text/css" >
<div class="container reset-form-container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header reset-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group row">
                          <div class="input-group mb-3 col-md-12 mx-auto">
                                <div class="input-group-append">
                                    <span class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }} input-group-text reset-icon-text"  id="basic-addon2"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                                </div>
                                <input id="email" type="email" placeholder="Email Address" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }} reset-form" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-12 ">
                                <button type="submit" class="btn btn-primary btn-block reset-button">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </div>
                        </div>

                        <div class="col-sm-5 col-xs-12 mx-auto login-bottom-text-div">
                            <a class="btn btn-link login-bottom-text" href="/">
                                {{ __('Go back') }}
                            </a>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
