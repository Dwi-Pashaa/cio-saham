<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected ?string $type;
    protected ?string $ownerType;
    protected ?string $startDate;
    protected ?string $endDate;
    protected int $rowNumber = 0;

    public function __construct(
        ?string $type = null,
        ?string $ownerType = null,
        ?string $startDate = null,
        ?string $endDate = null
    ) {
        $this->type = $type;
        $this->ownerType = $ownerType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Asset::with(['shareholder', 'creator'])->orderBy('id', 'desc');

        if (!empty($this->type)) {
            $query->where('type', $this->type);
        }

        if (!empty($this->ownerType)) {
            $query->where('owner_type', $this->ownerType);
        }

        if (!empty($this->startDate)) {
            $query->whereDate('purchase_date', '>=', $this->startDate);
        }

        if (!empty($this->endDate)) {
            $query->whereDate('purchase_date', '<=', $this->endDate);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA ASET / PERANGKAT',
            'KATEGORI ASET',
            'NILAI / HARGA ASET (RP)',
            'SERIAL NUMBER (SN)',
            'MAC ADDRESS',
            'KEPEMILIKAN',
            'INVESTOR PEMILIK',
            'TANGGAL PEROLEHAN',
            'CATATAN / SPESIFIKASI',
            'DICATAT OLEH',
            'WAKTU INPUT',
        ];
    }

    /**
     * @param Asset $asset
     */
    public function map($asset): array
    {
        $this->rowNumber++;

        $ownership = $asset->owner_type === 'pt' ? 'PT CIO NETWORK SOLUTION' : 'Pemegang Saham';
        $investorName = $asset->owner_type === 'shareholder'
            ? ($asset->shareholder?->name ?: ($asset->owner_name ?: '-'))
            : '-';

        return [
            $this->rowNumber,
            $asset->name,
            $asset->type,
            (float) $asset->price,
            $asset->serial_number ?: '-',
            $asset->mac_address ?: '-',
            $ownership,
            $investorName,
            $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-',
            $asset->notes ?: '-',
            $asset->creator?->name ?: 'Sistem',
            $asset->created_at ? $asset->created_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
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
                'startColor' => ['rgb' => '206BC4'], // Tabler primary blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(26);

        // Center align No, Kategori, SN, MAC, Tgl
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 1) {
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C2:C{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E2:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
