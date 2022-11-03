<?php

namespace App\Http\Controllers\Partner\CustomerService\Home\Order;

use App\Actions\Pricing\PricingCalculator;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Exceptions\Error;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\CustomerResource;
use App\Http\Response;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;
use App\Jobs\Packages\CreateWalkinOrder;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Jobs\Packages\Motobikes\CreateWalkinOrderTypeBike;
use App\Models\Customers\Customer;
use App\Models\Geo\Province;
use App\Models\Packages\Package;
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
    protected string $bike = 'bike';

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
        $request->validate([
            'items' => ['required'],
            'photos' => ['required'],
            'photos.*' => ['required', 'image', 'max:10240'],
            'order_type' => ['nullable', 'in:bike,other'], // for check condition bike or not
        ]);

        $bikes = $request->only(['moto_cc', 'moto_type', 'moto_merk', 'moto_year', 'package_id', 'package_item_id']);

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
        $inputs['sender_way_point'] = $partner->address;
        $inputs['sender_latitude'] = $partner->latitude;
        $inputs['sender_longitude'] = $partner->longitude;

        // add partner code
        $inputs['partner_code'] = $partner->code;
        $items = json_decode($request->input('items')) ?? [];

        foreach ($items as $key => $item) {
            $items[$key] = (new Collection($item))->toArray();
        }

        if ($request->input('order_type') === $this->bike) {
            $isSeparate = false;
            $job = new CreateWalkinOrderTypeBike($inputs, $item, $isSeparate, $bikes);
            $this->dispatchNow($job);
        } else {
            $job = new CreateWalkinOrder($inputs, $items);
            $this->dispatchNow($job);
        }

        $uploadJob = new CustomerUploadPackagePhotos($job->package, $request->file('photos') ?? []);
        $this->dispatchNow($uploadJob);

        // Copy from walkin order
        /** @var Package $package */
        // $package = $job->package;

        $job = new AssignFirstPartnerToPackage($job->package, $partner);

        $this->dispatch($job);

        $delivery = $job->delivery;

        event(new DriverUnloadedPackageInWarehouse($delivery));

        $job->package->setAttribute('status', Package::STATUS_WAITING_FOR_APPROVAL)->save();

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
            'items' => $request->items,
            'service_code' => $request->service_code,
        ];

        if (count($paramsCalculator['items']) && $paramsCalculator['items'][0]['desc'] == 'bike') {
            $getPrice = PricingCalculator::getBikePrice($paramsCalculator['origin_regency_id'], $paramsCalculator['destination_id']);
            $pickup_price = 0;
            $insurance = 0;

            $handling_price = 0;
            $service_price = 0;
            switch ($paramsCalculator['items'][0]['moto_cc']) {
                case 150:
                    $handling_price = 175000;
                    $service_price = $getPrice->lower_cc;
                    break;
                case 250:
                    $handling_price = 250000;
                    $service_price = $getPrice->middle_cc;
                    break;
                case 999:
                    $handling_price = 450000;
                    $service_price = $getPrice->high_cc;
                    break;
            }

            if ($paramsCalculator['items'] !== null) {
                $handlingAdditionalPrice = 50000;
            } else {
                $handlingAdditionalPrice = 0;
            }

            foreach ($paramsCalculator['items'] as $item) {
                if (isset($item['is_insured']) && $item['is_insured'] == true){
                // if ($item['is_insured'] == true) {
                    $insurance = $item['price'] * 0.002;
                } else {
                    $insurance = 0;
                }
            }

            $total_amount = $pickup_price + $insurance + $handling_price + $handlingAdditionalPrice + $service_price;

            $result = [
                'details' => [
                    'pickup_price' => $pickup_price,
                    'insurance_price' => $insurance,
                    'handling_price' => $handling_price,
                    'handling_additional_price' => $handlingAdditionalPrice,
                    'service_price' => intval($service_price)
                ],
                'total_amount' => $total_amount,
                'notes' => $getPrice->notes
            ];

            // return $result;
            return (new Response(Response::RC_SUCCESS, $result))->json();

        } else {
            return PricingCalculator::calculate($paramsCalculator);
        }
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
