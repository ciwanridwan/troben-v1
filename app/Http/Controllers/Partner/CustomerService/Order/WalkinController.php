<?php

namespace App\Http\Controllers\Partner\CustomerService\Order;

use App\Actions\Pricing\PricingCalculator;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Geo\Province;
use App\Models\Geo\Regency;
use App\Models\Partners\Partner;
use App\Models\Price;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\Request;

class WalkinController extends Controller
{
    public function create(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            if ($request->check) {
                /** @var Partner $partner */
                $partner = $partnerRepository->getPartner();
                /** @var Regency $regency */
                $regency = $partner->regency;

                /** @var Price $price */
                $price = PricingCalculator::getPrice($regency->province_id, $regency->id, $request->destination_id);
                if ($price) {
                    return (new Response(Response::RC_SUCCESS))->json();
                }
            }
            if ($request->geo) {
                $geo = Province::with('regencies', 'regencies.districts', 'regencies.districts.sub_districts')->all();
                return (new Response(Response::RC_SUCCESS, $geo->toArray()))->json();
            }
        }
        return view('partner.customer-service.order.walkin.index');
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
}
