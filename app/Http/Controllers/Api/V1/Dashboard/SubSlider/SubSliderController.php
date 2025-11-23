<?php

namespace App\Http\Controllers\Api\V1\Dashboard\SubSlider;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Slider\SliderService;
use App\Http\Requests\V1\Slider\SliderStoreRequest;
use App\Http\Requests\V1\Slider\SliderUpdateRequest;
use App\Helpers\ApiResponse;
use App\Http\Resources\SubSlider\AllSubSliderResource;
use App\Http\Resources\SubSlider\SubSliderResource;
use App\Http\Resources\V1\Slider\AllSliderResourceCursor;
use App\Http\Resources\V1\Slider\SliderResource;
use App\Services\SubSlider\SubSliderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
class SubSliderController extends Controller  implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
            new Middleware('permission:all_products', only:['index']),
            new Middleware('permission:create_product', only:['create']),
            new Middleware('permission:edit_product', only:['edit']),
            new Middleware('permission:update_product', only:['update']),
            new Middleware('permission:destroy_product', only:['destroy']),
        ];
    }
    public function __construct(public SubSliderService $sliderService)
    {
    }
    /**
     * Display a listing of sliders
     */
    public function index(Request $request)
    {
        try {
            $data = $this->sliderService->getAll($request);
            return ApiResponse::success(new AllSubSliderResource($data));
        } catch (\Throwable $th) {
            return ApiResponse::error(__('messages.error'), $th->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified slider
     */
    public function show(int $sliderId)
    {
        try {
            $slider = $this->sliderService->getById($sliderId);
            return ApiResponse::success(new SubSliderResource($slider));
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(__('messages.not_found'), [], HttpStatusCode::NOT_FOUND);
        } catch (\Throwable $th) {
            return ApiResponse::error(__('messages.error'), $th->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created slider
     */
    public function store(Request $request)
    {
        try {
          $data=  $request->validate([
                'title' => 'required|string|max:255',
                'images' => 'required|array|min:1',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
                'is_active' => 'sometimes|boolean',
            ]);
            $slider = $this->sliderService->create($data);
            return ApiResponse::success([], __('crud.created'));
        } catch (ValidationException $e) {
            return ApiResponse::error(__('messages.validation_failed'), $e->errors(), HttpStatusCode::BAD_REQUEST);
        } catch (\Throwable $th) {
            return ApiResponse::error(__('messages.error'), $th->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified slider
     */
    public function update(int $sliderId, Request $request)
    {
        try {
            $data =$request->validate([
            'title' => 'sometimes|string|max:255',
            'images' => 'sometimes|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'is_active' => 'sometimes|boolean',
            ]);
            $slider = $this->sliderService->update($sliderId, $data);
            return ApiResponse::success([], __('crud.updated'));
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(__('messages.not_found'), [], HttpStatusCode::NOT_FOUND);
        } catch (\Throwable $th) {
            return ApiResponse::error(__('messages.error'), $th->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified slider
     */
    public function destroy(int $sliderId)
    {
        try {
            $this->sliderService->delete($sliderId);
            return ApiResponse::success([], __('curd.deleted'));
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(__('curd.not_found'), [], HttpStatusCode::NOT_FOUND);
        } catch (\Throwable $th) {
            return ApiResponse::error(__('curd.error'), $th->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle slider active status
     */
    public function toggleActive(int $sliderId)
    {
        try {
            $slider = $this->sliderService->toggleActive($sliderId);
            return ApiResponse::success([], __('messages.updated'));
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(__('messages.not_found'), [], HttpStatusCode::NOT_FOUND);
        } catch (\Throwable $th) {
            return ApiResponse::error(__('messages.error'), $th->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
