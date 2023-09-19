<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Balance\DetailResource;
use App\Http\Resources\Api\Partner\Owner\Balance\ReportPartnerTransporterResource;
use App\Http\Resources\Api\Partner\Owner\Balance\SummaryResource;
use App\Http\Response;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerBalanceReportRepository;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    /** @var Builder $query */
    protected Builder $query;

    /** @var array $attributes */
    protected array $attributes;

    /**
     * @param Request $request
     * @param PartnerRepository $repository
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $inputs = array_merge($request->all(), [
            'group' => ['package_code', 'package_id', 'package_created_at'],
            'partner_id' => $repository->getPartner()->id,
            'is_package_created' => true
        ]);

        $partnerType = $repository->getPartner()->type;

        switch ($partnerType) {
            case Partner::TYPE_TRANSPORTER:
                // $query = $this->getMtakIncome($repository->getPartner()->id);
                // $result = DB::select($query);
                $result = $repository->queries()->getIncomeMtak($repository->getPartner()->id);
                // dd($result);
                return $this->jsonSuccess(ReportPartnerTransporterResource::collection($result));
                break;
            default:
                $query = $this->getIncome($repository->getPartner()->id);
                $result = collect(DB::select($query))->groupBy('package_code')->map(function ($k, $v) {
                    $k->map(function ($q) {
                        $q->amount = intval($q->amount);
                        $q->weight = intval($q->weight);
                    });

                    $totalAmount = 0;
                    $penaltyIncome = $k->where('type', 'penalty')->first();

                    $subber = ['penalty', 'discount', 'withdraw'];
                    $totalAmount = $k->whereNotIn('type', $subber)->sum('amount');
                    $totalSubber = $k->whereIn('type', $subber)->sum('amount');

                    $totalAmount = $totalAmount - $totalSubber;

                    return [
                        'package_code' => $k[0]->package_code,
                        'total_amount' => $totalAmount,
                        'created_at' => $k[0]->date,
                        'detail' => $k
                    ];
                })->values();

                return (new Response(Response::RC_SUCCESS, $result))->json();
                break;
        }
    }

    /**
     * @param PartnerRepository $repository
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function summary(PartnerRepository $repository): JsonResponse
    {
        $this->query = $repository->queries()->getPartnerBalanceReportQuery();

        return $this->jsonSuccess(SummaryResource::make($this->query->get()));
    }

    /**
     * @param Request $request
     * @param PartnerRepository $repository
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function detail(Request $request, PartnerRepository $repository): JsonResponse
    {
        $inputs = array_merge($request->all(), [
            'partner_id' => $repository->getPartner()->id,
        ]);

        $this->query = (new PartnerBalanceReportRepository($inputs))->getQuery();
        $sumQuery = (new PartnerBalanceReportRepository(['partner_id' => $repository->getPartner()->id]))->getQuery();

        return $this->jsonSuccess(DetailResource::make([
            'data' => $this->query->paginate($request->input('per_page', 10)),
            'total_amount' => $sumQuery->sum('balance')
        ]));
    }

    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    /** Get Income MTAK By Query
     *  Delivery &
     * Dooring Income.
     */
    private function getMtakIncome($partnerId)
    {
        $q = "select pbdh.partner_id, pbdh.delivery_id as codeable_id, pbdh.balance as total_amount, c.content as package_code, pbdh.created_at as created_at, pbdh.description, pbdh.type,
        total_weight
        from partner_balance_delivery_histories pbdh
        left join (select * from codes where codeable_type = 'App\Models\Deliveries\Delivery') c on pbdh.delivery_id = c.codeable_id
        left join (select delivery_id, sum(p.total_weight) total_weight, count(*) from deliverables
        	left join packages p on deliverables.deliverable_id = p.id
        	where deliverable_type = 'App\Models\Packages\Package' group by delivery_id) d on pbdh.delivery_id = d.delivery_id
        where pbdh.partner_id = $partnerId
        union all
        select pbh.partner_id, pbh.package_id as codeable_id, pbh.balance, c2.content, pbh.created_at, pbh.description, pbh.type, p2.total_weight from partner_balance_histories pbh
        left join (select * from codes where codeable_type = 'App\Models\Packages\Package') c2 on pbh.package_id = c2.codeable_id
        left join packages p2 on c2.codeable_id = p2.id
        where pbh.partner_id = $partnerId and pbh.description = 'dooring' order by created_at desc";

        return $q;
    }

    private function getIncome($partnerId)
    {
        $q = "select pbdh.balance as amount, c.content as package_code, pbdh.created_at as date, pbdh.description, pbdh.type,
        d.total_weight as weight
        from partner_balance_delivery_histories pbdh
        left join (select * from codes where codeable_type = 'App\Models\Deliveries\Delivery') c on pbdh.delivery_id = c.codeable_id
        left join (
        	select delivery_id, sum(p.total_weight) total_weight from deliverables
        	left join packages p on deliverables.deliverable_id = p.id
			where deliverable_type = 'App\Models\Packages\Package'
        	group by delivery_id
        ) d on pbdh.delivery_id = d.delivery_id
        where pbdh.partner_id = $partnerId
        union all
        select pbh.balance, c2.content, pbh.created_at, pbh.description, pbh.type, p2.total_weight from partner_balance_histories pbh
        left join (select * from codes where codeable_type = 'App\Models\Packages\Package') c2 on pbh.package_id = c2.codeable_id
        left join packages p2 on c2.codeable_id = p2.id
        where pbh.partner_id = $partnerId and pbh.type != 'withdraw' order by date desc";

        return $q;
    }
}
