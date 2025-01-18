<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymobPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    protected $api_key;
    protected $integrations_id;

    public function __construct()
    {
        $this->base_url=env('BAYMOB_BASE_URL');
        $this->api_key=env('BAYMOB_API_KEY');
        $this->header=[
            'Accept'=>'application/json',
            'Content-Type'=>'application/json',
        ];
        $this->integrations_id=[4920183,4920182];
    }

    protected function generateToken()
    {
        $response=$this->buildRequest('POST','/api/auth/tokens',['api_key'=>$this->api_key]);
        return $response->getData(true)['data']['token'];
    }
    public function sendPayment(Request $request)
    {
        $this->header['Authorization']='Bearer '.$this->generateToken();
        $data=$request->all();
        $data['api_source']='INVOICE';
        $data['integrations']=$this->integrations_id;

        $response=$this->buildRequest('POST','/api/ecommerce/orders',$data);


        if ($response->getData(true)['status']=='201')
        {
            return [
                'success'=>true,
                'url'=>$response->getData(true)['data']['url'],
            ];
        }
        return [
            'success'=>false,
            'url'=> route('payment.failed'),
        ];
    }

    public function callBack(Request $request)
    {
        $response = $request->all();
        Storage::put('payment_response.json', json_encode($request->all()));

        if (isset($response['success']) && $response['success'] === true)
        {
            return true;
        }
        return false;
    }
}
