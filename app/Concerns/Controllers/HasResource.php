<?php

namespace App\Concerns\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

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
}
