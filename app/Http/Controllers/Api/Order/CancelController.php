<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Payment\Nicepay\CheckPayment;
use App\Events\Packages\PackageCanceledByCustomer;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Package\PackageResource;
use App\Http\Response;
use App\Jobs\Packages\SelectCancelPickupMethod;
use App\Models\CancelOrder;
use App\Models\Packages\Package;
use App\Models\Packages\Price;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Concerns\Nicepay\UsingNicepay;
use App\Http\Resources\Payment\Nicepay\RegistrationResource;
use App\Models\CodeLogable;
use App\Models\Customers\Customer;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use App\Services\Tracker\CancelOrderTracker;

class CancelController extends Controller
{
    use UsingNicepay;
    public array $attributes;
    protected Gateway $gateway;

    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException|\Throwable
     */
    // public function cancel(Package $package): JsonResponse
    // {
    //     $this->authorize('update', $package);

    //     event(new PackageCanceledByCustomer($package));

    //     return $this->jsonSuccess(PackageResource::make($package->fresh()));
    // }

    public function cancel(Package $package, Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', Rule::in(CancelOrder::getCancelTypes())]
        ]);
        $this->authorize('update', $package);
        $price = Price::where('package_id', $package->id)
            ->where(['description'=>'pickup','type' => 'delivery'])
            ->first();
        if ($request->type == CancelOrder::TYPE_RETURN_TO_SENDER_ADDRESS) {
            $pickupPrice = $price->amount * 2;
        } else {
            $pickupPrice = $price->amount;
        }
        event(new PackageCanceledByCustomer($package));
        $check = CancelOrder::where('package_id', $package->id)->first();
        $existCodeLogable = CodeLogable::where('code_id', $package->code->id)
            ->whereIn('status', [CancelOrder::TYPE_SENDER_TO_WAREHOUSE,CancelOrder::TYPE_RETURN_TO_SENDER_ADDRESS])
            ->first();
        (is_null($check) and is_null($existCodeLogable)) ?
            $exist = false :
            $exist = true;
        $arr = [
            'cancel_order' => [
                'get_class' => new CancelOrder(),
                'package' => $package
            ],
            'codelogable' => [
                'code_id' => $package->code->id,
                'code_logable_type' => Customer::class,
                'code_logable_id' => $request->user()->id,
                'type' => 'info',
                'showable' => json_decode(json_encode(['admin','customer'])),
            ],
            'pickup_price' => $pickupPrice,
            'package_status' => $request->input('type'),
            'status_description' => 'Pesanan sedang dalam proses pengembalian'
        ];
        CancelOrderTracker::cancelService($exist, $existCodeLogable, $check, $arr);
        return (new Response(Response::RC_SUCCESS))->json();
    }

    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException|\Throwable
     */
    public function method(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);
        $job = new SelectCancelPickupMethod($package, $request->all());
        $this->dispatch($job);
        return $this->jsonSuccess(PackageResource::make($job->package));
    }

    public function cancelBefore(Package $package): JsonResponse
    {
        if ($package->status == 'pending' && $package->payment_status == 'draft' || $package->status == 'created') {
            $this->authorize('update', $package);
            event(new PackageCanceledByCustomer($package, ''));
            return $this->jsonSuccess(PackageResource::make($package->fresh()));
        } else {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        }
    }

    public function cancelOrder(Request $req, Package $package)
    {
        $package->status = 'cancel';
        $package->save();
        $existCodeLogable = CodeLogable::where('code_id', $package->code->id)->first();
        $arr = [
            'package_status' => Package::STATUS_CANCEL,
            'status_description' => 'Pesanan dalam proses pembatalan'
        ];
        CancelOrderTracker::CancelOrder($existCodeLogable, $arr);

        return (new Response(Response::RC_SUCCESS))->json();
    }

    public function payForCancelDummy(Package $package, Gateway $gateway)
    {
        if ($package->status !== Package::STATUS_CANCEL) {
            $msg = [
                'message'=> 'status '.$package->status
            ];
            return (new Response(Response::RC_ACCEPTED, $msg))->json();
        }
        $this->gateway = $gateway;
        $currentTime = Carbon::now();
        $expiredTime = $currentTime->addDays(7);
        $firstNum = 9999;
        $vaNumber = rand(100, 1000000000);
        $amt = $package->canceled->pickup_price;
        $data = [
            'total_amount' => $amt,
            'server_time' => $currentTime->format('Y-m-d H:i:s'),
            'expired_time' => $expiredTime->format('Y-m-d H:i:s'),
            'bank' => Gateway::convertChannel($this->gateway->channel)['bank'],
            'va_number' => $firstNum.$vaNumber,
        ];
        $this->createWhenAlreadyGeneratePayment($package);
        return (new Response(Response::RC_SUCCESS, $data))->json();
    }

    public function payForCancel(Package $package, Gateway $gateway)
    {
        if ($package->status !== Package::STATUS_CANCEL) {
            $msg = [
                'message'=> 'status '.$package->status
            ];
            return (new Response(Response::RC_ACCEPTED, $msg))->json();
        }

        switch(Gateway::convertChannel($gateway->channel)['type']):
            case 'va':
                $resource = (new CheckPayment($package, $gateway))->vaRegistration();
                break;
            case 'qris':
                $resource = (new CheckPayment($package, $gateway))->qrisRegistration();
                break;
        endswitch;
        return $this->jsonSuccess(new RegistrationResource($resource ?? []));
    }

    public function checkCancelPayment(Package $package)
    {
        $checkPayment = Payment::with('gateway')->where('payable_id', $package->id)
            ->where('payable_type', Package::class)->first();
        if(! is_null($checkPayment)) {
            $content = [
                'has_generate_payment' => true,
                'data_payment' => array_merge($checkPayment->toArray(), ['server_time' => Carbon::now()->format('Y-m-d H:i:s')]),
            ];
            return (new Response(Response::RC_ACCEPTED, $content))->json();
        } else {
            $content = [
                'has_generate_payment' => false,
                'data_payment' => $checkPayment,
            ];
            return (new Response(Response::RC_ACCEPTED, $content))->json();
        }
    }

    public function paymentCash(Request $req, Package $package)
    {
        if(is_null($package->canceled())) {
            return (new Response(Response::RC_ACCEPTED, ['message' => 'Order Not Canceled']))->json();
        } else {
            $package->status = Package::STATUS_WAITING_FOR_CANCEL_PAYMENT;
            $package->payment_status = Package::PAYMENT_STATUS_PENDING;
            $package->save();
            return (new Response(Response::RC_SUCCESS))->json();
        }
    }

    public function claimOrder(Request $req, Package $package)
    {
	//only set status to paid cancel
        $package->status = Package::STATUS_PAID_CANCEL;
        $package->save();

        $existCodeLogable = CodeLogable::where('code_id', $package->code->id)
            ->latest()
            ->first();
        $arr = [
            'status' => CodeLogable::STATUS_CANCEL_DRAFT,
            'status_description' => 'Pesanan dibatalkan oleh customer',
        ];
        CancelOrderTracker::PayThenClaimOrder($package, $existCodeLogable, $arr);
        return (new Response(Response::RC_SUCCESS, ['message' => 'Claim Order Success']))->json();
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
        }
    }
}
