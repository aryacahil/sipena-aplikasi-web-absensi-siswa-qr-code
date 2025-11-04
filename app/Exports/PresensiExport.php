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
            'Waktu',
            'Status',
            'Metode',
            'Keterangan',
        ];
    }

    public function map($presensi): array
    {
        return [
            $presensi->id,
            $presensi->siswa->name,
            $presensi->kelas->nama_kelas,
            $presensi->kelas->jurusan->nama_jurusan,
            $presensi->tanggal_presensi->format('d-m-Y'),
            $presensi->created_at->format('H:i:s'),
            strtoupper($presensi->status),
            strtoupper($presensi->metode),
            $presensi->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}