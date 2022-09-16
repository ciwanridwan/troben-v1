<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class WithdrawalExport implements FromCollection, WithHeadings, WithColumnFormatting, WithColumnWidths, WithStyles, WithMapping
{
    use Exportable;

    protected $rowsData;

    /**
     * @var DisbursmentHistories $withdrawal
     */
    public function __construct($rowsData)
    {
        $this->rowsData = $rowsData;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection($this->rowsData);
    }

    public function map($row): array
    {
        return [
            $row->no,
            $row->receipt,
            $row->amount,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Resi',
            'Total Penerimaan',
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
            'A' => 10,
            'B' => 25,
            'C' => 20,
        ];
    }

    public function styles(WorkSheet $sheet)
    {
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A:C')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }
}
