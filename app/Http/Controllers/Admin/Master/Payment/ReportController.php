<?php

namespace App\Http\Controllers\Admin\Master\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Master\Payment\Report\GraphResource;
use App\Http\Resources\Admin\Master\Payment\Report\PartnerBalanceDetailResource;
use App\Http\Resources\Admin\Master\Payment\Report\PartnerSummaryResource;
use App\Models\Partners\Balance\History;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerBalanceReportRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    private const DATA_TYPE_GRAPH = 'graph';
    private const DATA_TYPE_SUMMARY = 'summary';
    private const DATA_TYPE_DETAIL = 'detail';

    private const SUMMARY_DAILY = 'daily';
    private const SUMMARY_MONTHLY = 'monthly';

    private const DETAIL_SORT_BALANCE = 'balance';
    private const DETAIL_SORT_REGENCY = 'partner_geo_regency';
    private const DETAIL_SORT_PROVINCE = 'partner_geo_province';

    /**
     * Possible value for handle validation request.
     *
     * @var array|string[] $possibleTypeData
     */
    protected array $possibleTypeData = [
        self::DATA_TYPE_GRAPH,
        self::DATA_TYPE_SUMMARY,
        self::DATA_TYPE_DETAIL,
    ];

    protected array $possibleDetailSortData = [
        self::DETAIL_SORT_BALANCE,
        self::DETAIL_SORT_PROVINCE,
        self::DETAIL_SORT_REGENCY,
    ];

    /**
     * Attributes data.
     *
     * @var array $attributes
     */
    protected array $attributes;

    /**
     * Flag type.
     *
     * @var string $type
     */
    protected string $type;

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function data(Request $request): JsonResponse
    {
        $this->type = Arr::get(Validator::make($request->only('type'),[
            'type' => ['nullable','string',Rule::in($this->possibleTypeData)],
        ])->validate(),'type',self::DATA_TYPE_GRAPH);

        $this->attributes = Validator::make($request->all(),[
            'date' => ['nullable'],
            'summary_type' => [Rule::requiredIf($this->type === self::DATA_TYPE_SUMMARY), Rule::in([
                self::SUMMARY_DAILY,
                self::SUMMARY_MONTHLY,
            ])],
            'province_id' => [Rule::when($this->type === self::DATA_TYPE_GRAPH, 'nullable|int')],
            'regency_id' => [Rule::when($this->type === self::DATA_TYPE_GRAPH, 'nullable|int')],
            'partner_type' => [Rule::when($this->type === self::DATA_TYPE_DETAIL, ['required','string', Rule::in(Partner::getAvailableTypes())])],
            'q' => [Rule::when($this->type === self::DATA_TYPE_DETAIL, ['nullable','string'])],
            'sortBy' => [Rule::when($this->type === self::DATA_TYPE_DETAIL,['nullable','string',Rule::in($this->possibleDetailSortData)])],
            'sort' => [Rule::when($this->type === self::DATA_TYPE_DETAIL,['nullable','string',Rule::in(['asc','desc'])])],
        ])->validate();


        if ($this->type === self::DATA_TYPE_SUMMARY) return $this->jsonSuccess(PartnerSummaryResource::make($this->getSummaryData()));

        if ($this->type === self::DATA_TYPE_DETAIL) return $this->jsonSuccess(PartnerBalanceDetailResource::collection($this->getDetailData()));

        return $this->jsonSuccess(GraphResource::make($this->getGraphData()));
    }

    /**
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function getSummaryData(): array
    {
        $inputsNow = [
            'type' => History::TYPE_DEPOSIT,
        ];
        $inputsLast = [
            'type' => History::TYPE_DEPOSIT,
        ];
        $inputsData = [
            'type' => History::TYPE_DEPOSIT,
            'group' => ['partner_type']
        ];

        if ($this->attributes['summary_type'] === self::SUMMARY_DAILY) {
            if (empty($this->attributes['date'])) {
                $inputsNow = Arr::prepend($inputsNow, true, 'is_today');
                $inputsLast = Arr::prepend($inputsLast, true, 'is_yesterday');
                $inputsData = Arr::prepend($inputsData,true,'is_today');
            } else {
                $date = Carbon::parse($this->attributes['date']);
                $inputsNow = Arr::prepend($inputsNow, $date->day, 'day');
                $inputsNow = Arr::prepend($inputsNow, $date->month, 'month');
                $inputsNow = Arr::prepend($inputsNow, $date->year, 'year');

                $inputsData = Arr::prepend($inputsData, $date->day, 'day');
                $inputsData = Arr::prepend($inputsData, $date->month, 'month');
                $inputsData = Arr::prepend($inputsData, $date->year, 'year');

                $subDate = $date->subDay();
                $inputsLast = Arr::prepend($inputsLast, $subDate->day, 'day');
                $inputsLast = Arr::prepend($inputsLast, $subDate->month, 'month');
                $inputsLast = Arr::prepend($inputsLast, $subDate->year, 'year');
            }
        } else {
            if (empty($this->attributes['date'])) {
                $inputsNow = Arr::prepend($inputsNow, true, 'is_this_month');
                $inputsLast = Arr::prepend($inputsLast, true, 'is_sub_month');
                $inputsData = Arr::prepend($inputsData, true, 'is_this_month');
            } else {
                $date = Carbon::parse($this->attributes['date']);
                $inputsNow = Arr::prepend($inputsNow, $date->month, 'month');
                $inputsNow = Arr::prepend($inputsNow, $date->year, 'year');

                $inputsData = Arr::prepend($inputsData, $date->month, 'month');
                $inputsData = Arr::prepend($inputsData, $date->year, 'year');

                $subMonth = $date->subMonth();
                $inputsLast = Arr::prepend($inputsLast, $subMonth->month, 'month');
                $inputsLast = Arr::prepend($inputsLast, $subMonth->year, 'year');
            }
        }

        $incomeNow = (new PartnerBalanceReportRepository($inputsNow))->getQuery()->sum('balance');

        $incomeLast = (new PartnerBalanceReportRepository($inputsLast))->getQuery()->sum('balance');

        $data = (new PartnerBalanceReportRepository($inputsData))->getQuery()->get();

        return [
            'income_now' => $incomeNow,
            'income_sub' => $incomeLast,
            'data' => $data,
        ];
    }

    /**
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function getGraphData(): array
    {
        $inputsData = [
            'type' => History::TYPE_DEPOSIT,
            'group' => ['created_at_day']
        ];

        if (empty($this->attributes['date'])) $inputsData = Arr::prepend($inputsData,true,'is_this_month');
        else {
            $date = Carbon::parse($this->attributes['date']);
            $inputsData = Arr::prepend($inputsData, $date->month, 'month');
            $inputsData = Arr::prepend($inputsData, $date->year, 'year');
        }

        if (! empty($this->attributes['province_id'])) $inputsData = Arr::prepend($inputsData, $this->attributes['province_id'], 'partner_geo_province_id');
        if (! empty($this->attributes['regency_id'])) $inputsData = Arr::prepend($inputsData, $this->attributes['regency_id'], 'partner_geo_regency_id');

        $data = (new PartnerBalanceReportRepository($inputsData))->getQuery()->get();

        return [
            'date' => $this->attributes['date'] ?? Carbon::now()->format('Y-m-d'),
            'data' => $data,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function getDetailData()
    {
        $inputsData = [
            'type' => History::TYPE_DEPOSIT,
            'group' => ['partner_code','partner_name','partner_geo_regency','partner_geo_province'],
            'detail' => true,
        ];

        if (empty($this->attributes['date'])) {
            $inputsData = Arr::prepend($inputsData,true,'is_today');
        } else {
            $date = Carbon::parse($this->attributes['date']);
            $inputsData = Arr::prepend($inputsData, $date->day, 'day');
            $inputsData = Arr::prepend($inputsData, $date->month, 'month');
            $inputsData = Arr::prepend($inputsData, $date->year, 'year');
        }

        return (new PartnerBalanceReportRepository(array_merge(Arr::except($this->attributes,'date'),$inputsData)))->getQuery()->get();
    }
}
