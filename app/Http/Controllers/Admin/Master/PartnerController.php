<?php

namespace App\Http\Controllers\Admin\Master;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Builder;
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
        $this->baseBuilder();
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

            foreach (Arr::except($this->attributes, ['q', 'owner']) as $key => $value) {
                $this->getByColumn($key);
            }
            if (Arr::has($this->attributes, 'q')) {
                $this->getSearch($this->attributes['q']);
            }

            return (new Response(Response::RC_SUCCESS, $this->query->paginate($request->input('per_page', 15))));
        }

        return view('admin.master.partner.index');
    }
}
