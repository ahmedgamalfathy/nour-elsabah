<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\PaymentGatewayInterface;
use App\Services\Payment\PaypalPaymentService;
use App\Services\Payment\StripePaymentService;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // $this->app->bind(PaymentGatewayInterface::class,StripePaymentService::class);
        $this->app->singleton(PaymentGatewayInterface::class, function ($app) {
            $gatewayType = request('gatewayType', 'paypal');
            return match ($gatewayType) {
                'stripe' => $app->make(StripePaymentService::class),
                'paypal' => $app->make(PaypalPaymentService::class),
                default => throw new \Exception("Unsupported gateway type")
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
