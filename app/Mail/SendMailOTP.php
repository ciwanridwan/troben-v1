<?php

namespace App\Mail;

use App\Models\Customers\Customer;
use App\Models\OneTimePassword;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailOTP extends Mailable
{
    use Queueable, SerializesModels;

    public OneTimePassword $otp;
    public Customer $customer;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OneTimePassword $otp, Customer $customer)
    {
        $this->otp = $otp;
        $this->customer = $customer;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.forgotpassword')
            ->subject('Trawlbens - Token Verifikasi Lupa Password')
            ->with([
                'data' => $this->customer,
                'token' => $this->otp
            ]);
    }
}
