<?php

namespace App\Exports;

use App\Models\CashIncome;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashIncomeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected ?string $startDate;
    protected ?string $endDate;
    protected int $rowNumber = 0;

    public function __construct(?string $startDate = null, ?string $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = CashIncome::with('creator')->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');

        if (!empty($this->startDate)) {
            $query->whereDate('transaction_date', '>=', $this->startDate);
        }

        if (!empty($this->endDate)) {
            $query->whereDate('transaction_date', '<=', $this->endDate);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'NO',
            'NO TRANSAKSI',
            'TANGGAL TRANSAKSI',
            'PENGIRIM / SUMBER DANA',
            'BANK / REKENING ASAL',
            'NO REKENING ASAL',
            'NOMINAL POKOK (RP)',
            'BIAYA ADMIN (RP)',
            'BERSIH DITERIMA (RP)',
            'CATATAN',
            'DICATAT OLEH',
            'WAKTU INPUT',
        ];
    }

    /**
     * @param CashIncome $income
     */
    public function map($income): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $income->transaction_number,
            $income->transaction_date ? $income->transaction_date->format('d/m/Y') : '-',
            $income->sender_name ?: '-',
            $income->bank_name ?: '-',
            $income->account_number ?: '-',
            (float) $income->amount,
            (float) ($income->has_admin_fee ? $income->admin_fee : 0),
            (float) $income->net_amount,
            $income->notes ?: '-',
            $income->creator?->name ?: 'Sistem',
            $income->created_at ? $income->created_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header Styling
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '16A34A'], // Green emerald
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(26);

        // Center align No, No Trx, Tanggal
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 1) {
            $sheet->getStyle("A2:C{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E2:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L2:L{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Thin border across data
            $sheet->getStyle("A1:L{$highestRow}")->getBorders()->getAllBorders()->applyFromArray([
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['rgb' => 'D1D5DB'],
            ]);
        }

        return [];
    }
}
