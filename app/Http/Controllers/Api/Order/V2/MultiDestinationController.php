<?php

namespace App\Http\Controllers\Api\Order\V2;

use App\Exceptions\InvalidDataException;
use App\Exceptions\UserUnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\StoreMultiDestinationRequest;
use App\Http\Response;
use App\Jobs\Packages\Actions\MultiAssignFirstPartner;
use App\Jobs\Packages\CreateNewPackage;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Models\Customers\Customer;
use App\Models\Packages\MultiDestination;
use App\Models\Packages\Price as PackagePrice;
use App\Models\Partners\Partner;
use App\Supports\Geo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MultiDestinationController extends Controller
{
    protected array $attributes;
    /**
     * To create new order multi destination
     * on customer apps
     */
    public function store(StoreMultiDestinationRequest $request): JsonResponse
    {
        $request->validated();
        $this->attributes = $request->all();

        // check user
        $user = $request->user();
        throw_if(!$user instanceof Customer, UserUnauthorizedException::class, Response::RC_UNAUTHORIZED);
        $this->attributes['customer_id'] = $user->id;

        // check the sending area
        $coordOrigin = sprintf('%s,%s', $this->attributes['sender_latitude'], $this->attributes['sender_longitude']);
        $resultOrigin = Geo::getRegional($coordOrigin, true);
        if ($resultOrigin == null) {
            throw InvalidDataException::make(Response::RC_INVALID_DATA, ['message' => 'Origin not found', 'coord' => $coordOrigin]);
        }

        // check partner
        if (isset($this->attributes['partner_code'])) {
            $partner = Partner::where('code', $this->attributes['partner_code'])->first();
            if (is_null($partner)) {
                throw InvalidDataException::make(Response::RC_INVALID_DATA, ['message' => 'Partner not found', 'code' => $this->attributes['partner_code']]);
            }
        }

        // sender attributes
        $senderAttributes = $request->only('sender_address', 'sender_phone', 'sender_name', 'sender_detail_address', 'sender_latitude', 'sender_longitude', 'service_code', 'transporter_type', 'partner_code');
        $senderAttributes['customer_id'] = $user->id;
        $senderAttributes['origin_regency_id'] = $resultOrigin['regency'];
        $senderAttributes['origin_district_id'] = $resultOrigin['district'];
        $senderAttributes['origin_sub_district_id'] = $resultOrigin['subdistrict'];

        $countReceiver = count($this->attributes['receiver_name']);
        $packageIds = [];
        $childId = [];
        $allHash = [];

        for ($i = 0; $i < $countReceiver; $i++) {
            $receiverAttributes = [
                'receiver_name' => $this->attributes['receiver_name'][$i],
                'receiver_phone' => $this->attributes['receiver_phone'][$i],
                'receiver_address' => $this->attributes['receiver_address'][$i],
                'receiver_detail_address' => $this->attributes['receiver_detail_address'][$i],
                'destination_regency_id' => $this->attributes['destination_regency_id'][$i],
                'destination_district_id' => $this->attributes['destination_district_id'][$i],
                'destination_sub_district_id' => $this->attributes['destination_sub_district_id'][$i]
            ];

            $packageAttributes = array_merge($senderAttributes, $receiverAttributes);
            foreach ($this->attributes['items'][$i] as $key => $item) {
                $this->attributes['items'][$i][$key]['is_insured'] = false;
                
                if (isset($item['insurance'])) {
                    if ($item['insurance'] === '1') {
                        $this->attributes['items'][$i][$key]['is_insured'] = true;
                    }
                }

                $this->attributes['items'][$i][$key]['category_item_id'] = $item['category_id'];
            }

            $job = new CreateNewPackage($packageAttributes, $this->attributes['items'][$i]);
            $this->dispatchNow($job);
            Log::info('after dispatch job. ', [$request->get('sender_name')]);

            $uploadJob = new CustomerUploadPackagePhotos($job->package, $this->attributes['photos'][$i] ?? []);

            $this->dispatchNow($uploadJob);

            if ($i === 0) {
                $result['parent_id'] =  $job->package->id;
                $packageHash['parent_hash'] = $job->package->hash;
            } else {
                $pickupFee = $job->package->prices->where('type', PackagePrice::TYPE_DELIVERY)->where('description', PackagePrice::TYPE_PICKUP)->first();
                $job->package->total_amount -= $pickupFee->amount;
                $job->package->save();

                $pickupFee->amount = 0;
                $pickupFee->save();

                $result['child_id'] = $job->package->id;
                array_push($childId, $result['child_id']);

                $packageHash['child_hash'] = $job->package->hash;
                array_push($allHash, $packageHash['child_hash']);
            }
            $packageIds = $result;
            $hashPackage = $packageHash;
            // assign partner
            $type = 'new';
            $assignJob = new MultiAssignFirstPartner($job->package->toArray(), $partner, $type);
            $this->dispatchNow($assignJob);
        }

        // set package id parent package and child package
        $idPackages = [
            'parent_id' => $packageIds['parent_id'],
            'child_id' => $childId
        ];

        // inserting to multi destination table
        foreach ($idPackages['child_id'] as $idChild) {
            MultiDestination::create([
                'parent_id' => $idPackages['parent_id'],
                'child_id' => $idChild
            ]);
        }

        $results = [
            'parent_hash' => $hashPackage['parent_hash'],
            'child_hash' => $allHash
        ];

        return (new Response(Response::RC_SUCCESS, $results))->json();
    }
}
