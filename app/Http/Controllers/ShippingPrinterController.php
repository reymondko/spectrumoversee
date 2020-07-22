<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingPrinter;


class ShippingPrinterController extends Controller
{
    public function index()
    {
        $printers = ShippingPrinter::all();

        return view('layouts.shipping.printermanagement', compact('printers'));
    }

    public function store(Request $request) 
    {
        $data = $request->validate([
            'name' => 'required|unique:shipping_printers|max:255',
            'description' => 'required|max:255',
        ]);

        ShippingPrinter::create($data);

        return redirect()->back();
    }

    public function patch(Request $request) 
    {
        $data = $request->validate([
            'name' => 'required|unique:shipping_printers,name,'.$request->id.'|max:255',
            'description' => 'required|max:255',
        ]);

        ShippingPrinter::find($request->id)->update($data);

        return redirect()->back();
    }

    public function remove(Request $request)
    {
        if ($request->id)
        {
            if ( ShippingPrinter::where('id', $request->id)->exists() )
            {
                ShippingPrinter::destroy($request->id);
                
                return redirect()->back();
            }
        }

        return redirect()->back();
    }
}
