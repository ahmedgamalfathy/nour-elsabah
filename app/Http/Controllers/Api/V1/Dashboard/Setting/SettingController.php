<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Setting;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Setting\SettingService;
use App\Http\Resources\Setting\SettingResource;
use App\Enums\ResponseCode\HttpStatusCode;

class SettingController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Get all settings
     */
    public function index(Request $request)
    {
        $group = $request->query('group');
        
        if ($group) {
            $settings = $this->settingService->getSettingsByGroup($group);
        } else {
            $settings = $this->settingService->getAllSettings();
        }

        return ApiResponse::success(
            SettingResource::collection($settings),
            __('crud.retrieved')
        );
    }

    /**
     * Get single setting
     */
    public function show(int $id)
    {
        try {
            $setting = $this->settingService->getSetting($id);
            
            return ApiResponse::success(
                new SettingResource($setting),
                __('crud.retrieved')
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                __('crud.not_found'),
                [],
                HttpStatusCode::NOT_FOUND
            );
        }
    }

    /**
     * Update setting
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'value' => 'required',
            'description' => 'nullable|string',
        ]);

        try {
            $setting = $this->settingService->updateSetting($id, $validated);
            
            return ApiResponse::success(
                new SettingResource($setting),
                __('crud.updated')
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                __('crud.not_found'),
                [],
                HttpStatusCode::NOT_FOUND
            );
        }
    }

    /**
     * Bulk update settings
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required',
        ]);

        $this->settingService->bulkUpdateSettings($validated['settings']);

        return ApiResponse::success(
            [],
            __('crud.updated')
        );
    }

    /**
     * Get available payment gateways
     */
    public function getPaymentGateways()
    {
        $gateways = $this->settingService->getAvailablePaymentGateways();

        return ApiResponse::success([
            'gateways' => $gateways,
        ]);
    }
}
