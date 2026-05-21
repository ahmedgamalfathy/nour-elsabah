<?php

namespace App\Services\Payment;

use Carbon\Carbon;
use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Enums\Order\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Enums\Product\LimitedQuantity;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\Storage;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Interfaces\PaymentGatewayInterface;
use App\Services\Inventory\InventoryService;

class PaypalPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    protected $client_id;
    protected $client_secret;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //test base_url
        $this->base_url =  config('payment.paypal.base_url');
        $this->client_id =  config('payment.paypal.client_id');
        $this->client_secret =  config('payment.paypal.client_secret');
        $this->header=[
            "Accept" => "application/json",
            'Content-Type'=>"application/json",
            'Authorization'=> "Basic " . base64_encode("$this->client_id:$this->client_secret"),
        ];
    }

    public function sendPayment(Request $request): array|JsonResponse
    {
        // Check if PayPal is enabled
        if (!\App\Models\Setting\Setting::get('payment.paypal.enabled', true)) {
            return ApiResponse::error('بوابة الدفع PayPal غير متاحة حالياً', [], HttpStatusCode::SERVICE_UNAVAILABLE);
        }

        $orderId = $request->input('orderId');
        $order = Order::where('id', $orderId)
            ->where('client_id', $request->user()->client_id)
            ->first();
        if(!$order) {
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        if($order->price_after_discount <= 0){
            return ApiResponse::error('Order price must be greater than 0', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        }
        if (!in_array($order->status, [OrderStatus::DRAFT, OrderStatus::IN_CART])) {
            return ApiResponse::error(__('Order must be in draft or in-cart status to process payment'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        }

        try {
            app(InventoryService::class)->assertStockAvailable($order);
        } catch (InsufficientStockException $e) {
            return ApiResponse::error(__('crud.no_available_quantity'), [
                'product' => $e->productName,
                'availableQuantity' => $e->availableQuantity,
            ], HttpStatusCode::UNPROCESSABLE_ENTITY);
        }

        $data = $this->formatData([
            'amount' => $order->price_after_discount ,
            'orderId' => $order->id,
            'client_id' => $order->client_id,
            'host' => $request->getSchemeAndHttpHost(),
        ]);
        $response = $this->buildRequest("POST", "/v2/checkout/orders", $data);
        //handel payment response data and return it
        if ($response->getData(true)['success']){

            $order->update(['status' => OrderStatus::CHECKOUT->value]);

            return ['success' => true,'url'=>$response->getData(true)['data']['links'][1]['href']];
        }
        return ['success' => false,'url'=>route('payment.failed')];

    }

    public function callBack(Request $request):bool
    {

        $token=$request->get('token');
        $response=$this->buildRequest('POST',"/v2/checkout/orders/$token/capture");
        if($response->getData(true)['success']&& $response->getData(true)['data']['status']==='COMPLETED' ){
            $referenceId =  $response->getData(true)['data']['purchase_units'][0]['reference_id'] ?? '';
            // استخراج orderId و clientId من reference_id
            preg_match('/order_(\d+)_client_(\d+)/', $referenceId, $matches);
            $orderId = $matches[1] ?? null;
            $clientId = $matches[2] ?? null;
            $order = Order::where('id', $orderId)
                ->where('client_id', $clientId)
                ->firstOrFail();
            DB::transaction(function () use ($order) {
                $order->status = OrderStatus::CONFIRM;
                $order->save();
            });
            DB::table('payment_callback')->insert([
                'session_id' => $token,
                'name' => $response->getData(true)['data']['payer']['name']['given_name'] ?? null,
                'email' => $response->getData(true)['data']['payer']['email_address'] ?? null,
                'currency' => $response->getData(true)['data']['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? null,
                'status' => $response->getData(true)['data']['status'] ?? null,
                'country' => $response->getData(true)['data']['payer']['address']['country_code'] ?? null,
                'payment_status' => $response->getData(true)['data']['status'] ?? null,
                'amount_total' => $response->getData(true)['data']['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null,
                'client_id' => $order->client_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return true;
        }
        return false;
    }

    public function formatData(array $data): array
    {
        return [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => number_format($data['amount'], 2, '.', '')
                    ],
                    "reference_id" => "order_{$data['orderId']}_client_{$data['client_id']}", // دمج البيانات
                ]
            ],
            "application_context" => [
                "return_url" => $data['host'] . '/api/payment/callback/paypal',
                "cancel_url" => route("payment.failed"),
            ]
        ];
    }

}
