<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Stats;

use Carbon\Carbon;
use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\Client;
use App\Enums\Order\OrderStatus;
use App\Http\Controllers\Controller;

class StatsController extends Controller
{
    public function __invoke()
    {
        $monthlyTotals = Order::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            FORMAT(SUM(price_after_discount),2) as total
            ')
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');
        $totalRevenue= Order::selectRaw("FORMAT(SUM(price_after_discount) - SUM(total_cost),2)As totalRevenue")->where('status',OrderStatus::DRAFT)->first();

        $orderStats=Order::selectRaw("FORMAT(SUM(price_after_discount),2)As totalPrice, count('*') As totalOrders")->first();
        $draftOrderStats=Order::selectRaw("FORMAT(SUM(price_after_discount),2) As totalPrice, count('*') As totalOrders")->where('status',OrderStatus::DRAFT)->first();
        $totalClients=Client::count();
        return ApiResponse::success([
            'totalOrders'=>[...$orderStats->toArray()],
            'draftOrderStats'=>[...$draftOrderStats->toArray()],
            'totalClients'=> $totalClients,
            'totalRevenue'=>$totalRevenue,
            'monthlyTotals'=>$monthlyTotals

        ]);
    }
}
