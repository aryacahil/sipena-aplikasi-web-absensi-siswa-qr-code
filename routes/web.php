<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Siswa\PresensiController;
use App\Http\Controllers\Admin\SettingsController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/home');
    }
    return redirect('/login');
});

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
]);

Route::middleware(['auth'])->group(function() {
    Route::get('/home', function() {
        $role = auth()->user()->role;
        
        if ($role == 'admin') {
            return redirect()->route('admin.home');
        } elseif ($role == 'guru') {
            return redirect()->route('guru.home');
        } elseif ($role == 'siswa') {
            return redirect()->route('siswa.home');
        }
        
        return redirect('/');
    })->name('home');
});

// ==================== ADMIN ROUTES ====================
Route::middleware(['auth', 'user-role:admin'])->group(function() {
    Route::get('/admin/home', [HomeController::class, 'adminHome'])->name('admin.home');
    
    Route::get('/admin/chart-data', [HomeController::class, 'getChartDataAjax'])->name('admin.chart-data');

    Route::post('admin/users/bulk-delete', [UserController::class, 'bulkDeleteByRole'])
        ->name('admin.users.bulk-delete');
    Route::resource('admin/users', UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);
    
    Route::resource('admin/jurusan', JurusanController::class)->names([
        'index' => 'admin.jurusan.index',
        'create' => 'admin.jurusan.create',
        'store' => 'admin.jurusan.store',
        'show' => 'admin.jurusan.show',
        'edit' => 'admin.jurusan.edit',
        'update' => 'admin.jurusan.update',
        'destroy' => 'admin.jurusan.destroy',
    ]);
    
    Route::get('admin/kelas/{kela}/available-siswa', [KelasController::class, 'availableSiswa'])
        ->name('admin.kelas.available-siswa');
    Route::post('admin/kelas/{kela}/add-siswa', [KelasController::class, 'addSiswa'])
        ->name('admin.kelas.add-siswa');
    Route::delete('admin/kelas/{kela}/remove-siswa', [KelasController::class, 'removeSiswa'])
        ->name('admin.kelas.remove-siswa');
    Route::delete('admin/kelas/{kela}/remove-all-siswa', [KelasController::class, 'removeAllSiswa'])
        ->name('admin.kelas.remove-all-siswa');
    Route::get('admin/kelas/list-all', [KelasController::class, 'listAll'])
        ->name('admin.kelas.list-all');
    Route::get('admin/kelas/all-siswa', [KelasController::class, 'allSiswa'])
        ->name('admin.kelas.all-siswa');
    Route::post('admin/kelas/pindah-siswa', [KelasController::class, 'pindahSiswa'])
        ->name('admin.kelas.pindah-siswa');
    
    Route::resource('admin/kelas', KelasController::class)->names([
        'index' => 'admin.kelas.index',
        'create' => 'admin.kelas.create',
        'store' => 'admin.kelas.store',
        'show' => 'admin.kelas.show',
        'edit' => 'admin.kelas.edit',
        'update' => 'admin.kelas.update',
        'destroy' => 'admin.kelas.destroy',
    ]);

    Route::get('admin/qrcode', [App\Http\Controllers\Admin\QRCodeController::class, 'index'])
        ->name('admin.qrcode.index');
    Route::get('admin/qrcode/create', [App\Http\Controllers\Admin\QRCodeController::class, 'create'])
        ->name('admin.qrcode.create');
    Route::post('admin/qrcode', [App\Http\Controllers\Admin\QRCodeController::class, 'store'])
        ->name('admin.qrcode.store');
    Route::get('admin/qrcode/{qrcode}', [App\Http\Controllers\Admin\QRCodeController::class, 'show'])
        ->name('admin.qrcode.show');
    Route::get('admin/qrcode/{qrcode}/download', [App\Http\Controllers\Admin\QRCodeController::class, 'download'])
        ->name('admin.qrcode.download');
    Route::patch('admin/qrcode/{qrcode}/status', [App\Http\Controllers\Admin\QRCodeController::class, 'updateStatus'])
        ->name('admin.qrcode.updateStatus');
    Route::delete('admin/qrcode/{qrcode}', [App\Http\Controllers\Admin\QRCodeController::class, 'destroy'])
        ->name('admin.qrcode.destroy');

    Route::get('admin/presensi', [App\Http\Controllers\Admin\PresensiController::class, 'index'])
        ->name('admin.presensi.index');
    Route::get('admin/presensi/kelas/{kelas}', [App\Http\Controllers\Admin\PresensiController::class, 'showKelas'])
        ->name('admin.presensi.kelas');
    Route::post('admin/presensi/kelas/{kelas}/manual', [App\Http\Controllers\Admin\PresensiController::class, 'storeManual'])
        ->name('admin.presensi.kelas.manual');
    Route::get('admin/presensi/{presensi}', [App\Http\Controllers\Admin\PresensiController::class, 'show'])
        ->name('admin.presensi.show');
    Route::get('admin/presensi/{presensi}/edit', [App\Http\Controllers\Admin\PresensiController::class, 'edit'])
        ->name('admin.presensi.edit');
    Route::put('admin/presensi/{presensi}', [App\Http\Controllers\Admin\PresensiController::class, 'update'])
        ->name('admin.presensi.update');
    Route::delete('admin/presensi/{presensi}', [App\Http\Controllers\Admin\PresensiController::class, 'destroy'])
        ->name('admin.presensi.destroy');

    Route::get('admin/export-import', [App\Http\Controllers\Admin\ExportImportController::class, 'index'])
        ->name('admin.export-import.index');
    Route::post('admin/export/siswa', [App\Http\Controllers\Admin\ExportImportController::class, 'exportSiswa'])
        ->name('admin.export.siswa');
    Route::post('admin/export/presensi', [App\Http\Controllers\Admin\ExportImportController::class, 'exportPresensi'])
        ->name('admin.export.presensi');
    Route::get('admin/download/template', [App\Http\Controllers\Admin\ExportImportController::class, 'downloadTemplate'])
        ->name('admin.download.template');
    Route::post('admin/import/siswa', [App\Http\Controllers\Admin\ExportImportController::class, 'importSiswa'])
        ->name('admin.import.siswa');

    Route::get('admin/settings/whatsapp', [App\Http\Controllers\Admin\WhatsAppController::class, 'index'])
        ->name('admin.settings.whatsapp.index');

    Route::put('admin/settings/whatsapp', [App\Http\Controllers\Admin\WhatsAppController::class, 'update'])
        ->name('admin.settings.whatsapp.update');

    Route::post('admin/settings/whatsapp/devices', [App\Http\Controllers\Admin\WhatsAppController::class, 'storeDevice'])
        ->name('admin.settings.whatsapp.devices.store');

    Route::put('admin/settings/whatsapp/devices/{device}', [App\Http\Controllers\Admin\WhatsAppController::class, 'updateDevice'])
        ->name('admin.settings.whatsapp.devices.update');

    Route::delete('admin/settings/whatsapp/devices/{id}', [App\Http\Controllers\Admin\WhatsAppController::class, 'deleteDevice'])
        ->name('admin.settings.whatsapp.devices.destroy');

    Route::post('admin/settings/whatsapp/devices/{device}/toggle', [App\Http\Controllers\Admin\WhatsAppController::class, 'toggleDevice'])
        ->name('admin.settings.whatsapp.devices.toggle');

    Route::post('admin/settings/whatsapp/devices/test-connection', [App\Http\Controllers\Admin\WhatsAppController::class, 'testDeviceConnection'])
        ->name('admin.settings.whatsapp.devices.test-connection');

    Route::post('admin/settings/whatsapp/devices/test-all', [App\Http\Controllers\Admin\WhatsAppController::class, 'testAllDevices'])
        ->name('admin.settings.whatsapp.devices.test-all');

    Route::post('admin/settings/whatsapp/test-message', [App\Http\Controllers\Admin\WhatsAppController::class, 'testMessage'])
        ->name('admin.settings.whatsapp.test-message');

    Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings/school', [SettingsController::class, 'updateSchool'])->name('admin.settings.school.update');
    
    Route::post('/admin/settings/academic-year', [SettingsController::class, 'storeAcademicYear'])->name('admin.settings.academic-year.store');
    Route::put('/admin/settings/academic-year/{academicYear}', [SettingsController::class, 'updateAcademicYear'])->name('admin.settings.academic-year.update');
    Route::delete('/admin/settings/academic-year/{academicYear}', [SettingsController::class, 'deleteAcademicYear'])->name('admin.settings.academic-year.delete');
    Route::post('/admin/settings/academic-year/{academicYear}/activate', [SettingsController::class, 'activateAcademicYear'])->name('admin.settings.academic-year.activate');
});

