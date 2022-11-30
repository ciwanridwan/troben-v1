<?php

namespace App\Http\Controllers\Api\V;

use App\Exceptions\Error;
use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;
use App\Http\Resources\SelfServices\PackageResource;
use App\Http\Response;
use App\Jobs\Operations\CancelPackage;
use App\Jobs\Packages\UpdateExistingPackageByOffice;
use App\Jobs\Users\Actions\VerifyExistingUser;
use App\Models\Code;
use App\Models\Customers\Customer;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

use function PHPUnit\Framework\isNull;

class SelfServiceController extends Controller
{
    private Code $code;

    /**
     * @param string $content
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws Error
     */
    public function packageDetail(string $content): JsonResponse
    {
        $code = Code::query()->where('content', $content)->where('codeable_type', Package::class)->first();
        if ($code == null) {
            throw InvalidDataException::make(Response::RC_INVALID_DATA);
        }
        $package = Package::query()->find($code->codeable_id);
        return $this->jsonSuccess(new PackageResource($package));
    }
    /**
     * @param Request $request
     * @param string $content
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function packageUpdate(Request $request, string $content): JsonResponse
    {
        /** @var Code $code */
        $code = Code::query()->where('content', $content)->where('codeable_type', Package::class)->firstOrFail();
        $job = new UpdateExistingPackageByOffice($code->codeable, $request->all());
        $this->dispatch($job);
        $code->codeable->setAttribute('updated_by', $request->auth->id)->save();

        return $this->jsonSuccess();
    }

    /**
     * @param string $content
     * @return JsonResponse
     * @throws Error
     */
    public function packageCancel(Request $request, string $content): JsonResponse
    {
        /** @var Code $code */
        $code = Code::query()->where('content', $content)->where('codeable_type', Package::class)->firstOrFail();
        $deliverable = Deliverable::query()->where('deliverable_id', $code->codeable_id)->where('deliverable_type', Package::class)->first();
        if (isNull($deliverable)) {
            $job = new CancelPackage($code->codeable, $request->all());
            $this->dispatch($job);
            $code->codeable->setAttribute('updated_by', $request->auth->id)->save();

            return $this->jsonSuccess();
        }

        $delivery = Delivery::query()->where('id', $deliverable->delivery_id)->first();
        if (isNull($delivery)) {
            $job = new CancelPackage($code->codeable, $request->all());
            $this->dispatch($job);
            $code->codeable->setAttribute('updated_by', $request->auth->id)->save();

            return $this->jsonSuccess();
        }

        if ($delivery->status == Delivery::STATUS_FINISHED) {
            $job = new CancelPackage($code->codeable, $request->all());
            $this->dispatch($job);
            $code->codeable->setAttribute('updated_by', $request->auth->id)->save();

            return $this->jsonSuccess();
        } else {
            $job = new CancelPackage($code->codeable, $request->all());
            $this->dispatch($job);
            $delivery->where('id', $deliverable->delivery_id)->delete();
            $code->codeable->setAttribute('updated_by', $request->auth->id)->save();

            return $this->jsonSuccess();
        }
    }

    // LAST CONDITION AND FLOW FOR CANCEL PACKAGE
    // Log::info('canceling order '.$code->content);
    // try {
    //     $result = DB::select('call cancel_order(?)', [$code->content]);
    // } catch (\Throwable $e) {
    //     Log::alert($e->getMessage());
    //     throw Error::make(Response::RC_INVALID_DATA);
    // }
    // Log::info('canceling done.', $result);
    // END

    /**
     * @param Request $request
     * @param string $account
     * @return JsonResponse
     * @throws Error
     * @throws \Illuminate\Validation\ValidationException
     */
    public function accountVerify(Request $request, string $account): JsonResponse
    {
        $phoneNumber =
            PhoneNumberUtil::getInstance()->format(
                PhoneNumberUtil::getInstance()->parse($request->phone, 'ID'),
                PhoneNumberFormat::E164
            );
        if ($account === 'user') {
            //            $input = array_merge($request->toArray(),['email_verified_at' => Carbon::now()]);
            /** @var User $user */
            $user = User::where('phone', $phoneNumber)->first();
            throw_if($user == null, InvalidDataException::make(Response::RC_INVALID_DATA));
            $job = new VerifyExistingUser($user);
            $this->dispatch($job);
            $user->setAttribute('updated_by', $request->auth->id);
        } elseif ($account === 'customer') {
            //            $input = array_merge($request->toArray(),['phone_verified_at' => Carbon::now()]);
            /** @var Customer $customer */
            $customer = Customer::where('phone', $phoneNumber)->first();
            throw_if($customer == null, InvalidDataException::make(Response::RC_INVALID_DATA));

            $customer->{$customer->getVerifiedColumn()} = Carbon::now();
            $customer->save();
            $customer->setAttribute('updated_by', $request->auth->id)->save();
        } else {
            throw Error::make(Response::RC_BAD_REQUEST);
        }

        return $this->jsonSuccess();
    }

    /**
     * @param Request $request
     * @param string $content
     * @return JsonResponse
     * @throws Error
     * @throws \Illuminate\Validation\ValidationException
     */
    public function deliveryDestinationUpdate(Request $request, string $content): JsonResponse
    {
        $input = Validator::make($request->toArray(), [
            'partner_code' => ['required', 'exists:App\Models\Partners\Partner,code']
        ])->validate();

        /** @var Code $code */
        $this->checkDelivery($content);
        Log::info('changing destination delivery '.$this->code->content);
        try {
            $result = DB::select('call change_delivery_destination(?,?)', [$this->code->content, $input['partner_code']]);
        } catch (\Throwable $e) {
            Log::alert('error change destination delivery: '.$e->getMessage(), ['content' => $content, 'request' => $request->all()]);
            throw InvalidDataException::make(Response::RC_INVALID_DATA);
        }
        Log::info("change destination $content done.", [$result]);

        $this->code->codeable->setAttribute('updated_by', $request->auth->id)->save();
        return $this->jsonSuccess();
    }

    /**
     * @param Request $request
     * @param string $content
     * @return JsonResponse
     * @throws Error
     * @throws \Illuminate\Validation\ValidationException
     */
    public function deliveryPackageAppend(Request $request, string $content): JsonResponse
    {
        $input = Validator::make($request->toArray(), [
            'package_code' => ['required', 'exists:codes,content']
        ])->validate();

        $this->checkDelivery($content);

        Log::info('delivery append package '.$this->code->content);
        try {
            $result = DB::select('call append_package_to_delivery(?,?,?)', [$input['package_code'], $this->code->content], $request->auth->id);
        } catch (\Throwable $e) {
            Log::alert('error delivery append package: '.$e->getMessage(), ['content' => $content, 'request' => $request->all()]);
            throw InvalidDataException::make(Response::RC_INVALID_DATA);
        }
        Log::info("delivery append package $content done.", [$result]);

        return $this->jsonSuccess();
    }

    /**
     * @param $content
     */
    protected function checkDelivery($content): void
    {
        $this->code = Code::where('content', $content)->where('codeable_type', Delivery::class)->firstOrFail();
    }
}
