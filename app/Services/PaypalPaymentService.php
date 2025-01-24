<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PaypalPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    /**
     * Create a new class instance.
     */
    protected  $client_id;
    protected  $client_secret;


    public function __construct()
    {
        $this->base_url=env('PAYPAL_BASE_URL');
        $this->client_id = env('PAYPAL_CLIENT_ID');
        $this->client_secret = env('PAYPAL_CLIENT_SECRET');

        $this->heade=[
            'Accept'=>'application/json',
            'Content-Type'=>'application/json',
            'Authorization'=>'Basic '.base64_encode("{$this->client_id}:{$this->client_secret}")
        ];
    }
    public function sendPayment(Request $request)
    {
        $data=$this->formData($request);
        $response = $this->buildRequest("POST", "/v2/checkout/orders", $data);

        if ($response->getData(true)['success']){
            return [
                'success'=>true,
                'url'=>$response->getData(true)['data']['links'][1]['href'],
            ];
        }
        return [
            'success'=>false,
            'url'=>route('payment.failed'),
        ];
    }

    public function callBack(Request $request)
    {
        $token = $request->get('token');
        $response = $this->buildRequest("POST", "/v2/checkout/orders/{$token}/capture");
        Storage::put('paypal.json', json_encode([
            'callback_response'=>$request->all(),
            'capture_response'=>$response,
        ]));

        if ($response->getData(true)['success'] && $response->getData(true)['data']['status']=='COMPLETED'){
            return true;
        }
        return false;
    }


    public function formData($request): array
    {
        return  [
            "Intent"=>"CAPTURE",
            "purchase_units"=>[
                [
                    "amount"=>$request->input('amount'),
                ]
            ],
            "payment_source"=>[
                "paypal"=>[
                    "experience_context"=>[
                        'return_url'=>$request->getSchemeAndHttpHost().$request->getRequestUri().'/api/payment/callback',
                        "cancel_url"=>route('payment.failed'),
                    ]
                ]
            ]
        ];
    }

}
