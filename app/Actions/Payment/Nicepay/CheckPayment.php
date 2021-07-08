<?php

namespace App\Actions\Payment\Nicepay;

use App\Concerns\Nicepay\UsingNicepay;
use App\Exceptions\Error;
use App\Http\Response;
use App\Jobs\Payments\Nicepay\Inquiry;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class CheckPayment
 * @package App\Actions\Payment\Nicepay
 */
class CheckPayment
{
    use DispatchesJobs, UsingNicepay;

    /**
     * @var array $attributes
     */
    public array $attributes;

    /**
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\MorphMany|object|null|Payment $payment
     */
    public $payment;

    /**
     * @var Gateway $gateway
     */
    public Gateway $gateway;

    /**
     * @var object $response
     */
    public object $response;

    /**
     * @var Package $package
     */
    public Package $package;

    /**
     * CheckPayment constructor.
     * @param Package $package
     * @param Gateway $gateway
     * @throws \Throwable
     */
    public function __construct(Package $package, Gateway $gateway)
    {
        $this->package = $package;
        $this->gateway = $gateway;
        $this->expDate = Carbon::now()->addDay();
        $this->payment = $package->payments()
            ->where('status',Payment::STATUS_PENDING)
            ->whereHasMorph('payable',[Package::class])
            ->latest()
            ->first();
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function isValid(): array
    {
        if ($this->payment->expired_at >= Carbon::now()) {
            $result = [
                'total_amount' => (int) $this->response->amt,
                'va_number' => $this->response->vacctNo,
                'bank' => Gateway::convertChannel(array_flip(config('nicepay.bank_code'))[$this->response->bankCd])['bank'],
                'server_time' => Carbon::now()->format('Y-m-d H-i-s'),
                'expired_va' => date_format(date_create($this->response->vacctValidDt . $this->response->vacctValidTm), 'Y-m-d H:i:s'),
            ];
        } else {
            $this->payment->setAttribute('status', Payment::STATUS_EXPIRED)->save();
            $result = (new RegistrationPayment($this->package, $this->gateway))->vaRegistration();
        }

        return $result ?? [];
    }

    /**
     * @return object
     * @throws \Throwable
     */
    public function inquiryPayment(): object
    {
        $amt = $this->payment->payment_amount + $this->payment->payment_admin_charges;
        $now = Carbon::now()->format('YmdHis');

        $this->attributes = [
            'timeStamp' => $now,
            'merchantToken' => $this->merchantToken($now, $this->package->code->content, $amt),
            'referenceNo' => $this->package->code->content,
            'tXid' => $this->payment->payment_ref_id,
            'amt' => $amt,
            'iMid' => config('nicepay.imid'),
        ];
        $job = new Inquiry($this->attributes);
        $this->dispatchNow($job);

        throw_if(! $this->dispatchNow($job), Error::make(Response::RC_FAILED_REGISTRATION_PAYMENT));

        $this->response = $job->response;

        return $this;
    }

    /**
     * Nicepay va registration payment
     *
     * @return array
     * @throws \Throwable
     */
    public function vaRegistration (): array
    {
        if ($this->payment) {
            return self::inquiryPayment()->isValid();
        } else {
            return (new RegistrationPayment($this->package, $this->gateway))->vaRegistration();
        }
    }
}
