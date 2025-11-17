<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Coupon;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Coupon\Coupon;
use App\Utils\PaginateCollection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Resources\Coupon\AllCouponResource;
use App\Http\Resources\Coupon\AllCouponCollection;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::withCount('usage')
            ->latest()
            ->get();

        return ApiResponse::success(new AllCouponCollection(            PaginateCollection::paginate($coupons, $request->pageSize?$request->pageSize:10)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:50',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);
        try {
            DB::beginTransaction();
            Coupon::create($data);
            DB::commit();
            return ApiResponse::success([],__('crud.created'));
        } catch (\Throwable $th) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'),$th->getMessage(),HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        $coupon = Coupon::withCount('usage')->find($id);
        if (!$coupon) {
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        return ApiResponse::success(new AllCouponResource($coupon));
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);
       if (!$coupon) {
        return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
       }
        $data = $request->validate([
            'code' => 'sometimes|string|max:50|unique:coupons,code,' . $id,
            'type' => 'sometimes|in:percentage,fixed',
            'value' => 'sometimes|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);

        $coupon->update($data);
        return ApiResponse::success([], __('crud.updated'));
    }

    public function destroy($id)
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        $coupon->delete();

        return ApiResponse::success([],__('crud.deleted'));
    }

    public function toggleStatus($id)
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        $coupon->update(['is_active' => !$coupon->is_active]);

        return ApiResponse::success([], 'تم تغيير حالة الكوبون');
    }
}
