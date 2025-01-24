<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MoyasarPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    /**
     * Create a new class instance.
     */
    protected  $api_secret;

    public function __construct()
    {
        $this->base_url=env('MOYASAR_BASE_URL');
        $this->api_secret = env('MOYASAR_API_SECRET');
        $this->header=[
            'accept'=>'application/json',
            'Content-Type:application/json',
            'Authorization: Basic '.base64_encode($this->api_secret),
        ];
    }

    public function sendPayment(Request $request)
    {
        $data = $request->all();
        $data['success_url'] =$request->getSchemeAndHttpHost() . '/api/payment/callback';
        $response = $this->buildRequest('POST', '/v1/invoices', $data);
        //handel payment response data and return it
        if($response->getData(true)['success']){

            return['success'=>true,'url'=>$response->getData(true)['data']['url']];
        }
    }

    public function callBack(Request $request)
    {
        $response_status=$request->get('status');
        Storage::put('moyasar_response.json', json_encode($request->all()));
        if(isset($response_status)&&$response_status==='paid') {
            //save order data and return true
            return true;
        }
        return false;
    }
}
