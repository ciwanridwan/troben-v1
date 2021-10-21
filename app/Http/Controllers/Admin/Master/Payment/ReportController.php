<?php

namespace App\Http\Controllers\Admin\Master\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Master\Payment\Report\GraphResource;
use App\Http\Resources\Admin\Master\Payment\Report\PartnerBalanceDetailResource;
use App\Http\Resources\Admin\Master\Payment\Report\PartnerSummaryResource;
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
            'regency_id' => [Rule::when($this->type === self::DATA_TYPE_GRAPH, 'nullable|int')]
        ])->validate();

        $query = (new PartnerBalanceReportRepository($request->except('type')))->getQuery();
        $data = $query->get();

        if ($this->type === self::DATA_TYPE_SUMMARY) return $this->jsonSuccess(PartnerSummaryResource::make($this->getSummaryData()));
        #TODO: make resource detail (partner type, q, date, sortby income)
        if ($this->type === self::DATA_TYPE_DETAIL) return $this->jsonSuccess(PartnerBalanceDetailResource::collection($data));

        #TODO: make resource graph (year/month/day)
        return $this->jsonSuccess(GraphResource::make($this->getGraphData()));
    }

    /**
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function getSummaryData(): array
    {
        $inputsNow = array();
        $inputsLast = array();
        $inputsData = [
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

                $subDate = $date->subDay();
                $inputsLast = Arr::prepend($inputsLast, $subDate->day, 'day');
                $inputsLast = Arr::prepend($inputsLast, $subDate->month, 'month');
                $inputsLast = Arr::prepend($inputsLast, $subDate->year, 'year');

                $inputsData = Arr::prepend($inputsData, $date->day, 'day');
                $inputsData = Arr::prepend($inputsData, $date->month, 'month');
                $inputsData = Arr::prepend($inputsData, $date->year, 'year');
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

                $subMonth = $date->subMonth();
                $inputsLast = Arr::prepend($inputsLast, $subMonth->month, 'month');
                $inputsLast = Arr::prepend($inputsLast, $subMonth->year, 'year');

                $inputsData = Arr::prepend($inputsData, $date->month, 'month');
                $inputsData = Arr::prepend($inputsData, $date->year, 'year');
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
        $inputsData = ['group' => ['created_at_day']];

        if (empty($this->attributes['date'])) $inputsData = Arr::prepend($inputsData,true,'is_this_month');
        else {
            $date = Carbon::parse($this->attributes['date']);
            $inputsData = Arr::prepend($inputsData, $date->month, 'month');
            $inputsData = Arr::prepend($inputsData, $date->year, 'year');
        }

        if (! empty($this->attributes['regency_id'])) $inputsData = Arr::prepend($inputsData, $this->attributes['regency_id'], 'partner_geo_regency_id');

        $data = (new PartnerBalanceReportRepository($inputsData))->getQuery()->get();

        return [
            'date' => $this->attributes['date'],
            'data' => $data,
        ];
    }
}
