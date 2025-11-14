<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Areas;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\Area\AreaService;
use App\Http\Controllers\Controller;

class AreaController extends Controller
{
    public function __construct(public AreaService $areaService)
    {
       $this->areaService = $areaService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $areas = $this->areaService->allArea();
        return ApiResponse::success($areas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:areas,name|max:255',
            'areaId' => 'nullable|integer|exists:areas,id',
            'price' => 'required|numeric|min:1',
        ]);

        $area = $this->areaService->createArea($data);
        return ApiResponse::success([], 'Area created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $area = $this->areaService->editArea($id);
            return ApiResponse::success($area);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Area not found', [], HttpStatusCode::NOT_FOUND);
        }catch (\Exception $e) {
            return ApiResponse::error('Server error', $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $data = $request->validate([
            'name' => ['required','string','max:255',Rule::unique('areas')->ignore($id)],
            'areaId' => 'nullable|integer|exists:areas,id',
            'price' => 'required|numeric|min:1',
        ]);

        $area = $this->areaService->updateArea($id, $data);
        return ApiResponse::success([],"Area updated successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->areaService->deleteArea($id);
            return ApiResponse::success([], 'Area deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Area not found', [], HttpStatusCode::NOT_FOUND);
        } catch (\Exception $e) {
            return ApiResponse::error('Server error', $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
