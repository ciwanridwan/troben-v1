<?php

namespace App\Exports;

use App\Models\Partners\Balance\DisbursmentHistory;
use App\Models\Payments\Withdrawal;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Style;


class WithdrawalExport implements FromQuery, WithHeadings, WithColumnFormatting, WithColumnWidths, WithStyles
{
    use Exportable;

    /**
    * @var DisbursmentHistories $withdrawal
    */

    public function headings(): array
    {
        return [
            'No',
            'Resi',
            'Amount',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'B' => 25,
            'C' => 15,            
        ];
    }

    public function styles(WorkSheet $sheet)
    {
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A:C')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }

    public function query()
    {
        return DisbursmentHistory::query()->select('id', 'receipt', 'amount');
    } 
}
