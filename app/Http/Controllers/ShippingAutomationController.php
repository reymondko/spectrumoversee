<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\ShippingAutomationRules;
use App\Models\ShippingCarriers;
use App\Models\ShippingCarrierMethods;
use App\Models\Shipments;
use App\Models\Companies;
use Illuminate\Support\Facades\Gate;

class ShippingAutomationController extends Controller
{
    public function automationRules()
    {
        if (Gate::allows('admin-only', auth()->user())) {

            $shippingCarriers = ShippingCarriers::get();
            $shippingCarrierMethods = ShippingCarrierMethods::get();
            $printers = $this->fetchShiprushPrinters();
            $companies = array();

            if(Gate::allows('company-only', auth()->user())){
                $automationRules = ShippingAutomationRules::where('companies_id',\Auth::user()->companies_id)->get();
            }else{
                $automationRules = ShippingAutomationRules::with('company')->get();
                $companies = Companies::get();
            }

            $data = array(
                'shipping_carriers' => $shippingCarriers,
                'shipping_carrier_methods' => $shippingCarrierMethods,
                'automation_rules' => $automationRules,
                'companies' => $companies,
                'printers' => $printers
            );
            
            return view('layouts/shipping/automation')->with('data',$data);
        }
        return abort(403, 'Unauthorized action');
    }

    public function automationRulesSave(Request $request)
    {
        if (Gate::allows('admin-only', auth()->user())) {
            $newRule = new ShippingAutomationRules;

            $domesticCarrier = array();
            foreach($request->carrier_name_dom as $key => $value){
                $domesticCarrier[] = array(
                    'carrier' => $value,
                    'method' => $request->carrier_method_dom[$key]
                );
            }

            $internationalCarrier = array();
            foreach($request->carrier_name_int as $key => $value){
                $internationalCarrier[] = array(
                    'carrier' => $value,
                    'method' => $request->carrier_method_int[$key]
                );
            }

            $shiprush_printers = array();
            foreach($request->printer_ids as $value){
                if ($value != null) array_push($shiprush_printers, $value);
            }
            
            if(Gate::allows('company-only', auth()->user())){
                $newRule->companies_id = \Auth::user()->companies_id;
            }else{
                $newRule->companies_id = $request->company;
            }

            $newRule->name = $request->rule_name;
            $newRule->shipping_carriers_domestic = JSON_ENCODE($domesticCarrier);
            $newRule->shipping_carriers_international = JSON_ENCODE($internationalCarrier);
            $newRule->shiprush_printers = JSON_ENCODE($shiprush_printers);
            $newRule->min_shipping_days = $request->min_shipping_days;
            $newRule->max_shipping_days = $request->max_shipping_days;
            $newRule->automation_method = $request->automation_method;
            $newRule->threepl_customer_id = $request->threepl_customer_id;
            if($newRule->save()){
                return redirect()->route('shiping_automation_rules')->with('status','saved');
            }
        }
        return abort(403, 'Unauthorized action');
    }

    public function automationRulesUpdate(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {
            
            if(Gate::allows('company-only', auth()->user())){
                $rule = ShippingAutomationRules::where('companies_id',\Auth::user()->companies_id)
                                               ->where('id',$request->shipping_automation_rule_id)
                                               ->first();
            }else{
                $rule = ShippingAutomationRules::where('id',$request->shipping_automation_rule_id)
                                               ->first();
            }

            $domesticCarrier = array();
            foreach($request->edit_carrier_name_dom as $key => $value){
                $domesticCarrier[] = array(
                    'carrier' => $value,
                    'method' => $request->edit_carrier_method_dom[$key]
                );
            }

            $internationalCarrier = array();
            foreach($request->edit_carrier_name_int as $key => $value){
                $internationalCarrier[] = array(
                    'carrier' => $value,
                    'method' => $request->edit_carrier_method_int[$key]
                );
            }
            if($rule){
                $rule->name = $request->edit_rule_name;
                $rule->shipping_carriers_domestic = JSON_ENCODE($domesticCarrier);
                $rule->shipping_carriers_international = JSON_ENCODE($internationalCarrier);
                $rule->min_shipping_days = $request->edit_min_shipping_days;
                $rule->max_shipping_days = $request->edit_max_shipping_days;
                $rule->automation_method = $request->edit_automation_method;
                $rule->threepl_customer_id = $request->edit_threepl_customer_id;
                if($rule->save()){
                    return redirect()->route('shiping_automation_rules')->with('status','saved');
                }
            }
            
        }
        return abort(403, 'Unauthorized action');
        // return redirect()->route('dashboard');
    }

