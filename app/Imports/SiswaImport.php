<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new User([
            'name' => $row['nama_lengkap'],
            'email' => $row['email'],
            'password' => Hash::make($row['password'] ?? '12345678'),
            'role' => 2, // Siswa
            'kelas_id' => $row['kelas_id'] ?? null,
            'parent_phone' => $row['no_telp_orang_tua'] ?? null,
            'status' => $row['status'] ?? 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'kelas_id' => 'nullable|exists:kelas,id',
            'status' => 'in:active,inactive',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'email.unique' => 'Email :input sudah terdaftar',
            'email.required' => 'Email wajib diisi',
        ];
    }
}