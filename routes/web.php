<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Siswa\PresensiController;

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
    
    Route::resource('admin/kelas', KelasController::class)->names([
        'index' => 'admin.kelas.index',
        'create' => 'admin.kelas.create',
        'store' => 'admin.kelas.store',
        'show' => 'admin.kelas.show',
        'edit' => 'admin.kelas.edit',
        'update' => 'admin.kelas.update',
        'destroy' => 'admin.kelas.destroy',
    ]);

    // QR CODE ROUTES
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

    // PRESENSI ROUTES
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

    // Export & Import
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
});

// ==================== GURU ROUTES ====================
Route::middleware(['auth', 'user-role:guru'])->group(function() {
    Route::get('/guru/home', [HomeController::class, 'guruHome'])->name('guru.home');

    // QR CODE ROUTES GURU
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

    // PRESENSI ROUTES GURU
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

    // Export & Import GURU
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
    
    // Halaman Scanner QR (tanpa parameter)
    Route::get('siswa/presensi', [PresensiController::class, 'index'])
        ->name('siswa.presensi.index');
    
    // Submit Presensi setelah scan
    Route::post('siswa/presensi/submit', [PresensiController::class, 'submitPresensi'])
        ->name('siswa.presensi.submit');
});

// Route untuk validasi QR Code (diakses oleh scanner)
Route::get('siswa/presensi/scan/{code}', function($code) {
    return response()->json([
        'code' => $code,
        'timestamp' => now()->toIso8601String()
    ]);
})->name('siswa.presensi.scan');

Route::get('/test-qr-format', function() {
    $sessions = App\Models\PresensiSession::with('kelas')
        ->latest()
        ->take(5)
        ->get();
    
    return response()->json([
        'message' => 'Format QR Code yang ada di database',
        'count' => $sessions->count(),
        'sessions' => $sessions->map(function($session) {
            return [
                'id' => $session->id,
                'kelas' => $session->kelas->nama_kelas ?? 'N/A',
                'qr_code' => $session->qr_code,
                'qr_code_length' => strlen($session->qr_code),
                'status' => $session->status,
                'full_url' => route('siswa.presensi.scan', $session->qr_code),
                'created_at' => $session->created_at->format('Y-m-d H:i:s'),
            ];
        })
    ]);
})->middleware('auth');

// TEST ROUTE - SIMULATE SCAN
Route::post('/test-scan-qr', function(Illuminate\Http\Request $request) {
    $qrCode = $request->qr_code;
    
    // Cari session
    $session = App\Models\PresensiSession::where('qr_code', $qrCode)->first();
    
    $allSessions = App\Models\PresensiSession::select('id', 'qr_code', 'kelas_id', 'status')
        ->get()
        ->map(function($s) use ($qrCode) {
            return [
                'id' => $s->id,
                'qr_code' => $s->qr_code,
                'match' => $s->qr_code === $qrCode,
                'qr_length' => strlen($s->qr_code),
                'search_length' => strlen($qrCode),
                'status' => $s->status,
            ];
        });
    
    return response()->json([
        'searched_qr' => $qrCode,
        'qr_length' => strlen($qrCode),
        'found' => $session ? true : false,
        'session' => $session ? [
            'id' => $session->id,
            'qr_code' => $session->qr_code,
            'kelas_id' => $session->kelas_id,
            'status' => $session->status,
        ] : null,
        'all_sessions' => $allSessions
    ]);
})->middleware('auth');