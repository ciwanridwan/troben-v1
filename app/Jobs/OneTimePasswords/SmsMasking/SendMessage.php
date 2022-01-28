<?php

namespace App\Jobs\OneTimePasswords\SmsMasking;

use App\Exceptions\Error;
use App\Http\Response;
use App\Models\OneTimePassword;
use GuzzleHttp\Client;
use Illuminate\Foundation\Bus\Dispatchable;
use libphonenumber\PhoneNumberUtil;

class SendMessage
{
    use Dispatchable;

    public const SMS_MASKING_USERNAME = 'trawlbens2';
    public const SMS_MASKING_PASSWORD = 'v98aut6q';
    public const SMS_MASKING_BASE_URL = 'http://66.96.234.150:8080/web2sms/api/';
//    public const SMS_MASKING_BASE_URL = 'http://send.smsmasking.co.id:8080/web2sms/api/';

    public OneTimePassword $otp;

    public string $destination_number;
    public string $otpPreMessage  = '(Trawlbens.id) Kode verifikasi anda adalah';
    public string $otpContentMessage = '';
    public string $otpPostMessage = 'Awas penipuan! Jangan berikan kode ini ke siapapun! valid ref_id ';
    public string $otpLastMessage = '';


    /**
     * @param OneTimePassword $otp
     * @param string $destination_number
     */
    public function __construct(OneTimePassword $otp, string $destination_number)
    {
        $this->otp = $otp;
        $this->destination_number = PhoneNumberUtil::getInstance()->formatNumberForMobileDialing(
            PhoneNumberUtil::getInstance()->parse($destination_number, 'ID'),
            'ID',
            false
        );
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
        $this->otpLastMessage = $this->generate_string();
        return implode(' ', [$this->otpPreMessage, $this->otpContentMessage.$this->otp->token, $this->otpPostMessage, $this->otpLastMessage]);
    }
    protected function createAuth($params): string
    {
        $content = self::SMS_MASKING_USERNAME.self::SMS_MASKING_PASSWORD.$params['mobile'];
        return hash('md5', $content);
    }

    protected function generate_string($strength = 16): string
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $input_length = strlen($permitted_chars);
        $random_string = '';
        for ($i = 0; $i < $strength; $i++) {
            $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }

        return $random_string;
    }
}
