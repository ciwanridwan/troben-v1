<?php

namespace App\Concerns\Models;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Database\Eloquent\Model;

trait HasPhoneNumber
{
    /**
     * Register trait boot.
     */
    public static function bootHasPhoneNumber()
    {
        /**
         * @var self|\Illuminate\Database\Eloquent\Model $model
         */
        self::creating(function (Model $model) {
            $model->assignPhoneAttribute();
        });
        self::saving(function (Model $model) {
            $model->assignPhoneAttribute();
        });
    }

    /**
     * Set `phone` number attribute mutator.
     *
     * @param $value
     *
     * @throws \libphonenumber\NumberParseException
     */
    public function assignPhoneAttribute($value = null): void
    {
        $util = PhoneNumberUtil::getInstance();
        $value = $value ?: $this->{$this->getPhoneNumberColumn()};

        $this->attributes[$this->getPhoneNumberColumn()] = $util->format($util->parse($value, 'ID'), PhoneNumberFormat::E164);
    }

    /**
     * Get phone number column on the model.
     *
     * @return string
     */
    protected function getPhoneNumberColumn(): string
    {
        return property_exists($this, 'phoneNumberColumn')
            ? $this->phoneNumberColumn
            : 'phone';
    }
}
