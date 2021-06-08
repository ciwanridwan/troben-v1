<?php

namespace App\Jobs\OneTimePasswords\Nicepay;

use App\Actions\Auth\OtpVerification;
use App\Exceptions\Error;
use App\Http\Response;
use App\Models\OneTimePassword;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMessage
{
    use Dispatchable;

    public const NICEPAY_MERCHANT_KEY = "33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==";
    public OneTimePassword $otp;
    public string $destination_number;
    public string $otpPreMessage  = "JANGAN INFOKAN KODE INI KEPADA SIAPAPUN";
    public string $otpContentMessage = "Kode otentikasi Anda : ";
    public string $otpPostMessage = "Kode berlaku " . OtpVerification::EXPIRED_MINUTE . " mnt";

    /**
     * @param OneTimePassword $otp
     * @param string $destination_number
     */
    public function __construct(OneTimePassword $otp, string $destination_number)
    {
        $this->otp = $otp;
        $this->destination_number = $destination_number;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $sendParams = [
            "timeStamp" => date_format(Carbon::now(), 'YmdHis'),
            "iMid" => "IONPAYTEST",
            "smsType" => "2",
            "reservedFlag" => "0",
            "reservedDt" => "",
            "reservedTm" => "",
            "msgRefno" => "Trawlbens",
            "recipientNo" => $this->destination_number,
            "smsMsg" => $this->createOtpMessage(),
            "dbProcessUrl" => "https://ptsv2.com/t/icha/post",
            "reqNm" => "NICEPAY",
            "sender" => "SENDER1",
        ];
        $sendParams['merchantToken'] = $this->createMerchantToken($sendParams);

        $client = new Client(['base_uri' => 'https://www.nicepay.co.id/nicepay/direct/v2/']);
        $response = $client->post('sms-transmit', [
            'body' => json_encode($sendParams)
        ]);
        throw_if($response->getStatusCode() != 200, Error::make(Response::RC_SMS_GATEWAY_WAS_BROKEN));
    }

    protected function createOtpMessage(): string
    {
        return implode('. ', [$this->otpPreMessage, $this->otpContentMessage . $this->otp->token, $this->otpPostMessage]);
    }
    protected function createMerchantToken($params): string
    {
        $content = $params['timeStamp'] . $params['iMid'] . $params['msgRefno'] . self::NICEPAY_MERCHANT_KEY;
        return hash('sha256', $content);
    }
}
