<?php

namespace App\Exports;

use App\Models\Presensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PresensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Presensi::with(['siswa', 'kelas.jurusan']);

        if (!empty($this->filters['kelas_id'])) {
            $query->where('kelas_id', $this->filters['kelas_id']);
        }

        if (!empty($this->filters['tanggal_mulai'])) {
            $query->whereDate('tanggal_presensi', '>=', $this->filters['tanggal_mulai']);
        }

        if (!empty($this->filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_presensi', '<=', $this->filters['tanggal_akhir']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('tanggal_presensi', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'nis',
            'nama_siswa',
            'nama_kelas',
            'nama_jurusan',
            'tanggal_presensi',
            'waktu_checkin',
            'waktu_checkout',
            'status',
            'metode',
        ];
    }

    public function map($presensi): array
    {
        return [
            $presensi->id,
            $presensi->siswa->nis ?? '',
            $presensi->siswa->name,
            $presensi->kelas->nama_kelas,
            $presensi->kelas->jurusan->nama_jurusan,
            $presensi->tanggal_presensi->format('Y-m-d'),
            $presensi->waktu_checkin ? $presensi->waktu_checkin->format('H:i:s') : '',
            $presensi->waktu_checkout ? $presensi->waktu_checkout->format('H:i:s') : '',
            $presensi->status,
            $presensi->metode,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4299E1']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ],
        ];
    }
}