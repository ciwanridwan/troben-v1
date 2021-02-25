<?php

namespace App\Concerns\Controllers;

use App\Exceptions\Error;
use App\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;

trait HasResource
{
    public function columnExcept()
    {
        $column_except = ['q'];
        if (property_exists($this, 'byRelation')) {
            foreach ($this->byRelation as $relation => $columns) {
                $column_except = array_merge($column_except, array_column($columns, 0));
            }
        };
        return $column_except;
    }

    public function getResource()
    {

        foreach (Arr::except($this->attributes,  $this->columnExcept()) as $key => $value) {
            $this->getByColumn($key);
        }
        if (Arr::has($this->attributes, 'q')) {
            $this->getSearch($this->attributes['q']);
        }

        $this->getByRelation();
    }

    public function getByRelation()
    {
        if (!property_exists($this, 'byRelation')) {
            return false;
        };

        foreach ($this->byRelation as $relation => $columns) {
            $keys = array_keys($this->attributes);
            $this->query = $this->query->whereHas($relation, function ($query) use ($columns, $keys) {

                foreach ($columns as $key => $item) {
                    $form_field = array_shift($item);
                    $column = array_shift($item);

                    if (in_array($form_field, $keys)) {
                        $query->where($column !== null ? $column : $form_field, 'LIKE', '%' . $this->attributes[$form_field] . '%');
                    }
                }
            });
        }
    }
    public function searchByRelation($q = '')
    {
        if (!property_exists($this, 'byRelation')) {
            return false;
        };

        foreach ($this->byRelation as $relation => $columns) {

            $this->query = $this->query->orWhereHas($relation, function ($query) use ($columns, $q) {

                foreach ($columns as $key => $item) {
                    $form_field = array_shift($item);
                    $column = array_shift($item);


                    if ($key == 0) {

                        $query->where($column !== null ? $column : $form_field, 'LIKE', '%' . $q . '%');
                    } else {
                        $query->orWhere($column !== null ? $column : $form_field, 'LIKE', '%' . $q . '%');
                    }
                }
            });
        }
    }

    public function getByColumn($column = ''): Builder
    {
        $this->query = $this->query->where($column, 'LIKE', '%' . $this->attributes[$column] . '%');

        return $this->query;
    }

    public function getSearch($q = '')
    {
        $columns = Arr::except($this->rules, $this->columnExcept());

        // first
        $key_first = array_key_first($columns);
        $this->query = $this->query->where($key_first, 'LIKE', '%' . $q . '%');

        foreach (Arr::except($columns, $key_first) as $key => $value) {
            $this->query = $this->query->orWhere($key, 'LIKE', '%' . $q . '%');
        }

        $this->searchByRelation($q);

        return $this->query;
    }


    public function baseBuilder()
    {
        throw_if(!property_exists($this, 'model'), Error::make(Response::RC_OTHER));
        return $this->query = $this->model::query();
    }
}
