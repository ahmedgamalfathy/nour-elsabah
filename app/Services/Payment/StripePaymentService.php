<?php

namespace App\Services\Payment;

use Carbon\Carbon;
use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Client\Client;
use App\Enums\Order\OrderStatus;
use App\Models\Client\ClientUser;
use App\Models\Client\ClientEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMessageSuccessPayment;
use Illuminate\Support\Facades\Storage;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Interfaces\PaymentGatewayInterface;
use App\Services\Payment\BasePaymentService;
use App\Enums\Product\LimitedQuantity;

class StripePaymentService extends BasePaymentService implements PaymentGatewayInterface
{

    protected mixed $api_key;
    public function __construct()
    {
        $this->base_url = config('payment.stripe.base_url');
        $this->api_key =  config('payment.stripe.secret');
        $this->header = [
            'Accept' => 'application/json',
            'Content-Type' =>'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer ' . $this->api_key,
        ];

    }

    public function sendPayment(Request $request)
    {
        $orderId = $request->input('orderId');
        $order = Order::find($orderId);
        if(!$order){
            return ApiResponse::error(__('crud.not_found'),[],HttpStatusCode::NOT_FOUND);
        }
        if($order->price_after_discount <= 0){
            return ApiResponse::error('Order price must be greater than 0', [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        }
        if (!in_array($order->status, [OrderStatus::DRAFT, OrderStatus::IN_CART])) {
            return ApiResponse::error(__('Order must be in draft or in-cart status to process payment'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        }
        foreach ($order->items as $item) {
            if ($item->is_limited_quantity == LimitedQuantity::LIMITED && $item->product->quantity < $item->qty) {
                $avilableQuantity[] = [
                    'productId' => $item->product->id,
                    'quantity' => $item->product->quantity,
                    'name' => $item->product->name
                ];
                return  $avilableQuantity;
            }
        }
        $data = $this->formatData([
            "amount" => $order->price_after_discount * 100,
            "currency" => "USD",
            "client_id" => $order->client_id,
            "orderId"=> $order->id,
            "host" => $request->getSchemeAndHttpHost(),
        ]);

        $response =$this->buildRequest('POST', '/v1/checkout/sessions', $data, 'form_params');
        if($response->getData(true)['success']) {
            return ['success' => true, 'url' => $response->getData(true)['data']['url']];
        }
        return ['success' => false,'url'=>route('payment.failed')];
    }

    public function callBack(Request $request): bool
    {
        $session_id = $request->get('session_id');
        $response=$this->buildRequest('GET','/v1/checkout/sessions/'.$session_id);
         if($response->getData(true)['success']&& $response->getData(true)['data']['payment_status']==='paid') {

            $order = Order::find($response->getData(true)['data']['metadata']['orderId']);
            $order->status = OrderStatus::CONFIRM->value;
            $order->save();
            foreach ($order->items as $item) {
                $item->product->decrement('quantity', $item->qty);
            }
            DB::table('payment_callback')->insert([
                //session_id ,name ,email, currency ,status ,country ,payment_status,amount_total
                    'session_id'=>$request->get('session_id'),
                    'name'=>$response->getData(true)['data']['customer_details']['name']??null,
                    'email'=>$response->getData(true)['data']['customer_details']['email']??null,
                    'currency'=>$response->getData(true)['data']['currency']??null,
                    'status'=>$response->getData(true)['data']['status']??null,
                    'country'=>$response->getData(true)['data']['customer_details']['address']['country']??null,
                    'payment_status'=>$response->getData(true)['data']['payment_status']??null,
                    'amount_total'=>$response->getData(true)['data']['amount_total']??null,
                    'client_id'=>$response->getData(true)['data']['metadata']['client_id']??null,
                    'created_at'=>Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at'=>Carbon::now()->format('Y-m-d H:i:s'),
            ]);
            return true;
         }
        return false;

    }

    public function formatData(array $data): array
    {
        return [
            "success_url" =>$data['host'].'/api/payment/callback/stripe?session_id={CHECKOUT_SESSION_ID}',
            "line_items" => [
                [
                    "price_data"=>[
                        "unit_amount" => $data['amount'],
                        "currency" => $data['currency'],
                        "product_data" => [
                            "name" => "order",
                            "description" => "description of ORDER"
                        ],
                    ],
                    "quantity" => 1,
                ],
            ],
            "mode" => "payment",
            "metadata" => [
                "client_id" => $data['client_id'],
                "orderId" => $data['orderId']
            ]
        ];
    }

}
