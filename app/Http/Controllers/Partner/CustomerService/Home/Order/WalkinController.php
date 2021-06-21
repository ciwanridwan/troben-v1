<?php

namespace App\Http\Controllers\Partner\CustomerService\Home\Order;

use App\Actions\CustomerService\WalkIn\CreateWalkinOrder;
use App\Actions\Pricing\PricingCalculator;
use App\Exceptions\Error;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\CustomerResource;
use App\Http\Response;
use App\Jobs\Packages\CreateNewPackage;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Models\Customers\Customer;
use App\Models\Geo\Province;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class WalkinController extends Controller
{
    public function create(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            if ($request->check) {
                /** @var Partner $partner */
                $partner = $partnerRepository->getPartner();
                return $this->checkPrice($request, $partner);
            }
            if ($request->geo) {
                $geo = Province::with('regencies', 'regencies.districts', 'regencies.districts.sub_districts')->all();
                return (new Response(Response::RC_SUCCESS, $geo->toArray()))->json();
            }
        }
        return view('partner.customer-service.order.walkin.index');
    }

    public function store(Request $request, PartnerRepository $partnerRepository)
    {
        (new CreateWalkinOrder($partnerRepository->getPartner(), $request->all()))->create();

        return (new Response(Response::RC_SUCCESS))->json();




        $request->validate([
            'items' => ['required'],
            'photos' => ['required'],
            'photos.*' => ['required', 'image']
        ]);

        $inputs = $request->except('photos');
        foreach ($inputs as $key => $value) {
            $inputs[$key] = json_decode($value);
        }

        $inputs['customer_id'] = Customer::byHash($inputs['customer_hash'])->id;

        /** @var Partner $partner */
        $partner = $partnerRepository->getPartner();

        $inputs['sender_address'] = $partner->geo_address;
        $inputs['origin_regency_id'] = $partner->geo_regency_id;
        $inputs['origin_district_id'] = $partner->geo_district_id;
        $inputs['origin_sub_district_id'] = $partner->geo_sub_district_id;

        $items = json_decode($request->input('items')) ?? [];

        foreach ($items as $key => $item) {
            $items[$key] = (new Collection($item))->toArray();
        }

        $job = new CreateNewPackage($inputs, $items);

        $this->dispatchNow($job);

        $uploadJob = new CustomerUploadPackagePhotos($job->package, $request->file('photos') ?? []);

        $this->dispatchNow($uploadJob);




        return (new Response(Response::RC_SUCCESS))->json();
    }

    public function calculate(Request $request, PartnerRepository $partnerRepository)
    {
        /** @var Partner $partner */
        $partner = $partnerRepository->getPartner();
        $regency = $partner->regency;

        $paramsCalculator = [
            'origin_province_id' => $regency->province_id,
            'origin_regency_id' => $regency->id,
            'destination_id' => $request->destination_sub_district_id,
            'items' => $request->items
        ];

        return PricingCalculator::calculate($paramsCalculator);
    }

    public function customer(Request $request)
    {
        $phoneNumber =
            PhoneNumberUtil::getInstance()->format(
                PhoneNumberUtil::getInstance()->parse($request->phone, 'ID'),
                PhoneNumberFormat::E164
            );

        Validator::validate(['phone' => $phoneNumber], [
            'phone' => ['required', 'exists:customers,phone']
        ]);


        $customer = Customer::where('phone', $phoneNumber)->first();
        return (new Response(Response::RC_SUCCESS, CustomerResource::make($customer)))->json();
    }

    public function checkPrice(Request $request, Partner $partner): JsonResponse
    {

        /** @var Regency $regency */
        $regency = $partner->regency;

        throw_if(! $regency, Error::make(Response::RC_PARTNER_GEO_UNAVAILABLE));

        /** @var Price $price */
        $price = PricingCalculator::getPrice($regency->province_id, $regency->id, $request->destination_id);
        if ($price) {
            return (new Response(Response::RC_SUCCESS))->json();
        }
    }
}
