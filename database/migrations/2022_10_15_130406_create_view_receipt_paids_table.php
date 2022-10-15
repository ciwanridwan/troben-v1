<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateViewReceiptPaidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $q = "DROP VIEW IF EXISTS view_receipt_paid";
        DB::statement($q);
        $q = "CREATE VIEW view_receipt_paid AS
            SELECT c.content AS receipt_code,
                gr2.name AS origin_city,
                gp.name AS destination_province,
                gr.name AS destination_city,
                gd.name AS destination_district,
                gsd.name AS destination_sub_district,
                gsd.zip_code,
                    CASE
                        WHEN p.transporter_type IS NULL THEN 'walk-in'::text
                        ELSE 'by apps'::text
                    END AS type_order,
                    CASE
                        WHEN p.transporter_type IS NULL THEN '-'::character varying
                        ELSE p.transporter_type
                    END AS transporter_pickup_type,
                d2.updated_at AS unloaded_at,
                p2.code AS origin_partner,
                p3.payment_ref_id AS nicepay_trx_id,
                p3.status AS nicepay_status,
                p3.confirmed_at AS payment_verified_at,
                p3.created_at AS payment_request_at,
                p.total_weight,
                COALESCE(( SELECT sum(pi3.price) AS sum
                    FROM package_items pi3
                    WHERE pi3.package_id = p.id AND pi3.is_insured = true), 0::numeric) AS item_price,
                COALESCE(( SELECT pp.amount
                    FROM package_prices pp
                    WHERE pp.package_id = p.id AND pp.type::text = 'service'::text AND pp.description::text = 'service'::text), 0::numeric) AS total_delivery_price,
                COALESCE(( SELECT pp2.amount
                    FROM package_prices pp2
                    WHERE pp2.package_id = p.id AND pp2.type::text = 'discount'::text AND pp2.description::text = 'service'::text), 0::numeric) AS discount_delivery,
                COALESCE(( SELECT calculate_extra_commission(p.total_weight, ( SELECT pp.amount
                            FROM package_prices pp
                            WHERE pp.package_id = p.id AND pp.type::text = 'service'::text AND pp.description::text = 'service'::text)) AS calculate_extra_commission), 0::numeric) AS extra_commission,
                COALESCE(( SELECT calculate_commission(p2.type, ( SELECT pp.amount
                            FROM package_prices pp
                            WHERE pp.package_id = p.id AND pp.type::text = 'service'::text AND pp.description::text = 'service'::text)) AS calculate_commission), 0::numeric) AS commission_manual,
                COALESCE((( SELECT calculate_commission(p2.type, ( SELECT pp.amount
                            FROM package_prices pp
                            WHERE pp.package_id = p.id AND pp.type::text = 'service'::text AND pp.description::text = 'service'::text)) AS calculate_commission)) - COALESCE(( SELECT pp2.amount
                    FROM package_prices pp2
                    WHERE pp2.package_id = p.id AND pp2.type::text = 'discount'::text AND pp2.description::text = 'service'::text), 0::numeric) + COALESCE(( SELECT calculate_extra_commission(p.total_weight, ( SELECT pp.amount
                            FROM package_prices pp
                            WHERE pp.package_id = p.id AND pp.type::text = 'service'::text AND pp.description::text = 'service'::text)) AS calculate_extra_commission), 0::numeric), 0::numeric) AS total_commission,
                COALESCE(( SELECT calculate_package_price_by_package_id_and_type(p.id, 'handling'::character varying) AS calculate_package_price_by_package_id_and_type), 0::numeric) AS receipt_total_packing_price,
                COALESCE(( SELECT calculate_package_price_by_package_id_and_type(p.id, 'insurance'::character varying) AS calculate_package_price_by_package_id_and_type), 0::numeric) AS receipt_insurance_price,
                COALESCE(( SELECT sum(pp3.amount) AS sum
                    FROM package_prices pp3
                    WHERE pp3.package_id = p.id AND pp3.type::text = 'delivery'::text AND pp3.description::text = 'pickup'::text), 0::numeric) AS receipt_pickup_price,
                p.total_amount AS receipt_total_amount
            FROM deliverables d
                LEFT JOIN packages p ON p.id = d.deliverable_id AND d.deliverable_type::text = 'App\Models\Packages\Package'::text
                LEFT JOIN codes c ON p.id = c.codeable_id AND c.codeable_type::text = 'App\Models\Packages\Package'::text
                LEFT JOIN deliveries d2 ON d.delivery_id = d2.id
                LEFT JOIN partners p2 ON p2.id = d2.partner_id
                JOIN payments p3 ON p.id = p3.payable_id AND p3.payable_type::text = 'App\Models\Packages\Package'::text AND p3.status::text = 'success'::text
                LEFT JOIN geo_sub_districts gsd ON p.destination_sub_district_id = gsd.id
                LEFT JOIN geo_districts gd ON gsd.district_id = gd.id
                LEFT JOIN geo_regencies gr ON gd.regency_id = gr.id
                LEFT JOIN geo_provinces gp ON gr.province_id = gp.id
                LEFT JOIN geo_regencies gr2 ON p.origin_regency_id = gr2.id
            WHERE 1=1
                AND d2.type::text = 'pickup'::text
                AND d2.status::text = 'finished'::text
                AND p.payment_status::text = 'paid'::text
            ORDER BY p3.confirmed_at";
        DB::statement($q);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $q = "DROP VIEW IF EXISTS view_receipt_paid";
        DB::statement($q);
    }
}
