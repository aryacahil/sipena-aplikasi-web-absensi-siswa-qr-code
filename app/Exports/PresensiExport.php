<?php

namespace App\Exports;

use App\Models\Presensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PresensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Presensi::with(['siswa', 'kelas.jurusan']);

        // Apply filters
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
            'ID',
            'Nama Siswa',
            'Kelas',
            'Jurusan',
            'Tanggal',
            'Waktu Check-in',     // FIXED: Split waktu
            'Waktu Check-out',    // FIXED: Tambah checkout
            'Status',
            'Metode',
            'Keterangan',
        ];
    }

    public function map($presensi): array
    {
        // FIXED: Gabungkan keterangan checkin dan checkout
        $keterangan = [];
        if ($presensi->keterangan_checkin) {
            $keterangan[] = 'Checkin: ' . $presensi->keterangan_checkin;
        }
        if ($presensi->keterangan_checkout) {
            $keterangan[] = 'Checkout: ' . $presensi->keterangan_checkout;
        }
        $keteranganText = !empty($keterangan) ? implode(' | ', $keterangan) : '-';

        return [
            $presensi->id,
            $presensi->siswa->name,
            $presensi->kelas->nama_kelas,
            $presensi->kelas->jurusan->nama_jurusan,
            $presensi->tanggal_presensi->format('d-m-Y'),
            // FIXED: Tampilkan waktu checkin dan checkout terpisah
            $presensi->waktu_checkin ? $presensi->waktu_checkin->format('H:i:s') : '-',
            $presensi->waktu_checkout ? $presensi->waktu_checkout->format('H:i:s') : '-',
            strtoupper($presensi->status),
            strtoupper($presensi->metode),
            $keteranganText,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
        ];
    }
}