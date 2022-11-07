<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\Owner\Balance\DetailResource;
use App\Http\Resources\Api\Partner\Owner\Balance\ReportPartnerTransporterResource;
use App\Http\Resources\Api\Partner\Owner\Balance\ReportResource;
use App\Http\Resources\Api\Partner\Owner\Balance\SummaryResource;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerBalanceReportRepository;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
                $query = $this->getMtakIncome($repository->getPartner()->id);
                $result = DB::select($query);

                return $this->jsonSuccess(ReportPartnerTransporterResource::collection($result));
                break;
            default:
                $this->query = (new PartnerBalanceReportRepository($inputs))->getQuery();

                $this->query->with('balanceHistories', fn ($q) => $q->where('partner_id', $repository->getPartner()->id));
                return $this->jsonSuccess(ReportResource::collection($this->query->orderBy('package_created_at', 'desc')->paginate($request->input('per_page', 10))));
                break;

                // $query = $this->getIncome($repository->getPartner()->id);
                // $result = collect(DB::select($query));
                // $resultArr = $result->map(function ($k) { 
                //     return $k->package_code;
                // });
                // $code = collect(DB::select($query))->pluck('package_code')->values()->toArray();
                // $filterArr = array_filter($code, function ($q) use ($resultArr) {
                //     // dd($q == $resultArr);
                //     return $q == $resultArr[0];
                // });
                // dd($filterArr);
                // return $this->jsonSuccess(ReportResource::collection($result));
                // break;
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

    /** Get Income MTAK By Query
     *  Delivery &
     * Dooring Income
     */
    private function getMtakIncome($partnerId)
    {
        $q = "select pbdh.partner_id, pbdh.delivery_id as codeable_id, pbdh.balance as total_amount, c.content as package_code, pbdh.created_at, pbdh.description, pbdh.type,
        sum(p.total_weight) as total_weight
        from partner_balance_delivery_histories pbdh
        left join (select * from codes where codeable_type = 'App\Models\Deliveries\Delivery') c on pbdh.delivery_id = c.codeable_id
        left join (select * from deliverables where deliverable_type = 'App\Models\Packages\Package') d on pbdh.delivery_id = d.delivery_id
        left join packages p on d.deliverable_id = p.id
        where pbdh.partner_id = $partnerId group by pbdh.partner_id, pbdh.delivery_id, pbdh.balance, c.content, pbdh.created_at, pbdh.description, pbdh.type
        union all
        select pbh.partner_id, pbh.package_id as codeable_id, pbh.balance, c2.content, pbh.created_at, pbh.description, pbh.type, p2.total_weight from partner_balance_histories pbh
        left join (select * from codes where codeable_type = 'App\Models\Packages\Package') c2 on pbh.package_id = c2.codeable_id
        left join packages p2 on c2.codeable_id = p2.id
        where pbh.partner_id = $partnerId ";

        return $q;
    }

    private function getIncome($partnerId)
    {
        $q = "select pbdh.partner_id, pbdh.delivery_id as codeable_id, pbdh.balance as total_amount, c.content as package_code, pbdh.created_at, pbdh.description, pbdh.type,
        sum(p.total_weight) as total_weight
        from partner_balance_delivery_histories pbdh
        left join (select * from codes where codeable_type = 'App\Models\Deliveries\Delivery') c on pbdh.delivery_id = c.codeable_id
        left join (select * from deliverables where deliverable_type = 'App\Models\Packages\Package') d on pbdh.delivery_id = d.delivery_id
        left join packages p on d.deliverable_id = p.id
        where pbdh.partner_id = $partnerId group by pbdh.partner_id, pbdh.delivery_id, pbdh.balance, c.content, pbdh.created_at, pbdh.description, pbdh.type
        union all
        select pbh.partner_id, pbh.package_id as codeable_id, pbh.balance, c2.content, pbh.created_at, pbh.description, pbh.type, p2.total_weight from partner_balance_histories pbh
        left join (select * from codes where codeable_type = 'App\Models\Packages\Package') c2 on pbh.package_id = c2.codeable_id
        left join packages p2 on c2.codeable_id = p2.id
        where pbh.partner_id = $partnerId and pbh.type != 'withdraw'";

        return $q;
    }
}
