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
            config('nicepay.merchant_key')
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
     * @return false|string
     */
    public function validPhone(string $phonenumber)
    {
        if (str_contains($phonenumber,'+')) {
            return substr($phonenumber,1);
        } else {
            return $phonenumber;
        }
    }
}
