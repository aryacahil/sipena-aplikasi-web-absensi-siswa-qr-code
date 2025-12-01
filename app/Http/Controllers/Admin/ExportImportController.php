<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\SiswaExport;
use App\Exports\PresensiExport;
use App\Exports\SiswaTemplateExport;
use App\Imports\SiswaImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Kelas;

class ExportImportController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('jurusan')->get();
        return view('admin.export-import.index', compact('kelas'));
    }

    // Export Siswa
    public function exportSiswa(Request $request)
    {
        $filters = $request->only(['kelas_id', 'status']);
        $filename = 'data-siswa-' . date('Y-m-d-His') . '.xlsx';
        
        return Excel::download(new SiswaExport($filters), $filename);
    }

    // Export Presensi
    public function exportPresensi(Request $request)
    {
        $filters = $request->only(['kelas_id', 'tanggal_mulai', 'tanggal_akhir', 'status']);
        $filename = 'data-presensi-' . date('Y-m-d-His') . '.xlsx';
        
        return Excel::download(new PresensiExport($filters), $filename);
    }

    // Download Template Import Siswa
    public function downloadTemplate()
    {
        return Excel::download(new SiswaTemplateExport, 'template-import-siswa.xlsx');
    }

    // Import Siswa
    public function importSiswa(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            Excel::import(new SiswaImport, $request->file('file'));
            
            return redirect()->back()->with('success', 'Data siswa berhasil diimport!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            return redirect()->back()->with('error', 'Gagal import: ' . implode(' | ', $errorMessages));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}