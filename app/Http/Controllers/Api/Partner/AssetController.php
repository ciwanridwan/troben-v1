<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Asset\TransporterResource;
use App\Http\Resources\Api\Partner\asset\UserResource;
use App\Models\Partners\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

    public function index(Request $request)
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

    public function getEmployee()
    {
        return $this->jsonSuccess(new UserResource(collect($this->partner->users()->wherePivotNotIn('role',['owner'])->get()->groupBy('id'))));
    }

    public function getTransporter()
    {
        return $this->jsonSuccess(new TransporterResource(collect($this->partner->transporters->fresh())));
    }
}
