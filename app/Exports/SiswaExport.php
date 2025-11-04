<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = User::where('role', 2)->with('kelas.jurusan');

        // Apply filters
        if (!empty($this->filters['kelas_id'])) {
            $query->where('kelas_id', $this->filters['kelas_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Lengkap',
            'Email',
            'Kelas',
            'Jurusan',
            'No. Telp Orang Tua',
            'Status',
            'Tanggal Terdaftar',
        ];
    }

    public function map($siswa): array
    {
        return [
            $siswa->id,
            $siswa->name,
            $siswa->email,
            $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
            $siswa->kelas ? $siswa->kelas->jurusan->nama_jurusan : '-',
            $siswa->parent_phone ?? '-',
            ucfirst($siswa->status),
            $siswa->created_at->format('d-m-Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}