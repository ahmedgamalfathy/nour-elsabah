<?php

namespace App\Services\Payment;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BasePaymentService
{
    /**
     * Create a new class instance.
     */
    protected string $base_url;
    protected array $header;
    protected function buildRequest($method, $url, $data = null,$type='json'): \Illuminate\Http\JsonResponse
    {
        try {
            //type ? json || form_params
            $response = Http::withHeaders($this->header)->send($method, $this->base_url . $url, [
                $type => $data
            ]);
            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
            ], $response->status());
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => __('crud.server_error'),
            ], 500);
        }
    }
}
