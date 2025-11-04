<?php

namespace App\Http\Controllers\Api\V1\Website\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\PaymentGatewayInterface;
use App\Services\Payment\PaypalPaymentService;
use App\Services\Payment\StripePaymentService;

class PaymentController extends Controller
{
    protected PaymentGatewayInterface $paymentGateway;

    public function __construct(PaymentGatewayInterface $paymentGateway)
    {

        $this->paymentGateway = $paymentGateway;
    }


    public function paymentProcess(Request $request)
    {

        return $this->paymentGateway->sendPayment($request);
    }

    // public function callBack(Request $request): \Illuminate\Http\RedirectResponse
    // {
    //     $response = $this->paymentGateway->callBack($request);
    //     if ($response) {
    //         return redirect()->route('payment.success');
    //     }
    //     return redirect()->route('payment.failed');
    // }



    public function paypalCallback(Request $request): \Illuminate\Http\RedirectResponse
    {
        $paypal = app(PaypalPaymentService::class);
        $response = $paypal->callBack($request);

        return $response
            ? redirect()->route('payment.success')
            : redirect()->route('payment.failed');
    }

    public function stripeCallback(Request $request): \Illuminate\Http\RedirectResponse
    {
        $stripe = app(StripePaymentService::class);
        $response = $stripe->callBack($request);

        return $response
            ? redirect()->route('payment.success')
            : redirect()->route('payment.failed');
    }


    public function success()
    {

        return view('payment-success');
    }
    public function failed()
    {

        return view('payment-failed');
    }
}
