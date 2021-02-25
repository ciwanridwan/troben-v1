<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Response;
use Illuminate\Http\Request;
use App\Models\Partners\Partner;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\Partners\DeleteExistingPartner;
use App\Http\Resources\Admin\Master\PartnerResource;

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
            'name' => ['filled'],
            'code' => ['filled'],
            'contact_email' => ['filled'],
            'contact_phone' => ['filled'],
            'type' => ['filled'],
            'q' => ['filled'],
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
