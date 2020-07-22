@extends('layouts.app')

@section('content')
<link href="{{ asset('css/guest.css') }}" rel="stylesheet" type="text/css" >
<div class="container login-form-container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header login-header login-header-saved">
                    <span  class="login-title-1">SPECTRUM</span><span  class="login-title-2">OVERSEE</span>
                </div>
                @if(session('data')['order_number'])
                <div class="card-body">
                    <div class="container check-div">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="container order-number-div">
                        Request Submitted </br> Successfully
                    </div>
                    <div class="container order-number-div-2">
                        ORDER #{{session('data')['order_number']}}
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-11 mx-auto">
                        <a href="{{session('data')['url']}}">
                        <button  type="button" class="btn btn-primary btn-block login-button">
                            OK
                        </button>
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection