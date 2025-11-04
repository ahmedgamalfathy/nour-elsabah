<?php

namespace App\Http\Controllers\Api\V1\Website\StaticPage;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StaticPage\StaticPage;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\StaticPageType\StaticPageType;
use App\Http\Resources\StaticPage\StaticPageResource;

class StaticPageWebController extends Controller
{
    public function show(string $slug)
    {
        if (!StaticPageType::isValid($slug)) {
          return   ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        $page = StaticPage::where('slug', $slug)->firstOrFail();
         return  ApiResponse::success(new StaticPageResource($page));
    }
}
