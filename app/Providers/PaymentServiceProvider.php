<?php

namespace App\Providers;

use App\Interfaces\PaymentGatewayInterface;
use App\Services\GeideaPaymentService;
use App\Services\PaymobPaymentService;
use App\Services\PaypalPaymentService;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {

        //if you have multi payment gateways and want to use one of them you shoud send the pramater with data
//        $this->app->singleton(PaymentGatewayInterface::class, function ($app) {
//            $gatewayType = request()->get('gateway_type');
//            return match ($gatewayType) {
//
//
//                default => throw new \Exception("Unsupported gateway type"),
//            };

        $this->app->bind(PaymentGatewayInterface::class,GeideaPaymentService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
