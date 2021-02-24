<?php

namespace App\Concerns\Controllers;

use App\Exceptions\Error;
use App\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;

trait HasResource
{
    public function getByColumn($column = ''): Builder
    {
        $this->query = $this->query->where($column, 'LIKE', '%' . $this->attributes[$column] . '%');

        return $this->query;
    }

    public function getSearch($q = '')
    {
        $columns = Arr::except($this->rules, 'q');

        // first
        $key_first = array_key_first($columns);
        $this->query = $this->query->where($key_first, 'LIKE', '%' . $q . '%');

        foreach (Arr::except($columns, $key_first) as $key => $value) {
            $this->query = $this->query->orWhere($key, 'LIKE', '%' . $q . '%');
        }

        return $this->query;
    }


    public function baseBuilder()
    {
        throw_if(!property_exists($this, 'model'), Error::make(Response::RC_OTHER));
        return $this->query = $this->model::query();
    }
}
