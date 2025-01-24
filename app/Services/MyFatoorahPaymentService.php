<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MyFatoorahPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    /**
     * Create a new class instance.
     */
    protected  $api_key;
    public function __construct()
    {
        $this->base_url=env('MYFATORAH_BASE_URL');
        $this->api_key=env('MYFATORAH_API_KEY');
        $this->header=[
            'Accept'=>'application/json',
            'Content-Type'=>'application/json',
            'Authorization'=>'Bearer '.$this->api_key,
        ];
    }

    public function sendPayment(Request $request)
    {
        $data=$request->all();
        $data['NotificationOption']='LNK';
        $data['Language']='en';
        $data['CallBackUrl']=$request->getSchemeAndHttpHost().'/api/payment/callback';

        $response=$this->buildRequest('POST','/v2/SendPayment',$data);
        if ($response->getData(true)['success']) {
            return [
                'success'=>true ,
                'url'=>$response->getData(true)['data']['Data']['InvoiceURL']];
        }
        return ['success'=>false ,'url'=>route('payment.failed')];
    }

    public function callback(Request $request)
    {
        $data=[
            'KeyTypes'=>'paymentId',
            'key'=>$request->input('paymentId'),
        ];

        $response=$this->buildRequest('POST','/v2/getPaymentStatus',$data);
        $response_data=$response->getData(true);
        Storage::put('myfatoorah_response.json',json_encode([
            'myfatoorah_callback_response'=>$request->all(),
            'myfatoorah_response_status'=>$response_data,
        ]));
        if ($response_data['data']['Data']['InvoiceStatus'] == 'Paid'){
            return true;
        }
        return false;
    }

}
