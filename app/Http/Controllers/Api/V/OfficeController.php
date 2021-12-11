<?php

namespace App\Http\Controllers\Api\V;

use App\Actions\Auth\AccountAuthentication;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeResource;
use App\Http\Response;
use App\Jobs\Customers\CreateNewCustomer;
use App\Jobs\Customers\DeleteExistingCustomer;
use App\Jobs\Customers\UpdateExistingCustomer;
use App\Jobs\Offices\CreateNewOfficer;
use App\Jobs\Offices\DeleteExistingOfficer;
use App\Jobs\Offices\UpdateExistingOfficer;
use App\Models\Offices\Office;
use App\Models\OneTimePassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class OfficeController extends Controller
{

    public function authentication(Request $request): JsonResponse
    {
        $inputs = $this->validate($request, [
            'guard' => ['nullable', Rule::in(['admin', 'user'])],
            'username' => ['required'],
            'password' => ['required'],
            'otp_channel' => ['nullable', Rule::in(OneTimePassword::OTP_CHANNEL)],
            'device_name' => ['required'],
        ]);

        // override value
        $inputs['guard'] = 'office';
        $inputs['otp'] = $inputs['otp'] ?? false;

        return (new AccountAuthentication($inputs))->officeAttempt();
    }

    public function profile(Request $request): JsonResponse
    {
        $account = $request->user();;
        return $this->jsonSuccess(new OfficeResource($account));
    }

    public function index(Request $request): JsonResponse
    {
        $account = $request->user();;
        return $this->jsonSuccess(new OfficeResource($account));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin')) return $this->jsonSuccess(new Response::RC_UNAUTHORIZED);

        $job = new CreateNewOfficer($request->all());
        $this->dispatch($job);

        return $this->jsonSuccess(new Response::RC_SUCCESS);
    }


    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin')) return $this->jsonSuccess(new Response::RC_UNAUTHORIZED);
        $office = (new Office())->byHashOrFail($request->hash);
        $job = new DeleteExistingOfficer($office);
        $this->dispatch($job);

        return $this->jsonSuccess(new Response::RC_SUCCESS);
    }

    public function update(Office $office, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin')) return $this->jsonSuccess(new Response::RC_UNAUTHORIZED);

        $job = new UpdateExistingOfficer($office, $request->all());
        $this->dispatch($job);

        return $this->jsonSuccess(new Response::RC_SUCCESS);
    }
}
