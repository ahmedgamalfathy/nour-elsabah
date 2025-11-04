<?php

namespace App\Http\Controllers\Api\V1\Website\Slider;

use App\Enums\IsActive;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Utils\PaginateCollection;
use App\Http\Controllers\Controller;
use App\Services\Slider\SliderService;
use App\Http\Resources\Slider\SliderResource;


class SliderController extends Controller
{
    public $sliderService;
    public function __construct(SliderService $sliderService)
    {
     $this->sliderService =$sliderService;
    }

    public function index(Request $request)
    {
        $sliders= $this->sliderService->allSlider();
        return  ApiResponse::success( SliderResource::collection($sliders));
    }
}
