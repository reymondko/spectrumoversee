<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use Session;
use App\User;
use App\Models\UserLogs;
use App\Models\LastLogin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    protected $maxAttempts = 3;
    protected $decayMinutes = 60; //in minutes
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * overriden login functionality.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function login(Request $request)
    {

        if ($this->hasTooManyLoginAttempts($request)) {
            //check if reset attempts has been granted
            $reset = User::where('email', $request->email)->first();
            if($reset){
                if($reset->reset_attempts == 1){
                    $reset->reset_attempts = null;
                    $reset->save();
                    $this->clearLoginAttempts($request);
                }else{
                        $this->fireLockoutEvent($request);
                        return $this->sendLockoutResponse($request);
                }
            }else{
                $this->fireLockoutEvent($request);
                return $this->sendLockoutResponse($request);
            }

        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password,'deleted' => 0])) {
            $this->sendLoginResponse($request);
            return redirect()->intended('dashboard');
        }  else {
            $this->incrementLoginAttempts($request);
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

     /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $userLogs = new UserLogs;
        $userLogs->users_id = \Auth::user()->id;
        $userLogs->companies_id = \Auth::user()->companies_id;
        $userLogs->log_type = 'login';
        $userLogs->ip = $_SERVER['REMOTE_ADDR'];
        $userLogs->created_at = Carbon::now();
        $userLogs->updated_at = Carbon::now();
        $userLogs->save();


        $lastLogin = LastLogin::where('users_id',\Auth::user()->id)->first();
        if($lastLogin){
            $lastLogin->created_at = Carbon::now();
        }else{
            $lastLogin = new LastLogin;
            $lastLogin->users_id = \Auth::user()->id;
            $lastLogin->companies_id = \Auth::user()->companies_id;
        }

        $lastLogin->save();

        //clear reset attempts flag
        $user = User::where('id', \Auth::user()->id)->first();
        $user->reset_attempts = null;
        $user->save();
    }


    public function logout(Request $request) {

        //logout from masquerade function
        $id = Session::pull( 'orig_user' );
        if(isset($id)){
            $orig_user = User::find($id);
            Auth::login($orig_user);
            return redirect()->route('dashboard');
        }

        $userLogs = new UserLogs;
        $userLogs->users_id = \Auth::user()->id;
        $userLogs->companies_id = \Auth::user()->companies_id;
        $userLogs->log_type = 'logout';
        $userLogs->ip = $_SERVER['REMOTE_ADDR'];
        $userLogs->created_at = Carbon::now();
        $userLogs->updated_at = Carbon::now();
        $userLogs->save();



        Auth::logout();
        return redirect('/');
    }
}
