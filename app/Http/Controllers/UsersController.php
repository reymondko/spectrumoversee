<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;
use App\Models\Companies;
use App\Models\Locations;
use App\Models\UserPermissions;
use App\Models\UserLogs;
use App\Models\Roles;
use App\Mail\RequestHelpEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use App\User;
use Carbon\Carbon;
use Session;
use Auth;

class UsersController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::allows('admin-only', auth()->user()) || Gate::allows('can_login_as', auth()->user())) {

            if(Gate::allows('company-only', auth()->user())){
                $users = User::where('id','<>',\Auth::user()->id)
                         ->where('companies_id',\Auth::user()->companies_id)
                         ->where('role','<>',1)
                         ->where('deleted',0)
                         ->with('companies:id,company_name')
                         ->with('locations:id,name')
                         ->with('latestLogIn')
                         ->get();
            }else{
                $users = User::where('id','<>',\Auth::user()->id)
                         ->where('deleted',0)
                         ->with('companies:id,company_name')
                         ->with('locations:id,name')
                         ->with('latestLogIn')
                         ->get();
            }


            $companies = Companies::where('deleted',0)->orderBy('company_name')->get();
            $roles = Roles::get();
            //$locations = Locations::where('companies_id',\Auth::user()->companies_id)->get();

            $data = array(
                'users' => $users,
                'companies' =>$companies,
                //'locations' => $locations,
                'roles' => $roles
            );

            return view('layouts.users.users')->with('data',$data);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function addUser(){
        if (Gate::allows('company-only', auth()->user()) || Gate::allows('admin-only', auth()->user())) {
            $email_check = User::where('email',$_POST['email'])->first();
                if($email_check){
                    $deleted_check = User::where('email',$_POST['email'])->where('deleted','1')->first();
                    if($deleted_check){
                        return redirect('users')->with('status', 'email_deleted_error')
                        ->with('user_id',$deleted_check->id)
                        ->with('user_name',$deleted_check->name)
                        ->with('user_email',$deleted_check->email);
                    }
                    else{
                        return redirect('users')->with('status', 'email_error');
                    }

                }else{

                    if(Gate::allows('admin-only', auth()->user())){
                        $user = new User;
                        $user->email    = $_POST['email'];
                        $user->name    = $_POST['name'];
                        $user->password =  Hash::make($_POST['password']);
                        $user->role = $_POST['role'];
                        // $user->location_id = $_POST['location'];

                        if($_POST['company'] !== 0){
                            $user->companies_id = $_POST['company'];
                        }
                        $user->save();
                    }elseif(Gate::allows('company-only', auth()->user())){
                        $user = new User;
                        $user->email    = $_POST['email'];
                        $user->name    = $_POST['name'];
                        $user->password =  Hash::make($_POST['password']);
                        $user->role = 3; //basic user
                        $user->companies_id = \Auth::user()->companies_id;
                        $user->location_id = $_POST['location'];
                        $user->save();

                        if($_POST['add_permissions']){
                            $permissionsArray = array();
                            foreach($_POST['add_permissions'] as $p){
                                $tmp = array(
                                    "companies_id" => \Auth::user()->companies_id,
                                    "users_id" => $user->id,
                                    "permission_name" => $p,
                                );
                                $permissionsArray[] = $tmp;
                            }

                            if(!empty($permissionsArray)){
                                UserPermissions::insert($permissionsArray);
                            }
                        }

                    }

                    // return redirect()->route('companies');
                    return redirect('users')->with('status', 'saved');
                }
        }else{
            return redirect()->route('dashboard');
        }
    }
    public function reactivateUser(Request $r){
        if($r->ru_user_id){
            if (Gate::allows('company-only', auth()->user()) || Gate::allows('admin-only', auth()->user())) {
                if(Gate::allows('company-only', auth()->user())){
                    $user = User::where('companies_id',\Auth::user()->companies_id)
                             ->where('id',$r->ru_user_id)
                             ->first();
                }elseif(Gate::allows('admin-only', auth()->user())){
                    $user = User::where('id',$r->ru_user_id)->first();
                }

                if($user){
                    $user->password =  Hash::make($r->ru_password);
                    $user->name = $r->ru_user_name;
                    $user->deleted = 0;
                    if($user->save()){
                        return redirect('users')->with('status', 'reactivated');
                    }
                }
            }

        }
    }
    public function updateUser(Request $request){
        if($request->id_edit){
            $permissions = UserPermissions::where('users_id',$request->id_edit)->delete();
            if (Gate::allows('company-only', auth()->user()) || Gate::allows('admin-only', auth()->user())) {
                if(Gate::allows('company-only', auth()->user())){
                    $user = User::where('companies_id',\Auth::user()->companies_id)
                             ->where('id',$request->id_edit)
                             ->first();
                }elseif(Gate::allows('admin-only', auth()->user())){
                    $user = User::where('id',$request->id_edit)->first();
                }

                if($user){
                    $user->name = $request->name_edit;
                    $user->email = $request->email_edit;
                    $user->location_id = $request->location_edit;


                    if($request->permissions){
                        $permissionsArray = array();
                        foreach($request->permissions as $p){
                            $tmp = array(
                                "companies_id" => $user->companies_id,
                                "users_id" => $request->id_edit,
                                "permission_name" => $p,
                            );
                            $permissionsArray[] = $tmp;
                        }

                        if(!empty($permissionsArray)){
                            UserPermissions::insert($permissionsArray);
                        }
                    }

                    if($user->save()){
                        return redirect('users')->with('status', 'saved');
                    }
                }
            }
        }

        return redirect()->route('dashboard');
    }

