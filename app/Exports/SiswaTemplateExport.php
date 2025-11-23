<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        // Contoh data
        return [
            ['Ahmad Fauzi', 'ahmad@example.com', '12345678', 'X DKV 1', '081234567890', 'active'],
            ['Siti Nurhaliza', 'siti@example.com', '12345678', 'X DKV 2', '081234567891', 'active'],
        ];
    }

    public function headings(): array
    {
        return [
            'nama_lengkap',
            'email',
            'password',
            'nama_kelas',
            'no_telp_orang_tua',
            'status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFFF00']]],
        ];
    }
}