<?php

namespace App\Concerns\Models;

trait AttributeColumns
{

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
