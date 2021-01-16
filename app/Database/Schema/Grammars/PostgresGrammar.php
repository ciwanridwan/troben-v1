<?php

namespace App\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\PostgresGrammar as BasePostgresGrammar;
use Illuminate\Support\Fluent;

class PostgresGrammar extends BasePostgresGrammar
{
    protected function typePoint(Fluent $column)
    {
        return "point";
    }

    protected function typeMultiPoint(Fluent $column)
    {
        return "multipoint";
    }

    protected function typePolygon(Fluent $column)
    {
        return "polygon";
    }

    protected function typeMultiPolygon(Fluent $column)
    {
        return "multipolygon";
    }
}
