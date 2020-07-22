<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\Models\Companies;
use App\Models\LogiwaDepositor;
use App\Models\Locations;
use App\Models\LocationTypes;
use App\Models\ApiTokens;
use App\User;
use Carbon\Carbon;

class CompaniesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Gate::allows('admin-only', auth()->user())) {
            $companies = Companies::where('deleted',0)->get();
            return view('layouts/companies/companies')->with('companies', $companies);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function addCompany()
    {
        if (Gate::allows('admin-only', auth()->user())) {
            if($_POST){
                $email_check = User::where('email',$_POST['email'])->first();
                if($email_check){
                    return redirect('companies')->with('status', 'email_error');
                }else{
                    $company = new Companies;
                    $company->company_name = $_POST['company_name'];
                    $company->api_key = Hash::make($_POST['company_name'].time());
                    $company->save();

                    /*$location = new Locations;
                    $location->name = $_POST['company_admin_location'];
                    $location->companies_id = $company->id;
                    $location->save();*/

                    //make sure all companies have the default locations
                    $this->createCompanyDefaultLocations();

                    $user = new User;
                    $user->email    = $_POST['email'];
                    $user->name    = $_POST['name'];
                    $user->password =  Hash::make($_POST['password']);
                    $user->role = 2;
                    $user->companies_id = $company->id;
                    //$user->location_id = $location->id;
                    $user->save();

                    // return redirect()->route('companies');
                    return redirect('companies')->with('status', 'saved');
                }
            }
        }else{
            return redirect()->route('dashboard');
        }
    }


    public function companyLocations(){
        if (Gate::allows('admin-only', auth()->user())){
            $locations = Locations::where('tpl_customer_id',$_GET['companies_id'])->get();
            $company = Companies::where('id',$_GET['companies_id'])->first();
            if($company){
                $company_name = $company->company_name;
            }else{
                $company_name = "N/A";
            }

            $location_types = LocationTypes::get();

            $data = array(
                'locations' => $locations,
                'companies' =>array('companies_id'=>$_GET['companies_id'],'companies_name'=>$company_name),
                'location_types' => $location_types
            );
            return view('layouts/companies/companies_locations')->with('data',$data);
        }
        return redirect()->route('dashboard');
    }

