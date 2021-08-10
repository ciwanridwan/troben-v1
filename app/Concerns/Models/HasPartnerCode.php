<?php

namespace App\Concerns\Models;

use App\Models\Geo\Regency;
use App\Models\Partners\Partner;

/**
 *
 */
trait HasPartnerCode
{
    public static function bootHasPartnerCode()
    {
        self::creating(function ($model) {
            if (empty($model->{$model->getCodeColumn()})) {
                $model->{$model->getCodeColumn()} = $model->generateCode();
            }
        });
    }
    public function getCodeColumn()
    {
        return property_exists($this, 'codeColumn') ? $this->codeColumn : 'code';
    }

    public function generateCode()
    {
        $partner_code = Partner::getAvailableCodeTypes()[$this->type];
        /** @var Regency $partner_regency */
        $partner_regency = Regency::find($this->geo_regency_id);

        $code = $partner_code.'-'.($partner_regency->bsn_code === 'KYB' ? 'JKT' : $partner_regency->bsn_code).'-';

        $last_code = Partner::where('code', 'LIKE', $code.'%')->latest()->first();
        if ($last_code === null) {
            $code = $code.str_pad(0, 4, '0', STR_PAD_LEFT);
            $code_number = (int) substr($last_code, strlen($code));
        } else {
            $last_code = $last_code->code;
            $code_number = (int) substr($last_code, strlen($code));
            $code_number += 1;
            $code_number = str_pad($code_number, 5, '0', STR_PAD_LEFT);
        }
        $code .= $code_number;

        return $code;
    }
}
