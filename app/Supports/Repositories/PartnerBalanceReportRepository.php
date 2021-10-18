<?php

namespace App\Supports\Repositories;

use App\Models\Partners\Partner;
use App\Models\View\PartnerBalanceReport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PartnerBalanceReportRepository
{
    /**
     * Data attributes.
     *
     * @var array $attributes
     */
    protected array $attributes;

    /**
     * Payment report query.
     *
     * @var Builder partnerBalanceReportQuery
     */
    protected Builder $partnerBalanceReportQuery;

    /**
     * @param array $attributes
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = Validator::make($attributes, [
            'group.*' => ['nullable', Rule::in([
                'created_at_year',
                'created_at_month',
                'created_at_day',
                'partner_code',
                'partner_type',
                'partner_geo_regency',
                'partner_name',
                'package_id',
                'package_code',
                'package_created_at',
            ])],
            'sortBy' => ['nullable', Rule::in([
                'created_at_day',
                'balance',
            ])],
            'sort' => ['nullable', Rule::in([
                'asc','desc'
            ])],
            'q' => 'string|nullable',
            'is_today' => 'boolean|nullable',
            'day' => 'int|nullable',
            'month' => 'int|nullable',
            'year' => 'int|nullable',
            'partner_type' => ['string','nullable', Rule::in(Partner::getAvailableTypes())],
            'partner_id' => 'int|nullable',
            'detail' => 'boolean|nullable',
            'is_package_created' => 'boolean|nullable',
            'start_date' => 'nullable',
            'end_date' => 'nullable',
            'description' => 'string|nullable',
        ])->validate();
    }

    /**
     * Make query for payment report.
     *
     * @return Builder
     */
    public function getQuery(): Builder
    {
        $this->partnerBalanceReportQuery = PartnerBalanceReport::query();
        $this->selectColumnByDetail();

        $this->partnerBalanceReportQuery->when(Arr::has($this->attributes,'q'), fn ($q) => $q
            ->where('partner_geo_regency','ilike','%'.$this->attributes['q'].'%')
            ->orWhere('partner_code','ilike','%'.$this->attributes['q'].'%')
            ->orWhere('partner_name','ilike','%'.$this->attributes['q'].'%')
        );

        $this->partnerBalanceReportQuery->when(Arr::has($this->attributes,'year'), fn ($q) => $q
            ->where('created_at_year',$this->attributes['year']));

        $this->partnerBalanceReportQuery->when(Arr::has($this->attributes,'month'), fn ($q) => $q
            ->where('created_at_month',$this->attributes['month']));

        $this->partnerBalanceReportQuery->when(Arr::has($this->attributes,'day'), fn ($q) => $q
            ->where('created_at_day',$this->attributes['day']));

        $this->partnerBalanceReportQuery->when(Arr::get($this->attributes,'is_today',false), fn ($q) => $q
            ->where('created_at_day',Carbon::today()->day));

        $this->partnerBalanceReportQuery->when(Arr::has($this->attributes,'partner_type'), fn ($q) => $q
            ->where('partner_type',$this->attributes['partner_type']));

        $this->partnerBalanceReportQuery->when(Arr::has($this->attributes,'partner_id'), fn ($q) => $q
            ->where('partner_id',$this->attributes['partner_id']));

        $this->partnerBalanceReportQuery->when(
            (Arr::has($this->attributes,'start_date') &&
            Arr::has($this->attributes,'end_date')), function ($q) {
                $column = Arr::get($this->attributes, 'is_package_created', false) ? 'package_created_at' : 'history_created_at';
                $q
                    ->where($column, '>=', Carbon::parse($this->attributes['start_date'])->startOfDay()->format('Y-m-d H:i:s'))
                    ->where($column, '<', Carbon::parse($this->attributes['end_date'])->endOfDay()->format('Y-m-d H:i:s'));
            });

        $this->partnerBalanceReportQuery->when(Arr::has($this->attributes,'description'), fn ($q) => $q
            ->where('description',$this->attributes['description']));

        $this->partnerBalanceReportQuery->when(Arr::has($this->attributes,'group'), fn ($q) => $q
            ->groupBy($this->attributes['group']));

        $this->partnerBalanceReportQuery->when(Arr::has($this->attributes,'sortBy'), fn ($q) => $q
            ->orderBy($this->attributes['sortBy'], Arr::get($this->attributes,'sort','asc')));

        return $this->partnerBalanceReportQuery;
    }

    /**
     * Validate selected column by flag detail
     */
    public function selectColumnByDetail(): void
    {
        if (Arr::get($this->attributes, 'detail', false)) $this->partnerBalanceReportQuery
            ->select(['partner_code', 'partner_name', 'partner_geo_regency'])
            ->selectRaw('sum(balance) as balance');
        elseif (!Arr::has($this->attributes, 'group')) $this->partnerBalanceReportQuery
            ->select();
        else $this->partnerBalanceReportQuery
            ->select($this->attributes['group'])
            ->selectRaw('sum(balance) as balance');
    }
}
