<?php

namespace App\Http\Controllers\Api\Operation;

use App\Models\Code;
use App\Models\Packages\Price;
use App\Models\Packages\Package;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Jobs\Operations\UpdatePackagePaymentStatus;
use App\Jobs\Operations\UpdateDeliveryStatus;
use App\Jobs\Operations\UpdatePackageStatus as OperationsUpdatePackageStatus;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    /**
     * @param Request $request
     * @param string $content
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updatePaymentStatus(Request $request, string $content): JsonResponse
    {
        /** @var Code $code */
        $code = Code::query()->where('content', $content)->where('codeable_type', Package::class)->firstOrFail();
        $job = new UpdatePackagePaymentStatus($code->codeable, $request->all());

        if ($request->payment_status == Package::PAYMENT_STATUS_PAID) {
            $deliverable = Deliverable::query()->where('deliverable_id', $code->codeable_id)->where('deliverable_type', Package::class)->firstOrFail();
            $delivery = Delivery::where('id', $deliverable->delivery_id)->firstOrFail();
            $deliveryJob = new UpdateDeliveryStatus($delivery, $request->all());
            $this->dispatch($deliveryJob);
        }
        $this->dispatch($job);

	$userId = 0;
	if ($request->auth) { $userId = $request->auth->id; }
        $code->codeable->setAttribute('updated_by', $userId)->save();

        return $this->jsonSuccess();
    }

    /** Todo update package status, set logic and condition to this function */
    public function updateStatus(Request $request, string $content): JsonResponse
    {
        /** @var Code $code */
        $code = Code::query()->where('content', $content)->where('codeable_type', Package::class)->first();
        if (is_null($code)) {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        }

        $deliverable = Deliverable::where('deliverable_id', $code->codeable->id)->where('deliverable_type', Package::class)->first();
        if (is_null($deliverable)) {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        }

        switch ($request->status) {
            case Package::STATUS_WAITING_FOR_PICKUP:
                $code->codeable->status = Package::STATUS_WAITING_FOR_PICKUP;
                $deliverable->delivery->status = Delivery::STATUS_ACCEPTED;
                $deliverable->delivery->save();

                $job = new OperationsUpdatePackageStatus($code->codeable, $request->all());
                $this->dispatch($job);

                $data = [
                    'package_status' => $code->codeable->status,
                    'delivery_status' => $deliverable->delivery->status
                ];

                return (new Response(Response::RC_UPDATED, $data))->json();
                break;

            case Package::STATUS_PICKED_UP:
                $code->codeable->status = Package::STATUS_PICKED_UP;
                $deliverable->delivery->status = Delivery::STATUS_EN_ROUTE;
                // $deliverable->is_onboard = true;
                // $deliverable->save();
                // dd($deliverable->save());
                $deliverable->delivery->save();

                $job = new OperationsUpdatePackageStatus($code->codeable, $request->all());
                $this->dispatch($job);

                $data = [
                    'package_status' => $code->codeable->status,
                    'delivery_status' => $deliverable->delivery->status,
                    'delilverable_status' => $deliverable->is_onboard,
                ];

                return (new Response(Response::RC_UPDATED, $data))->json();
                break;

            case Package::STATUS_WAITING_FOR_ESTIMATING:
                $code->codeable->status = Package::STATUS_WAITING_FOR_ESTIMATING;
                $deliverable->delivery->status = Delivery::STATUS_FINISHED;
                $deliverable->is_onboard = false;
                $deliverable->save();
                $deliverable->delivery->save();

                $job = new OperationsUpdatePackageStatus($code->codeable, $request->all());
                $this->dispatch($job);

                $data = [
                    'package_status' => $code->codeable->status,
                    'delivery_status' => $deliverable->delivery->status,
                    'delilverable_status' => $deliverable->is_onboard,
                ];

                return (new Response(Response::RC_UPDATED, $data))->json();
                break;

            case Package::STATUS_ESTIMATING:
                $code->codeable->status = Package::STATUS_ESTIMATING;

                $job = new OperationsUpdatePackageStatus($code->codeable, $request->all());
                $this->dispatch($job);

                $data = [
                    'package_status' => $code->codeable->status,
                    'estimator_id' => $code->codeable->estimator_id,
                ];

                return (new Response(Response::RC_UPDATED, $data))->json();
                break;

            case Package::STATUS_ESTIMATED:
                $code->codeable->status = Package::STATUS_ESTIMATED;
                $code->codeable->payment_status = Package::PAYMENT_STATUS_DRAFT;

                $job = new OperationsUpdatePackageStatus($code->codeable, $request->all());
                $this->dispatch($job);

                $data = [
                    'package_status' => $code->codeable->status,
                    'payment_status' => $code->codeable->payment_status,
                ];

                return (new Response(Response::RC_UPDATED, $data))->json();
                break;

            case Package::STATUS_REVAMP:
                $code->codeable->status = Package::STATUS_REVAMP;
                $code->codeable->payment_status = Package::PAYMENT_STATUS_DRAFT;

                $job = new OperationsUpdatePackageStatus($code->codeable, $request->all());
                $this->dispatch($job);

                $data = [
                    'package_status' => $code->codeable->status,
                    'payment_status' => $code->codeable->payment_status,
                ];

                return (new Response(Response::RC_UPDATED, $data))->json();
                break;

            case Package::STATUS_WAITING_FOR_APPROVAL:
                $code->codeable->status = Package::STATUS_WAITING_FOR_APPROVAL;
                $code->codeable->payment_status = Package::PAYMENT_STATUS_PENDING;

                $job = new OperationsUpdatePackageStatus($code->codeable, $request->all());
                $this->dispatch($job);

                $data = [
                    'package_status' => $code->codeable->status,
                    'payment_status' => $code->codeable->payment_status,
                ];

                return (new Response(Response::RC_UPDATED, $data))->json();
                break;

            case Package::STATUS_ACCEPTED:
                $code->codeable->status = Package::STATUS_ACCEPTED;
                $code->codeable->payment_status = Package::PAYMENT_STATUS_PENDING;

                $job = new OperationsUpdatePackageStatus($code->codeable, $request->all());
                $this->dispatch($job);

                $data = [
                    'package_status' => $code->codeable->status,
                    'payment_status' => $code->codeable->payment_status,
                ];

                return (new Response(Response::RC_UPDATED, $data))->json();
                break;

            case Package::STATUS_WAITING_FOR_PACKING:
                $code->codeable->status = Package::STATUS_WAITING_FOR_PACKING;
                $code->codeable->payment_status = Package::PAYMENT_STATUS_PAID;

                $job = new OperationsUpdatePackageStatus($code->codeable, $request->all());
                $this->dispatch($job);

                $data = [
                    'package_status' => $code->codeable->status,
                    'payment_status' => $code->codeable->payment_status,
                ];

                return (new Response(Response::RC_UPDATED, $data))->json();
                break;

            default:
                return (new Response(Response::RC_BAD_REQUEST))->json();
                break;
        }
        return (new Response(Response::RC_BAD_REQUEST))->json();
    }
    /** End Todo */


    // this function for update Fee Pickup (biaya penjemputan)
    public function updatePickupFee(Request $request, string $content)
    {
        $attributes = $request->validate([
            'amount' => ['required', 'numeric']
        ]);

        $code = Code::where('content', $content)->where('codeable_type', Package::class)->first();
        $pickupPrice = $code->codeable->prices->where('type', Price::TYPE_DELIVERY)->where('description', Price::TYPE_PICKUP)->first();

        $pickupPrice->amount = $attributes['amount'];
        $pickupPrice->save();

	$userId = 0;
	if ($request->auth) {
	$userId = $request->auth->id;
	}

        $code->codeable->setAttribute('updated_by', $userId)->save();

        return (new Response(Response::RC_UPDATED))->json();
    }
    /** End Todo */


    /** Todo Declare Variable with array type to define status in CS
     * CS Scale.
     */
    private function assignDriver(Request $request)
    {
        $status = [
            'package_status' => Package::STATUS_WAITING_FOR_PICKUP,
            'develiry_status' => Delivery::STATUS_ACCEPTED
        ];

        return $status;
    }
    /** End Todo */

    // this function for update Fee Pickup (biaya penjemputan)
}
