<?php

namespace App\Exports;

use App\Models\Partners\Balance\DisbursmentHistory;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Sum;

class DisbursmentExport implements FromQuery, WithHeadings, WithColumnWidths, WithStyles
{
    use Exportable;


    public function headings(): array
    {
        return [
            'Nama Mitra',
            'Nama Bank',
            'No Rekening',
            'No Resi',
            'Berat',
            'Biaya Jemput',
            'Biaya Packing',
            'Biaya Asuransi',
            'Fee Mitra',
            'Diskon',
            'Total',
            'Fee Mitra Tambahan'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 25,
            'C' => 25,
            'D' => 25,
            'E' => 25,
            'F' => 25,            
            'G' => 25,
            'H' => 25,
            'I' => 25,
            'J' => 25,
            'K' => 25,
            'L' => 25,
        ];
    }

    public function styles(WorkSheet $sheet)
    {
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A:L')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }

    /**
    * @return \Illuminate\Database\Builder\Query
    */
    public function query()
    {
        $items = DB::table('package_items')->select('package_id', DB::raw('SUM(weight) as weight'))->whereNotNull('weight')->groupBy('package_id');

        $pickupFee = DB::table('package_prices')->select('package_id', 'amount')->where('type', 'delivery')->where('description', 'pickup');
        
        $packingFee = DB::table('package_prices')->select('package_id', DB::raw('SUM(amount) as packing_fee'))->where('type', 'handling')->whereNotNull('amount')->groupBy('package_id');
        
        $insuranceFee = DB::table('package_prices')->select('package_id', DB::raw('SUM(amount) as insurance_fee'))->where('type', 'insurance')->whereNotNull('amount')->groupBy('package_id');
        
        $commisionPartner = DB::table('package_prices')->select('package_id', DB::raw('amount * 0.3 as partner_fee'))->where('type', 'service');
        
        $discount = DB::table('package_prices')->select('package_id', 'amount')->where('type', 'discount')->whereNotNull('amount');

        $total = DB::table('package_prices')->select('package_id', DB::raw('SUM(amount) as total'))->whereIn('type', ['delivery', 'handling', 'insurance'])->groupBy('package_id');

        return DisbursmentHistory::query()
        ->select('p.code', 'b.name', 'pbd.account_number', 'disbursment_histories.receipt', 'pi2.weight', 'pp.amount as pickup_fee', 'pp2.packing_fee', 'pp3.insurance_fee', 'pp4.partner_fee', 'pp5.amount as discount', 'pp6.total')
        ->leftJoin('partner_balance_disbursement as pbd', 'disbursment_histories.disbursment_id', '=', 'pbd.id')
        ->leftJoin('partners as p', 'pbd.partner_id', '=', 'p.id')
        ->leftJoin('bank as b', 'pbd.bank_id', '=', 'b.id')
        ->leftJoin('codes as c', 'disbursment_histories.receipt', '=', 'c.content')
        ->leftJoinSub($items, 'pi2', function ($join) {
            $join->on('c.codeable_id', '=', 'pi2.package_id');
        })
        ->leftJoinSub($pickupFee, 'pp', function ($join) {
            $join->on('c.codeable_id', '=', 'pp.package_id');
        })
        ->leftJoinSub($packingFee, 'pp2', function ($join) {
            $join->on('c.codeable_id', '=', 'pp2.package_id');
        })
        ->leftJoinSub($insuranceFee, 'pp3', function ($join) {
            $join->on('c.codeable_id', '=', 'pp3.package_id');
        })
        ->leftJoinSub($commisionPartner, 'pp4', function ($join) {
            $join->on('c.codeable_id', '=', 'pp4.package_id');
        })
        ->leftJoinSub($discount, 'pp5', function ($join) {
            $join->on('c.codeable_id', '=', 'pp5.package_id');
        })
        ->leftJoinSub($total, 'pp6', function ($join) {
            $join->on('c.codeable_id', '=', 'pp6.package_id');
        });
    }
}