    public function sendResetEmail()
    {
        if (Gate::allows('company-only', auth()->user()) || Gate::allows('admin-only', auth()->user())) {

            $user_email = User::select('email')
                              ->where('id',$_GET['id'])
                              ->first();

            if(isset($user_email)){
                $response = Password::sendResetLink(['email' => $user_email->email], function (Message $message) {
                    $message->subject($this->getEmailSubject());
                });
                return redirect()->back()->with('status', 'reset_successful');
            }else{
                return redirect()->back()->with('status', 'reset_error');
            }

        }else{
            return redirect()->route('dashboard');
        }
    }

    public function logInAs(){

        if(Gate::allows('admin-only') || Gate::allows('can_login_as')){
            $logInAs = User::find($_GET['id']);
            if($logInAs){
                Session::put( 'orig_user', Auth::id());
                Auth::login( $logInAs );
                return redirect()->route('dashboard');
            }
        }

        return redirect()->back();
    }

    public function getUserDetails(Request $request){
        if (Gate::allows('company-only', auth()->user()) || Gate::allows('admin-only', auth()->user())) {
            if(Gate::allows('company-only', auth()->user())){
                $permissions = UserPermissions::where('users_id',$request->user_id)
                                          ->where('companies_id', explode(',', \Auth::user()->companies->fulfillment_ids))
                                          ->get();
                $locations = Locations::whereIn('tpl_customer_id',\Auth::user()->companies_id)->get();
            }elseif(Gate::allows('admin-only', auth()->user())){
                $permissions = UserPermissions::where('users_id',$request->user_id)
                                               ->get();
                $user = User::where('id',$request->user_id)->first();
                $locations = Locations::whereIn('tpl_customer_id', explode(',', $user->companies->fulfillment_ids))->get();

            }

            $response = array(
                "success" =>true,
                "result" =>array("permissions"=>$permissions,"locations" => $locations)
            );

            return response()->json($response,200);
        }

        $response = array(
            "success" =>false,
        );

        return response()->json($response,403);
    }

    public function sendRequestHelp(Request $request){

        $data = array(
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'description' => $request->description,
        );

        if(env('MAIL_TO_HELP')){
            $mailTo = env('MAIL_TO_HELP');
        }else{
            $mailTo = 'mark@digiance.com';
        }

        $mail = new \stdClass();
        $mail->message = $data;
        $mail->sender = 'Spectrum Oversee';
        $mail->subject = 'Spectrum Oversee Help Request';
        Mail::to($mailTo)->send(new RequestHelpEmail($mail));

        return response()->json(array('success' => true),200);
    }

    public function getUserLogs(Request $request){
        if (Gate::allows('company-only', auth()->user())) {
        $userLogs = UserLogs::where('users_id',$request->id)->where('companies_id',\Auth::user()->companies_id)->get();
        }

        if(Gate::allows('admin-only', auth()->user())){
            $userLogs = UserLogs::where('users_id',$request->id)->get();
        }

        if($userLogs){


            $tmpLogs = array();

            foreach($userLogs as $u){
                $u->formatted_date = $u->created_at->format('M d, Y - H:i');
                $tmpLogs[] = $u;
            }

            $data = array(
                'success' => true,
                'logs' => $tmpLogs
            );
            return response()->json($data,200);
        }else{
            return response()->json(array('success' => false),200);
        }
    }

    public function clearLoginAttempts(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
            $user = User::where('id',$request->id)->first();
            if($user){
                $user->reset_attempts = 1;
                $user->save();
                return redirect()->route('users')->with('status','saved');
            }
        }
        return redirect()->route('dashboard');
    }

    public function deleteUser(Request $request){
        if (Gate::allows('admin-only', auth()->user())){
           $user = User::where('id',$request->user_id)->first();
           if($user){
               $user->deleted = 1;
               $user->deleted_at = Carbon::now();
               $user->save();

               return redirect('users')->with('status', 'deleted');
           }
        }

        return redirect()->route('dashboard');
    }
}
