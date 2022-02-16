<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\UnloadCode;
use App\Jobs\Deliveries\Actions\UnloadCodeFromDelivery;
use App\Models\Code;
use App\Models\CodeLogable;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransitController extends Controller
{
    /**
     * @param Request $request
     * @param Delivery $delivery
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function unload(Request $request, Delivery $delivery)
    {
        $job = new UnloadCodeFromDelivery($delivery, array_merge($request->only('code'), [
            'status' => Deliverable::STATUS_UNLOAD_BY_DESTINATION_WAREHOUSE,
            'role' => CodeLogable::STATUS_WAREHOUSE_UNLOAD,
        ]));

        $this->dispatch($job);

        $delivery->refresh();

        return (new Response(Response::RC_SUCCESS, DeliveryResource::make($delivery->load('packages'))))->json();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function unloadItem(Request $request, PartnerRepository $repository)
    {
        $data = $this->checkCodes($request, $repository);
        if ($data['codes'] == []){
            return (new Response(Response::RC_INVALID_DATA, $data['error_codes']))->json();
        }
        $job = new UnloadCode(array_merge($data['codes'], [
            'status' => Deliverable::STATUS_UNLOAD_BY_DESTINATION_WAREHOUSE,
            'role' => CodeLogable::STATUS_WAREHOUSE_UNLOAD,
        ]));
        $this->dispatch($job);
        if ($data['error_codes'] != [] && $data['codes'] != []){
            return $this->jsonSuccess(new JsonResource($data));
        }

        return (new Response(Response::RC_SUCCESS))->json();
    }

    public function checkCodes(Request $request, PartnerRepository $repository){
        $codesError = [];
        $codes = [];

        $items = Code::select('id')
            ->whereIn('content', $request->codes)
            ->pluck('id')->toArray();

        foreach($items as $barang){
            $deliveries = Deliverable::select('delivery_id')
                ->where('deliverable_type', 'App\Models\Code')
                ->where('status', 'load_by_driver')
                ->whereHas('delivery', function($q) use ($repository) {
                    $q->where('partner_id', $repository->getPartner()->id);
                    $q->where('status', Delivery::STATUS_FINISHED);
                })
                ->where('deliverable_id', $barang)
                ->pluck('delivery_id')->toArray();

            $code = Code::find($barang);
            if($deliveries == []){
                $codesError[] = $code->content;
            }else{
                $codes[] = $code->content;
            }
        }

        return [
            'error_codes' => $codesError,
            'codes' => $codes
        ];
    }
}
