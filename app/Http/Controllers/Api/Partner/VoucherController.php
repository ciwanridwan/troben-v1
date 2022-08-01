<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\DataVoucherResource;
use App\Http\Resources\Api\Partner\VoucherResource;
use App\Http\Response;
use App\Jobs\Voucher\ClaimDiscountVoucher;
use App\Jobs\Voucher\CreateNewVoucher;
use App\Models\Packages\Package;
use App\Models\Packages\Price;
use App\Models\Partners\Voucher;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;
    /**
     * Get Type of Promo List
     * Route Path       : {API_DOMAIN}/promo
     * Route Name       : api.promo.
     */

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->getBasicBuilder(Voucher::query());
        $query->where('user_id', $request->user()->id);
        $query->get();

        return $this->jsonSuccess(VoucherResource::collection($query->paginate(request('per_page', 15))));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, PartnerRepository $repository): JsonResponse
    {
        $request->validate([
            'partner_id' => 'required',
            'discount' => ['required', 'lt:21'],
        ]);
        $job = new CreateNewVoucher($request->all(), $request, $repository);
        $this->dispatchNow($job);

//        $job = new UploadFileVoucher($job->voucher, $request->file('attachment') ?? []);
//        $this->dispatchNow($job);

        return $this->jsonSuccess(VoucherResource::make($job->voucher));
    }

    // function deprecated
    /*
    public function claim(Package $package, Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required']
        ]);

        $voucher = Voucher::where('code', $request->code)->first();
        if ($voucher) {
            $job = new ClaimDiscountVoucher($voucher, $package->id, $request->user()->id);
            $this->dispatchNow($job);

            $data = $this->calculation($voucher, $package);
            $collection = collect($data);
            $collection->push($package);

            return $this->jsonSuccess(DataVoucherResource::make(array_merge($data, $package->toArray())));
        } else {
            return (new Response(Response::RC_DATA_NOT_FOUND, ['message' => 'Kode Voucher Tidak Ditemukan']))->json();
        }
    }
    */

    // function deprecated
    /*
    public function calculation(Voucher $voucher, Package $package)
    {
        $service = $package->prices->where('type', Price::TYPE_SERVICE)->first();
        $discount = $service->amount * ($voucher->discount / 100);

        return [
            'service_price' => $service->amount,
            'discount' => $discount,
            'total_payment' => $package->total_amount - $discount
        ];
    }
    */

    /**
     * @param Builder $builder
     * @return Builder
     */
    private function getBasicBuilder(Builder $builder): Builder
    {
        $builder->when(request()->has('id'), fn ($q) => $q->where('id', $this->attributes['id']));
        $builder->when(
            request()->has('q') and request()->has('id') === false,
            fn ($q) => $q->where('title', 'like', '%'.$this->attributes['q'].'%')
        );

        return $builder;
    }
}
