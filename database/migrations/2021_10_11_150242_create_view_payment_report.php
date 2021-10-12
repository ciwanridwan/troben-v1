<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewPaymentReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->dropView());
    }

    private function createView(): string
    {
        return <<<SQL
        create or replace view view_payment_report as
        select
            pbh.id,
            pbh.partner_id,
            p.code as partner_code,
            p.name as partner_name,
            p."type" as partner_type,
            p.geo_regency_id as partner_geo_regency_id,
            gr.name as partner_geo_regency,
            pbh.package_id,
            c."content" as package_code,
            pbh.disbursement_id,
            pbh.balance,
            pbh."type",
            pbh.description,
            extract(day from pbh.created_at)::int as "created_at_day",
            extract(month from pbh.created_at)::int as "created_at_month",
            extract(year from pbh.created_at)::int as "created_at_year",
            pbh.created_at
        from
            partner_balance_histories pbh
        left join codes c on
            pbh.package_id = c.codeable_id
            and c.codeable_type like '%Package'
        join partners p on
            pbh.partner_id = p.id
        left join geo_regencies gr on
            p.geo_regency_id = gr.id
        order by pbh.id desc;
        SQL;
    }

    private function dropView(): string
    {
        return <<<SQL
        drop view if exists view_payment_report;
        SQL;
    }
}
