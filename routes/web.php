<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\QRCodeController;
use App\Http\Controllers\Guru\PresensiController;
use App\Http\Controllers\Siswa\ScanController;

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

Route::middleware(['auth', 'user-role:guru'])->group(function() {
    Route::get('/guru/home', [HomeController::class, 'guruHome'])->name('guru.home');
});

Route::middleware(['auth', 'user-role:admin'])->group(function() {
    Route::get('/admin/home', [HomeController::class, 'adminHome'])->name('admin.home');
    Route::post('admin/users/bulk-delete', [UserController::class, 'bulkDeleteByRole'])
        ->name('admin.users.bulk-delete');
    
    Route::resource('admin/users', UserController::class)
        ->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'show' => 'admin.users.show',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);
    
    Route::resource('admin/jurusan', JurusanController::class)
        ->names([
            'index' => 'admin.jurusan.index',
            'create' => 'admin.jurusan.create',
            'store' => 'admin.jurusan.store',
            'show' => 'admin.jurusan.show',
            'edit' => 'admin.jurusan.edit',
            'update' => 'admin.jurusan.update',
            'destroy' => 'admin.jurusan.destroy',
        ]);
    
    Route::resource('admin/kelas', KelasController::class)
        ->names([
            'index' => 'admin.kelas.index',
            'create' => 'admin.kelas.create',
            'store' => 'admin.kelas.store',
            'show' => 'admin.kelas.show',
            'edit' => 'admin.kelas.edit',
            'update' => 'admin.kelas.update',
            'destroy' => 'admin.kelas.destroy',
        ]);
});

Route::middleware(['auth', 'user-role:siswa'])->group(function() {
    Route::get('/siswa/home', [HomeController::class, 'siswaHome'])->name('siswa.home');
});

Route::middleware(['auth', 'user-role:admin'])->group(function() {
    Route::prefix('admin')->name('admin.')->group(function() {
        Route::resource('qrcode', QRCodeController::class);
        Route::get('qrcode/{session}/download', [QRCodeController::class, 'downloadQr'])
            ->name('qrcode.download');
    });
});

Route::middleware(['auth', 'user-role:guru'])->group(function() {
    Route::prefix('guru')->name('guru.')->group(function() {
        Route::get('qrcode', [PresensiController::class, 'index'])->name('qrcode.index');
        Route::post('qrcode', [PresensiController::class, 'store'])->name('qrcode.store');
        Route::get('qrcode/{id}', [PresensiController::class, 'show'])->name('qrcode.show');
        Route::delete('qrcode/{id}', [PresensiController::class, 'destroy'])->name('qrcode.destroy');
        
        Route::post('qrcode/{session}/close', [PresensiController::class, 'close'])->name('qrcode.close');
        Route::post('qrcode/{session}/reopen', [PresensiController::class, 'reopen'])->name('qrcode.reopen');
        Route::get('qrcode/{session}/download-qr', [PresensiController::class, 'downloadQr'])->name('qrcode.download-qr');
        Route::post('qrcode/{session}/absen-manual', [PresensiController::class, 'absenManual'])->name('qrcode.absen-manual');
        Route::put('qrcode/{session}/update/{presensi}', [PresensiController::class, 'updatePresensi'])->name('qrcode.update-presensi');
        Route::delete('qrcode/{session}/delete/{presensi}', [PresensiController::class, 'deletePresensi'])->name('qrcode.delete-presensi');
    });
});

Route::middleware(['auth', 'user-role:siswa'])->group(function() {
    Route::prefix('siswa')->name('siswa.')->group(function() {
        Route::get('presensi/scan', [\App\Http\Controllers\Siswa\ScanController::class, 'scan'])
            ->name('presensi.scan');
        Route::get('presensi/verify/{code}', [\App\Http\Controllers\Siswa\ScanController::class, 'verifyForm'])
            ->name('presensi.verify-form');
        Route::post('presensi/verify', [\App\Http\Controllers\Siswa\ScanController::class, 'verify'])
            ->name('presensi.verify');
        Route::get('presensi/history', [\App\Http\Controllers\Siswa\ScanController::class, 'history'])
            ->name('presensi.history');
    });
});