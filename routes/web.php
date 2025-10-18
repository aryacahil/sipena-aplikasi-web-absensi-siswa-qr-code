<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// ========================================
// HOME ROUTE 
// ========================================
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

// ========================================
// GURU ROUTES
// ========================================
Route::middleware(['auth', 'user-role:guru'])->group(function() {
    Route::get('/guru/home', [HomeController::class, 'guruHome'])->name('guru.home');
});

// ========================================
// ADMIN ROUTES
// ========================================
Route::middleware(['auth', 'user-role:admin'])->group(function() {
    Route::get('/admin/home', [HomeController::class, 'adminHome'])->name('admin.home');
});

// ========================================
// SISWA ROUTES
// ========================================
Route::middleware(['auth', 'user-role:siswa'])->group(function() {
    Route::get('/siswa/home', [HomeController::class, 'siswaHome'])->name('siswa.home');
});