// ==================== GURU ROUTES ====================
Route::middleware(['auth', 'user-role:guru'])->group(function() {
    Route::get('/guru/home', [HomeController::class, 'guruHome'])->name('guru.home');
    
    Route::get('/guru/chart-data', [HomeController::class, 'getChartDataAjax'])->name('guru.chart-data');

    Route::get('guru/qrcode', [App\Http\Controllers\Guru\QRCodeController::class, 'index'])
        ->name('guru.qrcode.index');
    Route::get('guru/qrcode/create', [App\Http\Controllers\Guru\QRCodeController::class, 'create'])
        ->name('guru.qrcode.create');
    Route::post('guru/qrcode', [App\Http\Controllers\Guru\QRCodeController::class, 'store'])
        ->name('guru.qrcode.store');
    Route::get('guru/qrcode/{qrcode}', [App\Http\Controllers\Guru\QRCodeController::class, 'show'])
        ->name('guru.qrcode.show');
    Route::get('guru/qrcode/{qrcode}/download', [App\Http\Controllers\Guru\QRCodeController::class, 'download'])
        ->name('guru.qrcode.download');
    Route::patch('guru/qrcode/{qrcode}/status', [App\Http\Controllers\Guru\QRCodeController::class, 'updateStatus'])
        ->name('guru.qrcode.updateStatus');
    Route::delete('guru/qrcode/{qrcode}', [App\Http\Controllers\Guru\QRCodeController::class, 'destroy'])
        ->name('guru.qrcode.destroy');

    Route::get('guru/presensi', [App\Http\Controllers\Guru\PresensiController::class, 'index'])
        ->name('guru.presensi.index');
    Route::get('guru/presensi/kelas/{kelas}', [App\Http\Controllers\Guru\PresensiController::class, 'showKelas'])
        ->name('guru.presensi.kelas');
    Route::post('guru/presensi/kelas/{kelas}/manual', [App\Http\Controllers\Guru\PresensiController::class, 'storeManual'])
        ->name('guru.presensi.kelas.manual');
    Route::get('guru/presensi/{presensi}/edit', [App\Http\Controllers\Guru\PresensiController::class, 'edit'])
        ->name('guru.presensi.edit');
    Route::put('guru/presensi/{presensi}', [App\Http\Controllers\Guru\PresensiController::class, 'update'])
        ->name('guru.presensi.update');
    Route::delete('guru/presensi/{presensi}', [App\Http\Controllers\Guru\PresensiController::class, 'destroy'])
        ->name('guru.presensi.destroy');

    Route::get('guru/export-import', [App\Http\Controllers\Guru\ExportImportController::class, 'index'])
        ->name('guru.export-import.index');
    Route::post('guru/export/siswa', [App\Http\Controllers\Guru\ExportImportController::class, 'exportSiswa'])
        ->name('guru.export.siswa');
    Route::post('guru/export/presensi', [App\Http\Controllers\Guru\ExportImportController::class, 'exportPresensi'])
        ->name('guru.export.presensi');
    Route::get('guru/download/template', [App\Http\Controllers\Guru\ExportImportController::class, 'downloadTemplate'])
        ->name('guru.download.template');
    Route::post('guru/import/siswa', [App\Http\Controllers\Guru\ExportImportController::class, 'importSiswa'])
        ->name('guru.import.siswa');
});

// ==================== SISWA ROUTES ====================
Route::middleware(['auth', 'user-role:siswa'])->group(function() {
    Route::get('/siswa/home', [HomeController::class, 'siswaHome'])->name('siswa.home');
    
    Route::get('siswa/presensi', [\App\Http\Controllers\Siswa\PresensiController::class, 'index'])
        ->name('siswa.presensi.index');
    Route::post('siswa/presensi/validate', [\App\Http\Controllers\Siswa\PresensiController::class, 'validateQRCode'])
        ->name('siswa.presensi.validate');
    Route::post('siswa/presensi/submit', [\App\Http\Controllers\Siswa\PresensiController::class, 'submitPresensi'])
        ->name('siswa.presensi.submit');
});

Route::get('siswa/presensi/scan/{code}', function($code) {
    if (Auth::check() && Auth::user()->role == 'siswa') {
        return redirect()->route('siswa.presensi.index')
            ->with('qr_code', $code);
    }
    
    return redirect()->route('login')
        ->with('info', 'Silakan login terlebih dahulu untuk melakukan presensi');
})->name('siswa.presensi.scan');