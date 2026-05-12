<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Unit;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\CreateUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Http\Resources\Unit\UnitResource;
use App\Services\Unit\UnitService;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller implements HasMiddleware
{
    public function __construct(private readonly UnitService $unitService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    public function index()
    {
        $units = $this->unitService->allUnits();

        return ApiResponse::success(UnitResource::collection($units));
    }

    public function store(CreateUnitRequest $request)
    {
        try {
            DB::beginTransaction();
            $unit = $this->unitService->createUnit($request->validated());
            DB::commit();

            return ApiResponse::success(new UnitResource($unit), __('crud.created'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id)
    {
        try {
            $unit = $this->unitService->findUnit($id);

            return ApiResponse::success(new UnitResource($unit));
        } catch (ModelNotFoundException) {
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        }
    }

    public function update(int $id, UpdateUnitRequest $request)
    {
        try {
            DB::beginTransaction();
            $unit = $this->unitService->updateUnit($id, $request->validated());
            DB::commit();

            return ApiResponse::success(new UnitResource($unit), __('crud.updated'));
        } catch (ModelNotFoundException) {
            DB::rollBack();
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->unitService->deleteUnit($id);

            return ApiResponse::success([], __('crud.deleted'));
        } catch (ModelNotFoundException) {
            return ApiResponse::error(__('crud.not_found'), [], HttpStatusCode::NOT_FOUND);
        } catch (\Throwable $e) {
            return ApiResponse::error(__('crud.server_error'), $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
