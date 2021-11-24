<?php

namespace App\Http\Controllers\Api\WMS\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\CreateNewDooring;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Models\Code;
use App\Models\Deliveries\Deliverable;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DooringController extends Controller
{
    public Collection $codes;
    public Code $code;

    public function store(Request $request, PartnerRepository $repository)
    {
        $manifested_code = [
            'existed' => []
        ];
        $this->codes = Code::query()->whereIn('content', is_array($request['code']) ? $request['code'] : [$request['code']])->with('codeable')->get();

        foreach ($this->codes as $code) {
            /** @var Package $package */
            $package = $code->codeable instanceof Package ? $code->codeable : $code->codeable->package;
            if ($package->status == Package::STATUS_MANIFESTED) {
                array_push($manifested_code['existed'], $code->content);
            }
        }
        if (empty($manifested_code['existed'])) {
            $job = new CreateNewDooring($repository->getPartner());
            $this->dispatchNow($job);

            $job = new ProcessFromCodeToDelivery($job->delivery, array_merge($request->only(['code']), [
                'status' => Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE,
                'role' => UserablePivot::ROLE_WAREHOUSE
            ]));
            $this->dispatchNow($job);
        } else {
            return (new Response(Response::RC_BAD_REQUEST, $manifested_code))->json();
        }
        return (new Response(Response::RC_SUCCESS, $job->delivery))->json();
    }
}
