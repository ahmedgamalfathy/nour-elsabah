<?php

namespace App\Http\Controllers\Api\V1\Website\SubSlider;


use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SubSlider\SubSliderService;
use App\Http\Resources\SubSlider\SubSliderResource;

class SubSliderWebsiteController extends Controller
{
    public $subSliderService;
    public function __construct(SubSliderService $subSliderService)
    {
     $this->subSliderService =$subSliderService;
    }

    public function subSliderWebsite()
    {
        $slider= $this->subSliderService->sliderWebsite();
        if (!$slider) {
            return ApiResponse::error('No active sub-slider found', 404);
        }
        return ApiResponse::success(new SubSliderResource($slider));
    }
}
