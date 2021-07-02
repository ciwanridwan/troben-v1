<?php

namespace App\Supports\Translates;

use App\Models\Code;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Supports\Translates\Delivery as TranslatesDelivery;
use App\Supports\Translates\Package as TranslatesPackage;
use Illuminate\Database\Eloquent\Model;

class Translate
{

    public Model $model;

    function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function translate()
    {
        $this->model = $this->model instanceof Code ? $this->model->codeable : $this->model;
        return $this->resolveTranslateByInstance();
    }
    protected function resolveTranslateByInstance()
    {
        switch (true) {
            case $this->model instanceof Package:
                return (new TranslatesPackage($this->model))->translate();
                break;
            case $this->model instanceof Delivery:
                return (new TranslatesDelivery($this->model))->translate();
                break;
            default:
                return '';
        }
    }
}
