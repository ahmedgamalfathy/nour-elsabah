<?php

namespace App\Http\Controllers\Api\V1\Dashboard\StaticPage;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StaticPage\StaticPage;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\StaticPageType\StaticPageType;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Resources\StaticPage\StaticPageResource;

class StaticPageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }
    public function show(string $slug)
    {
        if (!StaticPageType::isValid($slug)) {
          return   ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        $page = StaticPage::where('slug', $slug)->firstOrFail();
         return  ApiResponse::success(new StaticPageResource($page));
    }
    public function update(Request $request, string $slug)
    {
        if (!StaticPageType::isValid($slug)) {
        return   ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        $page = StaticPage::where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);
        $page->update($data);
        return  ApiResponse::success(__("crud.updated"));
    }
}
