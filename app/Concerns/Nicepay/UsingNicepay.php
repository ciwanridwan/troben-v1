<?php

namespace App\Concerns\Nicepay;

use Carbon\Carbon;

trait UsingNicepay
{
    /**
     * @var Carbon $expDate
     */
    protected Carbon $expDate;

    /**
     * @param string $timestamp
     * @param string $refNo
     * @param int $amt
     * @return string
     */
    public function merchantToken(string $timestamp, string $refNo, int $amt): string
    {
        return hash(
            'sha256',
            $timestamp.
            config('nicepay.imid').
            $refNo.
            $amt.
            config('nicepay.merchant_key'),
        );
    }

    /**
     * @return false|string
     */
    public function validDate()
    {
        return date_format($this->expDate, 'Ymd');
    }

    /**
     * @return false|string
     */
    public function validTime()
    {
        return date_format($this->expDate, 'His');
    }

    /**
     * @param string $phonenumber
     * @return array|string|string[]
     */
    public function validPhone(string $phonenumber)
    {
        return substr(str_replace(['+','-',' '], '', $this->validatePhoneCountryCode($phonenumber)), 0, 12);
    }

    /**
     * Change +62 to 0.
     * @param $phonenumber
     * @return array|string|string[]
     */
    protected function validatePhoneCountryCode($phonenumber)
    {
        return str_replace(['+62'], '0', $phonenumber);
    }
}
