<?php

namespace App\Http\Controllers\Api\Operation;

use App\Models\Code;
use App\Models\Packages\Package;
use App\Http\Controllers\Controller;
use App\Jobs\Operations\UpdatePackagePaymentStatus;
use App\Jobs\Operations\UpdateDeliveryStatus;
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
            $deliverale = Deliverable::query()->where('deliverable_id', $code->codeable_id)->firstOrFail();
            $delivery = Delivery::where('id', $deliverale->delivery_id)->firstOrFail();
            $deliveryJob = new UpdateDeliveryStatus($delivery, $request->all());
            $this->dispatch($deliveryJob);
        }
        $this->dispatch($job);
        $code->codeable->setAttribute('updated_by', $request->auth->id)->save();

        return $this->jsonSuccess();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $content
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
