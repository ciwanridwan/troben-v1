<?php

namespace App\Http\Controllers\Admin\Master;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Master\PartnerResource;
use App\Http\Resources\Geo\DistrictResource;
use App\Http\Resources\Geo\ProvinceResource;
use App\Http\Resources\Geo\RegencyResource;
use App\Http\Resources\Geo\SubDistrictResource;
use App\Http\Response;
use App\Jobs\Partners\DeleteExistingPartner;
use App\Models\Geo\District;
use App\Models\Geo\Province;
use App\Models\Geo\Regency;
use App\Models\Geo\SubDistrict;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PartnerController extends Controller
{
    use HasResource;

    /**
     * Filtered attributes.
     * @var array
     */
    protected array $attributes;

    /**
     * Partner base query builder.
     * @var Builder
     */
    protected Builder $query;

    protected string $model = Partner::class;

    /**
     * Request rule definitions.
     * @var array
     */
    protected array $rules;

    public function __construct()
    {
        $this->rules = [
            'name'          => ['filled'],
            'code'          => ['filled'],
            'contact_email' => ['filled'],
            'contact_phone' => ['filled'],
            'type'          => ['filled'],
            'q'             => ['filled'],
        ];
        $this->baseBuilder(Partner::query());
    }




    /**
     * Showing page and get all partner data.
     * Route Path       : {APP_URL}/admin/master/partner
     * Route Name       : admin.master.partner
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $this->attributes = $request->validate($this->rules);

            $this->getResource();

            return $this->jsonSuccess(PartnerResource::collection($this->query->paginate($request->input('per_page', 15))));
        }

        return view('admin.master.partner.index');
    }

    /**
     * Show Page create partner
     * Route Path       : {APP_URL}/admin/master/partner/add
     * Route Name       : admin.master.partner
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|JsonResponse
     */
    public function create(Request $request)
    {
        if ($request->expectsJson()) {

            $data = [
                'partner_types' => Partner::TYPES,
                'geo' => [
                    'provinces' => Province::all(),
                    'regencies' => Regency::all(),
                    'districts' => District::all(),
                    'sub_districts' => SubDistrict::all()
                ]
            ];
            return (new Response(Response::RC_SUCCESS, $data))->json();
        }

        return view('admin.master.partner.create_partner');
    }

    /**
     *
     * Delete Partner.
     * Route Path       : admin/master/partner
     * Route Name       : admin.master.partner
     * Route Method     : DELETE.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $partner = (new Partner())->byHashOrFail($request->hash);
        $job = new DeleteExistingPartner($partner);
        $this->dispatch($job);
        return (new Response(Response::RC_SUCCESS, $job->partner))->json();
    }
}
