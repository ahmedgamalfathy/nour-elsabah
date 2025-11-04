<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Category;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Category\AllCategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Services\Category\CategoryService;
use App\Utils\PaginateCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use OpenApi\Annotations as OA;


class CategoryController extends Controller implements HasMiddleware
{
    protected $categoryService;
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
            new Middleware('permission:all_categories', only:['index']),
            new Middleware('permission:create_category', only:['create']),
            new Middleware('permission:edit_category', only:['edit']),
            new Middleware('permission:update_category', only:['update']),
            new Middleware('permission:destroy_category', only:['destroy']),
        ];
    }


    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get list of users",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="pageSize",
     *         in="query",
     *         required=false,
     *         description="Number of users per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="users", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="userId", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="username", type="string", example="johndoe"),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="avatar", type="string", example="https://example.com/avatar.jpg"),
     *                         @OA\Property(property="roleName", type="string", example="Admin"),
     *                         @OA\Property(property="charityName", type="string", example="Charity Org")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="count", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total_pages", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */


    public function index(Request $request)
    {
        $categories = $this->categoryService->allCategories();

        return ApiResponse::success(new AllCategoryCollection(PaginateCollection::paginate($categories, $request->pageSize?$request->pageSize:10)));

    }

    /**
     * Show the form for creating a new resource.
     */

    public function store(CreateCategoryRequest $createCategoryRequest)
    {
        try {
            DB::beginTransaction();


            $this->categoryService->createCategory($createCategoryRequest->validated());

            DB::commit();

            return ApiResponse::success([], __('crud.created'));


        } catch (\Exception $e) {
            DB::rollBack();
           return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }


    }

    /**
     * Show the form for editing the specified resource.
     */

    public function show(int $id)
    {
        try {
        $category  =  $this->categoryService->editCategory($id);
        return ApiResponse::success(new CategoryResource($category));
        }catch(ModelNotFoundException $e){
            return  ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
          return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id,UpdateCategoryRequest $updateCategoryRequest)
    {

        try {
            DB::beginTransaction();

            $this->categoryService->updateCategory($id,$updateCategoryRequest->validated());
            DB::commit();
            return ApiResponse::success([], __('crud.updated'));

        } catch (\Exception $e) {
            DB::rollBack();
          return  ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {

        try {
            DB::beginTransaction();
            $this->categoryService->deleteCategory($id);
            DB::commit();
            return ApiResponse::success([], __('crud.deleted'));
        }catch(ModelNotFoundException $e){
            return  ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
          return ApiResponse::error(__('crud.server_error'),[],HttpStatusCode::INTERNAL_SERVER_ERROR);
        }


    }

}
