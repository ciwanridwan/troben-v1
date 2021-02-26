<?php

namespace App\Http\Controllers\Api\Partner;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\Partner\asset\UserResource;
use App\Http\Resources\Api\Partner\Asset\TransporterResource;

class AssetController extends Controller
{
    /**
     * Partner instances.
     *
     * @var \App\Models\Partners\Partner
     */
    protected Partner $partner;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * Get partner assets
     * Route Path       : {API_DOMAIN}/partner/asset
     * Route Name       : api.partner.asset
     * Route Method     : GET.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function index(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'type' => ['required',Rule::in([
                'transporter',
                'employee',
            ])],
        ])->validate();

        $this->partner = $request->user()->partners->first()->fresh();

        return $request->type == 'transporter'
                ? $this->getTransporter()
                : $this->getEmployee();
    }

    public function getEmployee(): JsonResponse
    {
        return $this->jsonSuccess(new UserResource(collect($this->partner->users()->wherePivotNotIn('role', ['owner'])->get()->groupBy('id'))));
    }

    public function getTransporter(): JsonResponse
    {
        return $this->jsonSuccess(new TransporterResource(collect($this->partner->transporters->fresh())));
    }
}