    public function companySaveLocations(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
            if($request->companies_id){
                foreach($request->locations as $loc){
                    $location = new Locations;
                    $location->name = $loc;
                    if (is_numeric($request->location_type)) {
                      $location->location_type = $request->location_type;
                    } else {
                      $location->location_type = null;
                    }
                    $location->tpl_customer_id = $request->companies_id;
                    $location->save();
                }
            }

            return redirect()->route('company_locations',['companies_id'=>$request->companies_id])->with('status','saved');
        }
        return redirect()->route('dashboard');
    }

    public function companyUpdateLocations(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
            if($request->edit_location_id){
                $location = locations::where('id',$request->edit_location_id)
                                     ->where('tpl_customer_id',$request->companies_id)
                                     ->first();
                if($location){
                    $location->name = $request->edit_location_name;
                    if (is_numeric($request->edit_location_type)) {
                      $location->location_type = $request->edit_location_type;
                    } else {
                      $location->location_type = null;
                    }
                }

                $location->save();
            }

            return redirect()->route('company_locations',['companies_id'=>$request->companies_id])->with('status','saved');
        }
        return redirect()->route('dashboard');
    }

    public function companyDeleteLocations(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
            if($request->delete_location_id){
                $location = locations::where('id',$request->delete_location_id)
                                     ->where('tpl_customer_id',$request->companies_id)
                                     ->first();

                $location->delete();
            }

            return redirect()->route('company_locations',['companies_id'=>$request->companies_id])->with('status','saved');
        }
        return redirect()->route('dashboard');
    }

    public function companyManageApiKeys(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
            $apiKeys = ApiTokens::where('companies_id',$_GET['companies_id'])->get();
            $company = Companies::where('id',$_GET['companies_id'])->first();
            if($company){
                $company_name = $company->company_name;
            }else{
                $company_name = "N/A";
            }
            $data = array(
                'api_keys' => $apiKeys,
                'companies' =>array('companies_id'=>$_GET['companies_id'],'companies_name'=>$company_name),
            );
            return view('layouts/companies/companies_apikeys')->with('data',$data);
        }
        return redirect()->route('dashboard');
    }

    public function generateKey(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
            $company =  Companies::where('id',$request->companies_id)->first();

            if($company->api_key == null){
                $company->api_key = Hash::make($company->company_name.time());
                $company->save();
            }


            /**
             * generate unique token by encrypting
             * company id, company_name and current time and api_key as encryption key
             */

            $raw = $company->id.'&'.$company->company_name.'&'.time();
            $key = $company->api_key;
            $cipher = "AES-256-CBC";

            $iv = random_bytes(16);
            $token = openssl_encrypt($raw, $cipher, $key, 0, $iv);

            $apiToken = new ApiTokens;
            $apiToken->companies_id = $request->companies_id;
            $apiToken->api_token = $token;
            $apiToken->iv = base64_encode($iv);
            $apiToken->save();

            return redirect()->route('manage_apikeys',['companies_id' => $request->companies_id]);
        }
        return redirect()->route('dashboard');
    }

    public function toggleApiKey(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
            $apiToken = ApiTokens::where('id',$request->id)->first();
            if($apiToken){
                if($apiToken->enabled == 1){
                    $apiToken->enabled = 0;
                }else{
                    $apiToken->enabled = 1;
                }

                if($apiToken->save()){
                    return redirect()->route('manage_apikeys',['companies_id' => $apiToken->companies_id]);
                }
            }
            return redirect()->route('dashboard');
        }
    }

    public function getCompanyFulfillmentDetails(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
           $company = Companies::select('company_name','fulfillment_ids','allow_only_tpl')->where('id',$request->companies_id)->first();
           if($company){
            return response()->json(array('success' =>true,'data' => array('company_name'=>$company->company_name,'fulfillment_ids'=>$company->fulfillment_ids,'allow_only_tpl'=>$company->allow_only_tpl)),200);
           }
        }
        return response()->json(array('status' =>'error','message'=>'unauthorized'),403);
    }

    public function saveFulfillmentIds(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
            $company = Companies::where('id',$request->companies_id)->first();
            if($company){
                $company->fulfillment_ids = ($request->fulfillment_ids != '' ? $request->fulfillment_ids:NULL);
                $company->customer_code = ($request->customer_code != '' ? $request->customer_code:NULL);
                $company->allow_only_tpl = ($request->tpl_only_check == 1 ? 1:0);
                if($company->save()){
                    return redirect('companies')->with('status', 'saved');
                }
            }

            //make sure all companies have the default locations
            $this->createCompanyDefaultLocations();
        }
        return redirect()->route('dashboard');
    }

    public function deleteCompany(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
           $company = Companies::where('id',$request->company_id)->first();
           if($company){
               $company->deleted = 1;
               $company->deleted_at = Carbon::now();
               $company->save();

               User::where('companies_id',$company->id)
                    ->update(['deleted'=>1,'deleted_at'=>Carbon::now()]);

                return redirect('companies')->with('status', 'deleted');
           }
        }

        return redirect()->route('dashboard');
    }


    public function createCompanyDefaultLocations() {
      $companies = Companies::all();
      foreach ($companies as $company) {
        $tpl_customer_ids = explode(',', $company->fulfillment_ids);
        if (count($tpl_customer_ids)) {
          foreach ($tpl_customer_ids as $tpl_customer_id) {
            if (!is_numeric($tpl_customer_id) || $tpl_customer_id <= 0)
              continue;

            //see if this tpl customer already has locations setup
            $result = Locations::where('tpl_customer_id', $tpl_customer_id)->get();
            if (count($result) <= 0) {
              //create each of the locations for the tpl customer
              Locations::create([
                'name' => 'Spectrum',
                'tpl_customer_id' => $tpl_customer_id,
                'location_type' => 1 //Spectrum
              ]);
              Locations::create([
                'name' => 'Customer',
                'tpl_customer_id' => $tpl_customer_id,
                'location_type' => 2 //Customer
              ]);
              Locations::create([
                'name' => 'Lab',
                'tpl_customer_id' => $tpl_customer_id,
                'location_type' => 3 //Lab
              ]);
            }
          }
        }
      }
    }

    public function saveCompanyPermissions(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
            $company = Companies::where('id',$request->permission_companies_id)->first();
            if($company){
                if($request->permission_can_manual_fulfill == 1){
                    $company->can_manual_fulfill = 1;
                }else{
                    $company->can_manual_fulfill = 0;
                }
                if($company->save()){
                    return redirect('companies')->with('status', 'saved');
                }
            }
        }
        return redirect()->route('dashboard');
    }

    public function getCompanyCustomerCodes(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
           $customerCode = LogiwaDepositor::where('companies_id',$request->companies_id)->get();
           if($customerCode){
            return response()->json(array('success' =>true,'data' =>$customerCode),200);
           }
        }
        return response()->json(array('status' =>'error','message'=>'unauthorized'),403);
    }

    public function saveCustomerCodes(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
            if(!empty($request->depositor_id)){
                // Delete current record
                LogiwaDepositor::where('companies_id',$request->depositor_id_companies_id)->delete();
                $saveArray = [];
                foreach($request->depositor_id as $key => $value){
                    $saveArray[] = [
                        'companies_id' => $request->depositor_id_companies_id,
                        'logiwa_depositor_code' =>$request->depositor_code[$key],
                        'logiwa_depositor_id' => $value,
                    ];
                }
                LogiwaDepositor::insert($saveArray);
            }

            return redirect('companies')->with('status', 'saved');
        }
        return redirect()->route('dashboard');
    }

    public function toggleEndToEndTracking(Request $request,$company_id)
    {
        if (Gate::allows('admin-only', auth()->user())) {
            $companies = Companies::where('id',$company_id)->first();
            if($companies){
                if($companies->end_to_end_tracking == 0){
                    $companies->end_to_end_tracking = 1;
                }else{
                    $companies->end_to_end_tracking = 0;
                }
                $companies->save();
            }
            return redirect('companies')->with('status', 'saved');
        }else{
            return redirect()->route('dashboard');
        }
    }


}
