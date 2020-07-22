<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Models\ShippingClient;
use App\Models\ShippingCarriers;

class ThirdPartyShippingClientController extends Controller
{
    public function index()
    {
        if (! Gate::allows('admin-only', auth()->user())) {
            abort(403);
        }

        $shipping_clients = ShippingClient::all();
        $carriers = ShippingCarriers::all();

        return view('layouts.thirdparty.shippingclients', compact('shipping_clients','carriers'));
    }

    public function store(Request $request)
    {
        $data = request()->validate([
            'name' => 'required',
            'tpl_client_id' => 'required',
            'require_scan_serial_number'=>'required',
            //'shipping_carriers'=>'required',
            'shipping_markup' => 'required',
        ]);
        //dd();
        if (is_array($request->shipping_carriers)) {
          $data['carriers'] =  implode(",", $request->shipping_carriers);
        } else {
          $data['carriers'] = '';
        }
        // insert input for required scan serial #
//         if ( request()->has('require_scan_serial_number') ) {
//             $data['require_scan_serial_number'] = true;
//         }
        unset($data['shipping_carriers']);
        ShippingClient::create($data);

        return redirect()->route('shippingclients');
    }

    public function patch(Request $request)
    {
        $data = request()->validate([
            'name' => 'required',
            'require_scan_serial_number'=>'required',
            'tpl_client_id' => 'required',
            //'shipping_carriers'=>'required',
            'shipping_markup' => 'required',
        ]);
        if (is_array($request->shipping_carriers)) {
          $data['carriers'] =  implode(",", $request->shipping_carriers);
        } else {
          $data['carriers'] = '';
        }
        unset($data['shipping_carriers']);
        // insert input for required scan serial #
//         if ( request()->has('require_scan_serial_number') ) {
//             $data['require_scan_serial_number'] = true;
//         }
//         else {
//             $data['require_scan_serial_number'] = false;
//         }
        ShippingClient::findOrFail( request()->input('id') )->update($data);

        return redirect()->route('shippingclients');
    }

    public function remove()
    {
        ShippingClient::findOrFail( request()->input('id') )->delete();

        return redirect()->route('shippingclients');
    }
}
