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

    public $subject;
    public $purpose;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OneTimePassword $otp, Customer $customer)
    {
        $this->otp = $otp;
        $this->customer = $customer;
        $this->subject = 'Trawlbens - Token OTP Verifikasi Data Akun';
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.forgotpassword')
            ->subject($this->subject)
            ->with([
                'data' => $this->customer,
                'token' => $this->otp,
            ]);
    }
}