    public function automationRulesDelete(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {

            if(Gate::allows('company-only', auth()->user())){
                $rule = ShippingAutomationRules::where('id',$request->id)
                                               ->where('companies_id',\Auth::user()->companies_id)
                                               ->first();
            }else{
                $rule = ShippingAutomationRules::where('id',$request->id)
                                                ->first();
            }

            if($rule){
                $rule->delete();
                return redirect()->route('shiping_automation_rules')->with('status','deleted');
            }
        }

        return redirect()->route('dashboard');
    }

    public function automationRulesDetails(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {
            
            if(Gate::allows('company-only', auth()->user())){
                $rule = ShippingAutomationRules::where('id',$request->id)
                                               ->where('companies_id',\Auth::user()->companies_id)
                                               ->first();
            }else{
                $rule = ShippingAutomationRules::where('id',$request->id)
                                                ->first();
            }

            if($rule){
                $rule->shipping_carriers_domestic = JSON_DECODE($rule->shipping_carriers_domestic,true);
                $rule->shipping_carriers_international = JSON_DECODE($rule->shipping_carriers_international,true);

                $response = array(
                    'success' => true,
                    'data'=>$rule
                );
                
                return response()->json($response,200);
            }
        }
        return abort(403, 'Unauthorized action');
        // return response()->json(array('status' =>'error','message'=>'unauthorized'),200);
    }

    public function shipments(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {
            
            if(Gate::allows('company-only', auth()->user())){
                $shipments = Shipments::with('deliveryAddress','shipperAddress')->where('companies_id',\Auth::user()->companies_id)->get();
            }else{
                $shipments = Shipments::with('deliveryAddress','shipperAddress','company')->orderByDesc('companies_id')->get();
            }

            $data = array(
                'shipments' => $shipments,
            );
       
            return view('layouts/shipping/shipments')->with('data',$data);
        }
        return abort(403, 'Unauthorized action');
        // return redirect()->route('dashboard');
    }

    private function fetchShiprushPrinters() 
    {
        $client = new Client();
            $req = $client->request('POST', 'https://api.my.shiprush.com/accountservice.svc/print/getprinters', [
                'headers' => [
                    'X-SHIPRUSH-SHIPPING-TOKEN' => '88c9a9a7-de94-49f4-a6f6-0ff33bfe8f8b',
                    'Content-Type' => 'application/xml'
                ],
                'body' => '<?xml version="1.0" encoding="utf-8"?><GetPrintersRequest></GetPrintersRequest>'
            ]);
 
            $response = simplexml_load_string($req->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);
            $initJson = json_encode($response->Printers);
            $initArray = json_decode($initJson,TRUE);
            $printers = array();
            //$printers = json_encode($initArray['CloudPrinter']);
            foreach($initArray['CloudPrinter'] as $printer)
            {
                $printerName = stripslashes($printer['PrinterName']);
                $printerName = str_replace('dc01','',$printerName);
                $printerName = stripslashes($printerName);
                
                if($printer['IsOnline'] == 'true'){
                    $printers[] = array(
                        'ComputerId'=>$printer['ComputerId'],
                        'ComputerName'=>$printer['ComputerName'],
                        'PrinterId'=>$printer['PrinterId'],
                        'IsOnline'=>$printer['IsOnline'],
                        'WebShippingPrinterId'=>$printer['WebShippingPrinterId'],
                        //'PrinterName'=>$printer['PrinterName'],
                        'PrinterName'=>$printerName,
                    );
                }
            }

            return $printers;
    }
}
