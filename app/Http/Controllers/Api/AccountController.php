<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Account\CustomerInfoResource;
use App\Http\Response;
use App\Jobs\Customers\CustomerUploadPhoto;
use App\Jobs\Users\UserUploadPhoto;
use App\Models\Attachment;
use App\Models\Customers\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Http\Controllers\Controller;
use App\Jobs\Users\UpdateExistingUser;
use App\Http\Resources\Account\UserResource;
use App\Jobs\Customers\UpdateExistingCustomer;
use App\Http\Resources\Account\CustomerResource;
use App\Http\Requests\Api\Account\UpdateAccountRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class AccountController extends Controller
{
    public const DISK_CUSTOMER = 'avatar';

    /**
     * Get Account Information
     * Route Path       : {API_DOMAIN}/me
     * Route Name       : api.me
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $account = $request->user();
        return $account instanceof Customer ? $this->getCustomerInfo($account) : $this->getUserInfo($account);
    }

    /**
     * Update Account Information
     * Route Path       : {API_DOMAIN}/me
     * Route Name       : api.me.update
     * Route Method     : POST/PUT.
     *
     * @param \App\Http\Requests\Api\Account\UpdateAccountRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(UpdateAccountRequest $request): JsonResponse
    {
        if ($request->has('referral_code')) {
            $agent = DB::table('agents')->where('referral_code', $request->get('referral_code'))->first();
            if (is_null($agent)) {
                return (new Response(Response::RC_INVALID_DATA, []))->json();
            }
        }

        $account = ($request->user() instanceof Customer)
            ? $this->updateCustomer($request->user(), $request)
            : $this->updateUser($request->user(), $request);

        return $account instanceof Customer
            ? $this->getCustomerInfo($account)
            : $this->getUserInfo($account);
    }

    /**
     * Delete Account Customer */
    public function deleteAccount(Request $request): JsonResponse
    {
        $customers = $request->user();
        if (is_null($customers)) {
            return (new Response(Response::RC_UNAUTHORIZED))->json();
        }

        $customers->delete_expired_at = Carbon::now()->addMonth();
        $customers->save();

        return (new Response(Response::RC_SUCCESS))->json();
    }

    /**
     * @param Customer $account
     *
     * @return JsonResponse
     */
    public function getCustomerInfo(Customer $account): JsonResponse
    {
        return $this->jsonSuccess(new CustomerInfoResource($account));
    }
    /**
     * @param User $account
     *
     * @return JsonResponse
     */
    public function getUserInfo(User $account): JsonResponse
    {
        return $this->jsonSuccess(new UserResource($account));
    }


    public function updatePassword(Request $inputs): JsonResponse
    {
        $phoneNumber =
            PhoneNumberUtil::getInstance()->format(
                PhoneNumberUtil::getInstance()->parse($inputs->phone, 'ID'),
                PhoneNumberFormat::E164
            );

        $customer = Customer::where('phone', $phoneNumber)
            ->Where('email', $inputs->email)
            ->first();

        if ($customer != null) {
            $job = new UpdateExistingCustomer($customer, $inputs->all());
            $this->dispatch($job);

            $customer->save();

            return $this->jsonSuccess(new CustomerResource($customer));
        }

        return (new Response(Response::RC_INVALID_DATA, []))->json();
    }

    /**
     * @param \App\Models\Customers\Customer                        $customer
     * @param \App\Http\Requests\Api\Account\UpdateAccountRequest   $inputs
     *
     * @return \App\Models\Customers\Customer
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function updateCustomer(Customer $customer, UpdateAccountRequest $inputs): Customer
    {
        if ($inputs->has('photos')) {
            $attachable = DB::table('attachable')
                ->where('attachable_id', $customer->id)
                ->where('attachable_type', 'App\Models\Customers\Customer')
                ->first();
            if ($attachable != null) {
                $attachment = Attachment::where('id', $attachable->attachment_id)->first();
                Storage::disk(self::DISK_CUSTOMER)->delete($attachment->path);
                $attachment->forceDelete();
            }
        }
        $job = new UpdateExistingCustomer($customer, $inputs->all());
        $this->dispatch($job);

        $uploadJob = new CustomerUploadPhoto($job->customer, $inputs->file('photos') ?? []);
        $this->dispatchNow($uploadJob);

        return $job->customer->refresh();
    }

    /**
     * @param \App\Models\User                                      $user
     * @param \App\Http\Requests\Api\Account\UpdateAccountRequest   $inputs
     *
     * @return \App\Models\User
     */
    protected function updateUser(User $user, UpdateAccountRequest $inputs): User
    {
        if ($inputs->has('photos')) {
            $attachable = DB::table('attachable')
                ->where('attachable_id', $user->id)
                ->where('attachable_type', 'App\Models\Customers\Customer')
                ->first();
            if ($attachable != null) {
                $attachment = Attachment::where('id', $attachable->attachment_id)->first();
                Storage::disk(self::DISK_CUSTOMER)->delete($attachment->path);
                $attachment->forceDelete();
            }
        }
        $job = new UpdateExistingUser($user, $inputs->all());
        $this->dispatch($job);

        $uploadJob = new UserUploadPhoto($job->user, $inputs->file('photos') ?? []);
        $this->dispatchNow($uploadJob);

        return $job->user->refresh();
    }

    /**
     * @param Customer $customer
     * @param Request $request
     * @return Customer
     */
    protected function storeAddress(Customer $customer, Request $request): Customer
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'geo_province_id' => 'required',
            'geo_regency_id' => 'required',
            'geo_district_id' => 'required',
        ]);

        $address = DB::table('customer_addresses')
            ->where('customer_id', $customer->id)
            ->first();
        if ($address != null) {
            $address->delete();
        }


        $address = new Address();

        $address->customer_id = $customer->id;
        $address->name = $request->name;
        $address->address = $request->address;
        $address->geo_province_id = $request->geo_province_id;
        $address->geo_regency_id = $request->geo_regency_id;
        $address->geo_district_id = $request->geo_district_id;
        $address->is_default = '1';

        $address->save();
        return $customer->refresh();
    }
}
