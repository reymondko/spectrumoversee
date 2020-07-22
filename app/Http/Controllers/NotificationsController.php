<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifications;
use App\User;

class NotificationsController extends Controller
{
    public function getUnseenNotificationCount(){
        $notifications = Notifications::where('companies_id',\Auth::user()->companies_id)->where('seen',0)->count();
        if($notifications){
            $response = array(
                "status" =>"ok",
                "result" =>array("total"=>$notifications)
            );
            return response()->json($response,200);
        }else{
            $response = array(
                "status" =>"ok",
                "result" =>array("total"=>0)
            );
            return response()->json($response,200);
        }
    }

    public function getNotifications(){
        $notifications = Notifications::select('type','message')->where('companies_id',\Auth::user()->companies_id)->orderByDesc('id')->get();
        Notifications::where('companies_id',\Auth::user()->companies_id)->update(['seen' => 1]);
        if($notifications){
            $response = array(
                "status" =>"ok",
                "result" =>array("notifications"=>$notifications)
            );
            return response()->json($response,200);
        }else{
            $response = array(
                "status" =>"no records",
                "result" =>array("notifications"=>null)
            );
            return response()->json($response,200);
        }
    }

    
}
