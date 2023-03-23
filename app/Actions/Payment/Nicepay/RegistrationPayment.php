<?php

namespace App\Actions\Payment\Nicepay;

use App\Concerns\Controllers\HasAdminCharge;
use App\Concerns\Nicepay\UsingNicepay;
use App\Events\Payment\Nicepay\Registration\NewQrisRegistration;
use App\Events\Payment\Nicepay\Registration\NewVacctRegistration;
use App\Exceptions\Error;
use App\Http\Response;
use App\Jobs\Payments\Nicepay\Registration;
use App\Models\CodeLogable;
use App\Models\Customers\Customer;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;

class RegistrationPayment
{
    use DispatchesJobs, HasAdminCharge, UsingNicepay;

    /**
     * @var array $attributes
     */
    public array $attributes;

    /**
     * @var Package $package
     */
    protected Package $package;

    /**
     * @var Gateway $gateway
     */
    protected Gateway $gateway;

    /**
     * RegistrationPayment constructor.
     * @param Package $package
     * @param Gateway $gateway
     */
    public function __construct(Package $package, Gateway $gateway)
    {
        if ($package->status !== Package::STATUS_CANCEL) {
            Log::debug('Registration payment for: ', ['package_code' => $package->code->content, 'channel' => $gateway->channel]);
            $this->expDate = Carbon::now()->addDay();
            $customer = $package->customer;

            $billingAddr = 'Jl. alamat';
            $billingCity = 'Jakarta';
            $billingState = 'DKI Jakarta';
            $billingPostCd = '12345';
            $billingEmail = 'noreply@trawlbens.co.id';
            $billingPhone = '6281234567890';
            $addressList = $customer
                ->addresses()
                ->with(['province', 'regency', 'district'])
                ->where('is_default', true);
            if ($addressList->count()) {
                $address = $addressList->first();
                $billingAddr = $address->address ?? '';
                $billingCity = $address->regency ? $address->regency->name : '';
                $billingState = $address->district ? $address->district->name : '';
                $billingPostCd = $address->sub_district ? $address->sub_district->zip_code : '';
            }
            if (! is_null($customer->email)) {
                $billingEmail = $customer->email;
            }
            if (! is_null($package->sender_phone)) {
                $billingEmail = 'tb-'.$this->validPhone($package->sender_phone).'@gmail.com';
                $billingPhone = $this->validPhone($package->sender_phone);
            }

            // todo get total amount child package of multi destination
            $amt = 0.0;
            if ($package->multiDestination()->exists()) {
                $childId = $package->multiDestination()->get()->pluck('child_id')->toArray();
                $totalAmountChild = Package::whereIn('id', $childId)->get()->sum('total_amount');
                $totalAmount = $package->total_amount + $totalAmountChild;
                $amt = ceil($totalAmount + self::adminChargeCalculator($gateway, $totalAmount));
            } else {
                $amt = ceil($package->total_amount + self::adminChargeCalculator($gateway, $package->total_amount));
            }

            $now = date_format(Carbon::now(), 'YmdHis');
            $this->attributes = [
                'timeStamp' => $now,
                'merchantToken' => $this->merchantToken($now, $package->code->content, $amt),
                'amt' => $amt,
                'iMid' => config('nicepay.imid'),
                'currency' => 'IDR',
                'referenceNo' => $package->code->content,
                'goodsNm' => 'Trawlpack Order '.$package->code->content,
                'billingNm' => $customer->name,
                'billingPhone' => $this->validPhone($package->sender_phone),
                'billingEmail' => $billingEmail,
                'billingAddr' => $billingAddr,
                'billingCity' => $billingCity,
                'billingState' => $billingState,
                'billingPostCd' => $billingPostCd,
                'billingCountry' => 'Indonesia',
                'cartData' => json_encode(['items' => $package->item_codes->pluck('content')]),
                'dbProcessUrl' => config('nicepay.db_process_url'),
            ];
            $this->package = $package;
            $this->gateway = $gateway;
        }

        // Unuseable
        // if ($package->status === Package::STATUS_CANCEL) {
        //     $log = [
        //         'package_code' => $package->code->content,
        //         'channel' => $gateway->channel
        //     ];
        //     Log::debug('CancelController: ', $log);
        //     $customer = $package->customer;
        //     $address = $customer->addresses()
        //         ->with(['province', 'regency', 'district'])
        //         ->where('is_default', true)
        //         ->first() ?? null;


        //     $amt = ceil($package->canceled->pickup_price + self::adminChargeCalculator($gateway, $package->canceled->pickup_price));
        //     $now = date_format(Carbon::now(), 'YmdHis');
        //     $this->attributes = [
        //         'timeStamp' => $now,
        //         'merchantToken' => $this->merchantToken($now, $package->code->content, $amt),
        //         'amt' => (string) $amt,
        //         'iMid' => config('nicepay.imid'),
        //         'currency' => 'IDR',
        //         'referenceNo' => $package->code->content,
        //         'goodsNm' => 'Trawlpack Cancel '.$package->code->content,
        //         'billingNm' => $customer->name,
        //         'billingPhone' => $this->validPhone($package->sender_phone),
        //         'billingEmail' => $customer->email,
        //         'billingAddr' => $address->address ?? 'Jl. alamat',
        //         'billingCity' => $address->regency->name ?? 'Jakarta',
        //         'billingState' => $address->district->name ?? 'DKI Jakarta',
        //         'billingPostCd' => $address->sub_district->zip_code ?? '12345',
        //         'billingCountry' => 'Indonesia',
        //         'cartData' => json_encode(['items' => $package->item_codes->pluck('content')]),
        //         'dbProcessUrl' => config('nicepay.db_process_url'),
        //         'mitraCd'=>''
        //     ];
        //     $this->package = $package;
        //     $this->gateway = $gateway;
        // }
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function vaRegistration(): array
    {
        $this->attributes = array_merge($this->attributes, [
            'payMethod' => config('nicepay.payment_method_code.va'),
            'bankCd' => config('nicepay.bank_code.'.$this->gateway->channel),
            'merFixAcctId' => config('nicepay.merchant_fix_account_id'),
            'vacctValidDt' => '',
            'vacctValidTm' => ''
            // 'vacctValidDt' => date_format(Carbon::now(), 'Ymd'),
            // 'vacctValidTm' => date_format(Carbon::now(), 'His')
        ]);
        Log::debug('Registration body va: ', ['body' => $this->attributes]);
        $job = new Registration($this->package, $this->attributes);
        throw_if(! $this->dispatchNow($job), Error::make(Response::RC_FAILED_REGISTRATION_PAYMENT, [$job->response]));
        Log::debug('Nicepay response va: ', ['response' => $job->response]);
        event(new NewVacctRegistration($this->package, $this->gateway, $job->response));
        $this->createWhenAlreadyGeneratePayment($this->package);
        return [
            'total_amount' => $this->attributes['amt'],
            'server_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'expired_time' => date_format(date_create($job->response->vacctValidDt.$job->response->vacctValidTm), 'Y-m-d H:i:s'),
            'bank' => Gateway::convertChannel($this->gateway->channel)['bank'],
            'va_number' => $job->response->vacctNo,
        ];
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function qrisRegistration(): array
    {
        $this->attributes = array_merge($this->attributes, [
            'payMethod' => config('nicepay.payment_method_code.qris'),
            'userIP' => request()->server('SERVER_ADDR'),
            'mitraCd' => config('nicepay.mitra_code'),
            'shopId' => config('nicepay.shop_id'),
        ]);

        Log::debug('Registration body qr: ', ['body' => $this->attributes]);
        $job = new Registration($this->package, $this->attributes);
        throw_if(! $this->dispatchNow($job), Error::make(Response::RC_FAILED_REGISTRATION_PAYMENT, [$job->response]));
        Log::debug('Nicepay response qr: ', ['response' => $job->response]);
        event(new NewQrisRegistration($this->package, $this->gateway, $job->response));
        $this->createWhenAlreadyGeneratePayment($this->package);
        return [
            'total_amount' => $this->attributes['amt'],
            'server_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'expired_time' => date_format(date_create($job->response->paymentExpDt.$job->response->paymentExpTm), 'Y-m-d H:i:s'),
            'qr_content' => $job->response->qrContent,
        ];
    }
    private function createWhenAlreadyGeneratePayment($package)
    {
        if ($package->status === Package::STATUS_CANCEL) {
            $package->status = Package::STATUS_WAITING_FOR_CANCEL_PAYMENT;
            $package->save();
            $pay = Payment::where('payable_id', $package->id)
                ->where('payable_type', Package::class)
                ->first();
            if ($pay) {
                $pay->payment_amount = $package->canceled->pickup_price;
                $pay->total_payment = $package->canceled->pickup_price + $pay->payment_admin_charges;
                $pay->save();
            }
        } else {
            $package->status = Package::STATUS_WAITING_FOR_PAYMENT;
            $package->save();
        }
    }
    private function generateCodeLogable($package)
    {
        $existCodeLogable = CodeLogable::where('code_id', $package->code->id)->first();
        CodeLogable::create([
            'code_id' => $package->code->id,
            'code_logable_type' => Customer::class,
            'code_logable_id' => $existCodeLogable->code_logable_id,
            'type' => $existCodeLogable->type,
            'showable' => json_decode(json_encode(['customer','partner','admin'])),
            'status' => 'accepted_pending',
            'description' => 'Menunggu pembayaran customer',
        ]);
    }
}
