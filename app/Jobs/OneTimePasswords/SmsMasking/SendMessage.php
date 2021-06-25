<?php

namespace App\Jobs\OneTimePasswords\SmsMasking;

use App\Exceptions\Error;
use App\Http\Response;
use App\Models\OneTimePassword;
use GuzzleHttp\Client;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMessage
{
    use Dispatchable;

    public const SMS_MASKING_USERNAME = 'trawlbens';
    public const SMS_MASKING_PASSWORD = 'u0z2dnfq';
    public const SMS_MASKING_BASE_URL = 'http://send.smsmasking.co.id:8080/web2sms/api/';

    public OneTimePassword $otp;

    public string $destination_number;
    public string $otpPreMessage  = 'Segera masukkan angka berikut ini';
    public string $otpContentMessage = '';
    public string $otpPostMessage = 'ke dalam Applikasi Trawlbens ';

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
            'username' => self::SMS_MASKING_USERNAME,
            'mobile' => $this->destination_number,
            'message' => $this->createOtpMessage()
        ];
        $sendParams['auth'] = $this->createAuth($sendParams);
        $client = new Client(['base_uri' => self::SMS_MASKING_BASE_URL]);
        $response = $client->get('sendSMS.aspx', [
            'query' => $sendParams
        ]);

        throw_if($response->getStatusCode() != 200, Error::make(Response::RC_SMS_GATEWAY_WAS_BROKEN));
    }

    protected function createOtpMessage(): string
    {
        return implode(' ', [$this->otpPreMessage, $this->otpContentMessage.$this->otp->token, $this->otpPostMessage]);
    }
    protected function createAuth($params): string
    {
        $content = self::SMS_MASKING_USERNAME.self::SMS_MASKING_PASSWORD.$params['mobile'];
        return hash('md5', $content);
    }
}
