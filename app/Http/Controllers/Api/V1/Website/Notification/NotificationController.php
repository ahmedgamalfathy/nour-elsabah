<?php

namespace App\Http\Controllers\Api\V1\Website\Notification;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Client\Client;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Redis;

class NotificationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:client'),
        ];
    }
    public function notifications(Request $request)
    {
        $auth = $request->user();
        $client = Client::findOrFail($auth->client_id);
        if(!$auth){
        return response()->json(['error' => 'Unauthenticated'], 401);
        }
        if(!$auth){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $notifications = DB::table('notifications')
            ->where('notifiable_id', $auth->client_id) // Assuming 'notifiable_id' links notifications to users
            ->select('id', 'notifiable_id','read_at', 'data', 'created_at', 'updated_at')
            ->get();
         $data=[];
    if(count($notifications) >0){
            foreach ($notifications as $notification) {
                $data_decode =json_decode($notification->data,true);
                    $data_push=[
                        "id" => $notification->id,
                        "read_at"=>isset($notification->read_at) ? $notification->read_at : null,
                        "title" => $data_decode['title'] ?? 'No Title',
                        "message" => $data_decode['message'] ?? 'No Message',
                        "created_at" => $notification->created_at,
                        "updated_at" => $notification->updated_at,
                    ];
                array_push($data,$data_push);
            }
            return response()->json([
                'data'=>$data
            ]);
       }else{
        return response()->json(['data'=>'not found Notifications'],200);
       }
    }
    public function auth_unread_notifications(Request $request)
    {
        $auth = $request->user();
        $client = Client::findOrFail($auth->client_id);
        if(!$auth){
        return response()->json(['error' => 'Unauthenticated'], 401);
        }
       $notifications= DB::table('notifications')->where('notifiable_id',$auth->client_id)->whereNull('read_at')->select('id','data','created_at','read_at')->get();
        if (count($notifications)>0) {
            $data=[];
                foreach ($notifications as $notification) {
                    $data_decode =json_decode($notification->data,true);
                    $data_push=[
                        "id" => $notification->id,
                        "title" => $data_decode['title'] ?? 'No Title',
                        "message" => $data_decode['message'] ?? 'No Message',
                        "created_at" => $notification->created_at,
                        "read_at" => $notification->read_at,
                    ];
                    array_push($data,$data_push);
                }
                return response()->json([
                    "data"=>$data
                ],200);
        }else{
            return response()->json([
                "massage"=>'not found Notifications'
            ],200);
        }
    }
    public function auth_read_notifications(Request $request)
    {
        $auth = $request->user();
        $client = Client::findOrFail($auth->client_id);
        if(!$auth){
        return response()->json(['error' => 'Unauthenticated'], 401);
        }
        if($client->unreadNotifications){
            $client->unreadNotifications->markAsRead();
            return response()->json(['data'=> 'All Notification marked as read successfully!'], 200);
        }else {
            return response()->json(['data'=>'not found Notifications'],200);
        }
    }
    public function auth_read_notification($id)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();

        if (isset($notification)) {
            if ($notification->read_at != null) {
                return response()->json(['data' => 'notification has been marked as read already'], 200); // specify a status code
            }

            DB::table('notifications')
                ->where('id', $id)
                ->update(['read_at' => now()]); // update the read_at field

            $notificationfind = DB::table('notifications')->where('id', $id)->first();
            $notificationdata = json_decode($notification->data, true);
            $createdAt = Carbon::parse($notification->created_at);
            $data = [
                "id" => $notificationfind->id,
                "title" => $notificationdata['title'],
                "message" => $notificationdata['message'],
                "created_at" => $createdAt->diffForHumans(),
                "read_at" => $notificationfind->read_at,
            ];

            return response()->json(['data' => $data], 200);
        } else {
            return response()->json(['data' => 'not found Notifications'], 404);
        }
    }
    public function auth_delete_notifications(Request $request)
    {
    $auth = $request->user();
    $client = Client::findOrFail($auth->client_id);
    if(!$auth){
    return response()->json(['error' => 'Unauthenticated'], 401);
    }
    $client->notifications()->delete();
       return response()->json([
        'massage' => "delete notifications"
       ]);
    }
}
