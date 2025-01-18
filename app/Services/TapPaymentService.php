<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TapPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    protected $api_key;

    public function  __construct()
    {
        $this->base_url=env('TAP_BASE_URL');
        $this->api_key=env('TAP_API_KEY');
        $this->header=[
            'Accept'=>'application/json',
            'Content-Type'=>'application/json',
            'Authorization'=>'Bearer '.$this->api_key,
        ];
    }
    public function sendPayment(Request $request)
    {
        $data=$request->all();
        $data['source']=['id'=>'src_all'];
        $data['redirect']=['url'=>$request->getSchemeAndHttpHost().'/api/payment/callback'];

        $response=$this->buildRequest('POST','/v2/charges/',$data);


        if ($response->getData(true)['status']=='200')
        {
            return [
                'success'=>true,
                'url'=>$response->getData(true)['data']['transaction']['url'],
            ];
        }
        return [
            'success'=>false,
            'url'=> route('payment.failed'),
        ];
    }

    public function callBack(Request $request)
    {
        $chargeId= $request->input('tap_id');

        $response_data=$this->buildRequest('GET','/v2/charges/'.$chargeId);

        Storage::put('payment_response.json', json_encode([
            'callback_response'=>$request->all(),
            'response'=>$response_data
        ]));

        if ($response_data['success'] && $response_data['data']['status']=='CAPTURED')
        {
            return true;
        }
        return false;

    }
}
