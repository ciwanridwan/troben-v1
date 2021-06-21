<?php

namespace App\Concerns\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

trait CanSearch
{
    use AttributeColumns;
    protected $search_value = '';



    public function scopeSearch(Builder $builder, $search_value = '', $search_columns = [])
    {
        $this->search_value = $search_value;
        if ($search_columns === []) {
            if (!property_exists($this, 'search_columns')) {
                $this->search_columns = $this->getTableSearchColumns();
            }
        } else {
            $this->search_columns = Arr::wrap($search_columns);
        }

        foreach ($this->search_columns as $key => $value) {
            if ($key != 0) {
                $builder->orWhere($this->table . '.' . $value, 'ilike', '%' . $this->search_value . '%');
            } else {
                $builder->where($this->table . '.' . $value, 'ilike', '%' . $this->search_value . '%');
            }
        }
    }
}
