<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\KelasController;

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

// ============================================
// ADMIN ROUTES
// ============================================
Route::middleware(['auth', 'user-role:admin'])->group(function() {
    Route::get('/admin/home', [HomeController::class, 'adminHome'])->name('admin.home');
    
    // Users Management
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
    
    // Jurusan Management
    Route::resource('admin/jurusan', JurusanController::class)->names([
        'index' => 'admin.jurusan.index',
        'create' => 'admin.jurusan.create',
        'store' => 'admin.jurusan.store',
        'show' => 'admin.jurusan.show',
        'edit' => 'admin.jurusan.edit',
        'update' => 'admin.jurusan.update',
        'destroy' => 'admin.jurusan.destroy',
    ]);
    
    // Kelas Management
    Route::get('admin/kelas/{kela}/available-siswa', [KelasController::class, 'availableSiswa'])
        ->name('admin.kelas.available-siswa');
    Route::post('admin/kelas/{kela}/add-siswa', [KelasController::class, 'addSiswa'])
        ->name('admin.kelas.add-siswa');
    Route::delete('admin/kelas/{kela}/remove-siswa', [KelasController::class, 'removeSiswa'])
        ->name('admin.kelas.remove-siswa');
    Route::delete('admin/kelas/{kela}/remove-all-siswa', [KelasController::class, 'removeAllSiswa'])
        ->name('admin.kelas.remove-all-siswa');
    
    Route::resource('admin/kelas', KelasController::class)->names([
        'index' => 'admin.kelas.index',
        'create' => 'admin.kelas.create',
        'store' => 'admin.kelas.store',
        'show' => 'admin.kelas.show',
        'edit' => 'admin.kelas.edit',
        'update' => 'admin.kelas.update',
        'destroy' => 'admin.kelas.destroy',
    ]);

    // ============================================
    // QR Code Management
    // ============================================
    Route::get('admin/qrcode', [App\Http\Controllers\Admin\QRCodeController::class, 'index'])->name('admin.qrcode.index');
    Route::get('admin/qrcode/create', [App\Http\Controllers\Admin\QRCodeController::class, 'create'])->name('admin.qrcode.create');
    Route::post('admin/qrcode', [App\Http\Controllers\Admin\QRCodeController::class, 'store'])->name('admin.qrcode.store');
    Route::get('admin/qrcode/{qrcode}', [App\Http\Controllers\Admin\QRCodeController::class, 'show'])->name('admin.qrcode.show');
    Route::get('admin/qrcode/{qrcode}/download', [App\Http\Controllers\Admin\QRCodeController::class, 'download'])->name('admin.qrcode.download');
    Route::patch('admin/qrcode/{qrcode}/status', [App\Http\Controllers\Admin\QRCodeController::class, 'updateStatus'])->name('admin.qrcode.updateStatus');
    Route::delete('admin/qrcode/{qrcode}', [App\Http\Controllers\Admin\QRCodeController::class, 'destroy'])->name('admin.qrcode.destroy');

    // ============================================
    // Presensi Management (UPDATED - NEW SYSTEM WITH MANUAL INPUT)
    // ============================================
    // Main route - Daftar Kelas
    Route::get('admin/presensi', [App\Http\Controllers\Admin\PresensiController::class, 'index'])->name('admin.presensi.index');
    
    // Show Kelas - Detail presensi per kelas (support AJAX/JSON)
    Route::get('admin/presensi/kelas/{kelas}', [App\Http\Controllers\Admin\PresensiController::class, 'showKelas'])->name('admin.presensi.kelas');
    
    // CRUD Presensi
    Route::get('admin/presensi/{presensi}', [App\Http\Controllers\Admin\PresensiController::class, 'show'])->name('admin.presensi.show');
    Route::get('admin/presensi/{presensi}/edit', [App\Http\Controllers\Admin\PresensiController::class, 'edit'])->name('admin.presensi.edit');
    Route::put('admin/presensi/{presensi}', [App\Http\Controllers\Admin\PresensiController::class, 'update'])->name('admin.presensi.update');
    Route::delete('admin/presensi/{presensi}', [App\Http\Controllers\Admin\PresensiController::class, 'destroy'])->name('admin.presensi.destroy');
    
    // Session based routes
    Route::get('admin/presensi/session/{session}/create', [App\Http\Controllers\Admin\PresensiController::class, 'create'])->name('admin.presensi.create');
    Route::post('admin/presensi/session/{session}', [App\Http\Controllers\Admin\PresensiController::class, 'store'])->name('admin.presensi.store');
    
    // NEW: Manual Input Presensi (From Modal)
    Route::post('admin/presensi/session/{session}/manual', [App\Http\Controllers\Admin\PresensiController::class, 'storeManual'])->name('admin.presensi.manual');
    
    Route::post('admin/presensi/session/{session}/bulk', [App\Http\Controllers\Admin\PresensiController::class, 'bulkCreate'])->name('admin.presensi.bulkCreate');
});

