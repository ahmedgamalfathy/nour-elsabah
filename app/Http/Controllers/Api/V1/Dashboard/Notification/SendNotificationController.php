<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Client\Client;
use App\Notifications\SendNotificationToClient;
use Illuminate\Support\Facades\Notification;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
class SendNotificationController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
        ]);
       Notification::send(Client::all(), new SendNotificationToClient($data));
        return ApiResponse::success([], __('crud.created'));
    }
}
