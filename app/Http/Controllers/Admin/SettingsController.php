<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $setting = SchoolSetting::first();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        
        return view('admin.settings.index', compact('setting', 'academicYears'));
    }

    public function updateSchool(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $setting = SchoolSetting::first() ?? new SchoolSetting();
        
        if ($request->hasFile('logo')) {
            if ($setting->logo_path && file_exists(public_path($setting->logo_path))) {
                unlink(public_path($setting->logo_path));
            }
            
            $file = $request->file('logo');
            $filename = 'logo_sekolah.' . $file->getClientOriginalExtension();
            $path = 'admin_assets/images/brand/logo/';
            
            if (!file_exists(public_path($path))) {
                mkdir(public_path($path), 0755, true);
            }
            
            $file->move(public_path($path), $filename);
            $validated['logo_path'] = $path . $filename;
        }

        $setting->fill($validated);
        $setting->save();

        return redirect()->back()->with('success', 'Pengaturan sekolah berhasil diperbarui');
    }

    public function storeAcademicYear(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|string|unique:academic_years,year',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean'
        ]);

        if ($request->is_active) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        AcademicYear::create($validated);

        return redirect()->back()->with('success', 'Tahun pelajaran berhasil ditambahkan');
    }

    public function updateAcademicYear(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'year' => 'required|string|unique:academic_years,year,' . $academicYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean'
        ]);

        if ($request->is_active) {
            AcademicYear::where('id', '!=', $academicYear->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $academicYear->update($validated);

        return redirect()->back()->with('success', 'Tahun pelajaran berhasil diperbarui');
    }

    public function deleteAcademicYear(AcademicYear $academicYear)
    {
        if ($academicYear->is_active) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus tahun pelajaran yang sedang aktif');
        }

        $academicYear->delete();

        return redirect()->back()->with('success', 'Tahun pelajaran berhasil dihapus');
    }

    public function activateAcademicYear(AcademicYear $academicYear)
    {
        AcademicYear::where('is_active', true)->update(['is_active' => false]);
        $academicYear->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Tahun pelajaran berhasil diaktifkan');
    }
}