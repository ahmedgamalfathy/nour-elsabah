<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Slider;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Utils\PaginateCollection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\Slider\SliderService;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Http\Resources\Slider\SliderResource;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Requests\Slider\CreateSliderRequst;
use App\Http\Requests\Slider\UpdateSliderRequst;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Resources\Slider\AllSliderCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SliderController extends Controller
{
    public $sliderService;
    public function __construct(SliderService $sliderService)
    {
     $this->sliderService =$sliderService;
    }
    // public static function middleware(): array
    // {
    //     return [
    //         new Middleware('auth:api'),
    //         new Middleware('permission:all_categories', only:['index']),
    //         new Middleware('permission:create_category', only:['create']),
    //         new Middleware('permission:edit_category', only:['edit']),
    //         new Middleware('permission:update_category', only:['update']),
    //         new Middleware('permission:destroy_category', only:['destroy']),
    //     ];
    // }


   public function index(Request $request)
    {
       $sliders= $this->sliderService->allSlider();
       return  ApiResponse::success(new AllSliderCollection(PaginateCollection::paginate($sliders, $request->pageSize?$request->pageSize:10)));
    }
    public function show(int $id)
    {
        try {
        $slider= $this->sliderService->editSlider($id);
        return ApiResponse::success(new SliderResource($slider));
        }catch (ModelNotFoundException $th) {
        return  ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }catch (\Throwable $th) {
        return  ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }
    public function store(CreateSliderRequst $createSliderRequst)
    {
        DB::beginTransaction();
        try {
        $this->sliderService->createSlider($createSliderRequst->validated());
        DB::commit();
        return ApiResponse::success([], __('crud.created'));
        } catch (\Throwable $th) {
        DB::rollBack();
        return  ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }


    }
    public function update(int $id,UpdateSliderRequst $updateSliderRequst)
    {

        try{
        $this->sliderService->updateSlider($id,$updateSliderRequst->validated());
        return ApiResponse::success([], __('crud.updated'));
        } catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
    public function destroy(int $id) {
        try {
        $this->sliderService->deleteSlider($id);
        return ApiResponse::success([], __('crud.updated'));
        }catch (ModelNotFoundException $th) {
        return  ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
    public function changeStatus(int $id,Request $request)
    {
        try{
        $this->sliderService->changeSliderStatus($id,$request->isActive);
        return ApiResponse::success([], __('crud.updated'));
        }catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
