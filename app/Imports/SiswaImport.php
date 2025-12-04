<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $kelas = Kelas::where('nama_kelas', $row['nama_kelas'])->first();

        return new User([
            'nis'           => (string) $row['nis'], 
            'name'          => $row['nama_lengkap'],
            'password'      => Hash::make($row['password'] ?? '12345678'),
            'role'          => 2,
            'kelas_id'      => $kelas->id ?? null,
            'parent_phone'  => $row['no_telp_orang_tua'] ?? null,
            'status'        => $row['status'] ?? 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'nis'               => 'required|unique:users,nis',
            'nama_lengkap'      => 'required|string|max:255',
            'nama_kelas'        => 'required|string|max:255',
            'status'            => 'in:active,inactive',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nis.required'      => 'NIS wajib diisi',
            'nis.unique'        => 'NIS :input sudah terdaftar',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'nama_kelas.required' => 'Nama kelas wajib diisi',
        ];
    }
}