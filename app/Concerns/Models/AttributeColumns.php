<?php

namespace App\Concerns\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

trait AttributeColumns
{


    public function getTableSearchColumns()
    {
        $columns = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());

        if (property_exists($this, 'hidden')) {
            $columns = array_diff($columns, $this->hidden);
        }

        if (property_exists($this, 'appends')) {
            $columns = array_merge($columns, $this->appends);
        }
        return $columns;
    }

    public function getColumnType($column)
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnType($this->getTable(), $column);
    }

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