// ============================================
// GURU ROUTES
// ============================================
Route::middleware(['auth', 'user-role:guru'])->group(function() {
    Route::get('/guru/home', [HomeController::class, 'guruHome'])->name('guru.home');

    // QR Code Management 
    Route::get('guru/qrcode', [App\Http\Controllers\Guru\QRCodeController::class, 'index'])->name('guru.qrcode.index');
    Route::get('guru/qrcode/create', [App\Http\Controllers\Guru\QRCodeController::class, 'create'])->name('guru.qrcode.create');
    Route::post('guru/qrcode', [App\Http\Controllers\Guru\QRCodeController::class, 'store'])->name('guru.qrcode.store');
    Route::get('guru/qrcode/{qrcode}', [App\Http\Controllers\Guru\QRCodeController::class, 'show'])->name('guru.qrcode.show');
    Route::get('guru/qrcode/{qrcode}/download', [App\Http\Controllers\Guru\QRCodeController::class, 'download'])->name('guru.qrcode.download');
    Route::patch('guru/qrcode/{qrcode}/status', [App\Http\Controllers\Guru\QRCodeController::class, 'updateStatus'])->name('guru.qrcode.updateStatus');
    Route::delete('guru/qrcode/{qrcode}', [App\Http\Controllers\Guru\QRCodeController::class, 'destroy'])->name('guru.qrcode.destroy');

    // Presensi Management (UPDATED - NEW SYSTEM WITH MANUAL INPUT)
    // Main route - Daftar Kelas
    // Route::get('guru/presensi', [App\Http\Controllers\Guru\PresensiController::class, 'index'])->name('guru.presensi.index');
    
    // // Show Kelas - Detail presensi per kelas (support AJAX/JSON)
    // Route::get('guru/presensi/kelas/{kelas}', [App\Http\Controllers\Guru\PresensiController::class, 'showKelas'])->name('guru.presensi.kelas');
    
    // // CRUD Presensi
    // Route::get('guru/presensi/{presensi}', [App\Http\Controllers\Guru\PresensiController::class, 'show'])->name('guru.presensi.show');
    // Route::get('guru/presensi/{presensi}/edit', [App\Http\Controllers\Guru\PresensiController::class, 'edit'])->name('guru.presensi.edit');
    // Route::put('guru/presensi/{presensi}', [App\Http\Controllers\Guru\PresensiController::class, 'update'])->name('guru.presensi.update');
    // Route::delete('guru/presensi/{presensi}', [App\Http\Controllers\Guru\PresensiController::class, 'destroy'])->name('guru.presensi.destroy');
    
    // // Session based routes
    // Route::get('guru/presensi/session/{session}/create', [App\Http\Controllers\Guru\PresensiController::class, 'create'])->name('guru.presensi.create');
    // Route::post('guru/presensi/session/{session}', [App\Http\Controllers\Guru\PresensiController::class, 'store'])->name('guru.presensi.store');
    
    // // NEW: Manual Input Presensi (From Modal)
    // Route::post('guru/presensi/session/{session}/manual', [App\Http\Controllers\Guru\PresensiController::class, 'storeManual'])->name('guru.presensi.manual');
    
    // Route::post('guru/presensi/session/{session}/bulk', [App\Http\Controllers\Guru\PresensiController::class, 'bulkCreate'])->name('guru.presensi.bulkCreate');
});

// ============================================
// SISWA ROUTES
// ============================================
Route::middleware(['auth', 'user-role:siswa'])->group(function() {
    Route::get('/siswa/home', [HomeController::class, 'siswaHome'])->name('siswa.home');
    
    // Presensi - Scan QR Code
    #Route::get('siswa/presensi/scan/{code}', [App\Http\Controllers\Siswa\PresensiController::class, 'scan'])->name('siswa.presensi.scan');
    #Route::post('siswa/presensi/submit', [App\Http\Controllers\Siswa\PresensiController::class, 'submit'])->name('siswa.presensi.submit');
    
    // Presensi - History
    #Route::get('siswa/presensi', [App\Http\Controllers\Siswa\PresensiController::class, 'index'])->name('siswa.presensi.index');
    #Route::get('siswa/presensi/{presensi}', [App\Http\Controllers\Siswa\PresensiController::class, 'show'])->name('siswa.presensi.show');
});