@extends('layouts.app')

@section('content')
<link href="{{ asset('css/guest.css') }}" rel="stylesheet" type="text/css" >
<div class="container login-form-container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header login-header">
                    <span  class="login-title-1">SPECTRUM</span><span  class="login-title-2">OVERSEE</span>
                </div>

                <div class="card-body">
                    <form method="POST" id="custom_order_form" action={{route('guest_custom_order_save')}}>
                        @csrf
                        <div class="form-group container">
                            <div class="col-md-12 company_label">Company<a href"#" class="previous-orders" data-toggle="modal" data-target="#previousOrdersModal"> View Previous Orders</a></div>
                            <div class="row col-md-12">
                                <div class="col-md-12 company_title">{{$data['custom_order']['company_name']}}
                                    
                                </div>
                            </div>
                        </div>
                        
                        @foreach($data['custom_order_data'] as $key => $value)
                        <div class="form-group container">
                            <div class="col-md-12 company_label">{{$key}} Needed</div>
                            <select class="form-control col-md-12 order-select" name="customorder_val[]">
                                <option value="{{ $value['min'] }}">{{ $value['min'] }}</option>
                                @for ($select_val = $value['quantities']; $select_val < $value['max']; $select_val = $select_val + $value['quantities'])
                                    @if($select_val > $value['min'])
                                    <option value="{{ $select_val }}">{{ $select_val }}</option>
                                    @endif
                                @endfor
                                <option value="{{ $value['max'] }}">{{ $value['max'] }}</option>
                            </select>
                        </div>
                        @endforeach
                        </br>
                        <div class="form-group row">
                            <div class="col-md-11 mx-auto">
                                <button  type="submit" class="btn btn-primary btn-block login-button">
                                    Request
                                </button>
                            </div>
                        </div>
                    <input type="hidden" name="url" value="{{$data['url']}}"/>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="previousOrdersModal" tabindex="-1" role="dialog" aria-labelledby="previousOrdersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="previousOrdersModalLabel">Previous Orders</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      @foreach($data['previous_orders'] as $p)
      <div id="accordion">
        <div class="card">
            <div class="card-header" >
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse{{$p->order_number}}" aria-expanded="false" aria-controls="collapse{{$p->order_number}}">
                        Order #{{$p->order_number}} - {{date('M d, Y - H:i', strtotime($p->created_at))}}
                    </button>
                </h5>
            </div>
            <div id="collapse{{$p->order_number}}" class="collapse" data-parent="#accordion">
                <div class="card-body">
                <table class="table table-striped table-bordered order-items-table" style="width:100%">
                    <thead class="table_head">
                        <th class="table_head_th col-md-6" id="sku">SKU</th>
                        <th class="table_head_th col-md-4" id="sku">Quantity</th>
                    </thead>
                    <tbody>
                        @foreach($p['orderItems'] as $o)
                        <tr>
                            <td>{{$o->sku}}</td>
                            <td>{{$o->quantity}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
      @endforeach
    </div>
  </div>
</div>
<link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@endsection