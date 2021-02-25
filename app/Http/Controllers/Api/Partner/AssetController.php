<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\asset\UserResource;
use App\Models\Partners\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    protected array $attributes;

    public function index(Request $request)
    {
        $this->attributes = Validator::make($request->all(), [
            'type' => ['required',Rule::in([
                'transporter',
                'employee',
            ])],
        ])->validate();

        return $request->type == 'transporter'
                ? $this->getTransporter()
                : $this->getEmployee($request->user()->partners->first()->fresh());
    }

    public function getEmployee(Partner $partner)
    {
        return $this->jsonSuccess(new UserResource(collect($partner->users()->wherePivotNotIn('role',['owner'])->get()->groupBy('id'))));
    }

    public function getTransporter()
    {
        // TODO
    }
}
