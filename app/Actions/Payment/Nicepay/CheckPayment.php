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
 * Class CheckPayment.
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
            ->where('status', Payment::STATUS_PENDING)
            ->whereHasMorph('payable', [Package::class])
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
            $expiredAt = $this->response->payMethod === config('nicepay.payment_method_code.va')
                ? $this->response->vacctValidDt . $this->response->vacctValidTm
                : $this->response->paymentExpDt . $this->response->paymentExpTm;

            $result = [
                'total_amount' => (int) $this->response->amt,
                'server_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'expired_time' => date_format(date_create($expiredAt), 'Y-m-d H:i:s'),
            ];

            if ($this->response->payMethod === config('nicepay.payment_method_code.va')) {
                $result['bank'] = Gateway::convertChannel(array_flip(config('nicepay.bank_code'))[$this->response->bankCd])['bank'];
                $result['va_number'] = $this->response->vacctNo;
            }

            if ($this->response->payMethod === config('nicepay.payment_method_code.qris')) {
                $result['qr_content'] = $this->payment->payment_content;
            }
        } else {
            $this->payment->setAttribute('status', Payment::STATUS_EXPIRED)->save();

            if ($this->response->payMethod === config('nicepay.payment_method_code.va')) {
                $result = (new RegistrationPayment($this->package, $this->gateway))->vaRegistration();
            }

            if ($this->response->payMethod === config('nicepay.payment_method_code.qris')) {
                $result = (new RegistrationPayment($this->package, $this->gateway))->qrisRegistration();
            }
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
     * Nicepay va registration payment.
     *
     * @return array
     * @throws \Throwable
     */
    public function vaRegistration(): array
    {
        if ($this->payment) {
            return self::inquiryPayment()->isValid();
        } else {
            return (new RegistrationPayment($this->package, $this->gateway))->vaRegistration();
        }
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function qrisRegistration(): array
    {
        if ($this->payment) {
            return self::inquiryPayment()->isValid();
        } else {
            return  (new RegistrationPayment($this->package, $this->gateway))->qrisRegistration();
        }
    }
}
