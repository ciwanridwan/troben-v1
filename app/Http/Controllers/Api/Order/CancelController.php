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
use App\Models\Payments\Gateway;

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
        if ($package->status == Package::STATUS_CANCEL) {
            $cancel = CancelOrder::where('package_id', $package->id)->first();
            $msg = [
                'message' => 'has been canceled',
                'type_cancel' => $cancel->type
            ];
            return (new Response(Response::RC_ACCEPTED, $msg))->json();
        }
        event(new PackageCanceledByCustomer($package));
        $data = new CancelOrder();
        $data->package_id = $package->id;
        $data->type = $request->input('type');
        $data->pickup_price = $pickupPrice;
        $data->save();

        // return $this->jsonSuccess(PackageResource::make($package->fresh()));
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
            'server_time' => $currentTime,
            'expired_time' => $expiredTime,
            'bank' => Gateway::convertChannel($this->gateway->channel)['bank'],
            'va_number' => $firstNum.$vaNumber,
        ];
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
}
