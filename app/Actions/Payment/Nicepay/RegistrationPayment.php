<?php

namespace App\Actions\Payment\Nicepay;

use App\Events\Payment\Nicepay\NewRegistrationVA;
use App\Exceptions\Error;
use App\Http\Response;
use App\Jobs\Payments\Nicepay\VirtualAccount\Registration;
use App\Models\Packages\Package;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class RegistrationPayment
{
    use DispatchesJobs;

    protected const IMID = 'IONPAYTEST';
    protected const MERCHANT_KEY = '33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==';
    protected const URL = 'registration';

    /** @var Carbon $expDate */
    protected Carbon $expDate;

    /** @var Package $package */
    protected Package $package;

    /** @var array $attributes */
    public array $attributes;

    public function __construct(Package $package)
    {
        $this->expDate = Carbon::now()->addDay();
        $customer = $package->customer;
        $address = $customer->addresses()
                ->with(['province','regency','district'])
                ->where('is_default', true)
                ->first() ?? null;

        $this->attributes = [
            'merchantToken' => $this->merchantToken(self::IMID, $package->code->content, $package->total_amount, self::MERCHANT_KEY),
            'timeStamp' => date_format(Carbon::now(), 'YmdHis'),
            'iMid' => self::IMID,
            'currency' => 'IDR',
            'amt' => (string) $package->total_amount,
            'referenceNo' => $package->code->content,
            'goodsNm' => 'Trawlpack Order',
            'billingNm' => $customer->name,
            'billingPhone' => "081294529025",
            'billingEmail' => $customer->email,
            'billingAddr' => $address->address ?? 'dirumah',
            'billingCity' => $address->regency->name ?? 'Jakarta',
            'billingState' => $address->district->name ?? 'DKI Jakarta',
            'billingPostCd' => '12345',
            'billingCountry' => 'Indonesia',
            'cartData' => json_encode(['items' => $package->item_codes->pluck('content')]),
        ];

        $this->package = $package;
    }

    public function vaRegistration(string $bankCd): array
    {
        $sendParams = array_merge($this->attributes,[
            'payMethod' => '02',
            'bankCd' => $bankCd === 'BCA' ? 'CENA' : 'BMRI',
            'merFixAcctId' => '',
            'dbProcessUrl' => env('API_DOMAIN','https://api.trawlbens.co.id').'/payment/nicepay/webhook/va',
            'vacctValidDt' => $this->validDate(),
            'vacctValidTm' => $this->validTime(),
        ]);

        $job = new Registration($this->package, $sendParams);
        $this->dispatchNow($job);

        throw_if(! $job->flag, Error::make(Response::RC_FAILED_REGISTRATION_PAYMENT));

        event(new NewRegistrationVA($this->package, $job->response));

        return [
            'va_number' => $job->response->vacctNo,
            'bank' => $bankCd
        ];
    }

    public function qrisRegistration(): array
    {
        $sendParams = array_merge($this->attributes,[
            'payMethod' => '08',
            'userIp' => request()->server('SERVER_ADDR'),
            'mitraCd' => 'QSHP',
            'shopId' => 'NICEPAY',
            'paymentExpDt' => '',
            'paymentExpTm' => '',
            'dbProcessUrl' => env('API_DOMAIN','https://api.trawlbens.co.id').'/payment/nicepay/webhook/qris',
        ]);

        $job = new Registration($this->package, $sendParams);
        $this->dispatchNow($job);

        throw_if(! $job->flag, Error::make(Response::RC_FAILED_REGISTRATION_PAYMENT));

        return [
            'qr_content' => $job->response->qrContent,
            'qr_url' => $job->response->qrUrl
        ];
    }

    protected function merchantToken(string $iMid, string $refNo, int $amt, string $merchant_key): string
    {
        return hash('sha256',
            date_format(Carbon::now(), 'YmdHis').
            $iMid.
            $refNo.
            $amt.
            $merchant_key
        );
    }

    protected function validDate() {
        return date_format($this->expDate, 'Ymd');
    }

    protected function validTime() {
        return date_format($this->expDate, 'His');
    }
}
