<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;

class DisbursmentExport implements FromQuery
{
    use Exportable;

    /**
    * @return \Illuminate\Database\Builder\Query
    */
    public function query()
    {
        return "SELECT p.code AS partner_name, 
        b.name AS bank_name, 
        pbd.account_number AS bank_number, 
        dh.receipt,
        weight,
        pp.amount AS pickup_fee,
        packing_fee, 
        insurance_fee,
        pp5.amount * 0.3 AS partner_fee,
        pp4.amount AS discount_fee
        FROM disbursment_histories dh
        LEFT JOIN partner_balance_disbursement pbd ON dh.disbursment_id = pbd.id
        LEFT JOIN partners p ON pbd.partner_id = p.id
        LEFT JOIN bank b ON pbd.bank_id = b.id
        LEFT JOIN codes c ON dh.receipt = c.content
        LEFT JOIN (	SELECT pi2.package_id, SUM(pi2.weight) AS weight 
                    FROM package_items pi2 WHERE weight notnull GROUP BY 1) pi2 
                    ON pi2.package_id = c.codeable_id
        LEFT JOIN (	SELECT pp.package_id, pp.amount 
                    FROM package_prices pp WHERE type = 'delivery' AND description = 'pickup')pp 
                    ON pp.package_id = c.codeable_id
        LEFT JOIN (	SELECT pp2.package_id, SUM(pp2.amount) AS packing_fee 
                    FROM package_prices pp2 WHERE type = 'handling' GROUP BY 1) pp2 
                    ON pp2.package_id = c.codeable_id
        LEFT JOIN (	SELECT pp3.package_id, SUM(pp3.amount) AS insurance_fee 
                    FROM package_prices pp3 WHERE type = 'insurance' AND description = 'insurance' GROUP BY 1) pp3
                    ON pp3.package_id = c.codeable_id
        LEFT JOIN (	SELECT pp4.package_id, pp4.amount 
                    FROM package_prices pp4 WHERE type = 'discount') pp4 
                    ON pp4.package_id = c.codeable_id
        LEFT JOIN ( SELECT pp5.package_id, pp5.amount FROM package_prices pp5 WHERE type = 'service' AND description = 'service') pp5
                    ON pp5.package_id  = c.codeable_id";
    }
}
