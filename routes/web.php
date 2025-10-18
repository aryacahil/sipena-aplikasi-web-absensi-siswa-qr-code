<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return view('welcome');
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
    
    Route::resource('admin/users', App\Http\Controllers\Admin\UserController::class)
        ->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'show' => 'admin.users.show',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);
});

Route::middleware(['auth', 'user-role:siswa'])->group(function() {
    Route::get('/siswa/home', [HomeController::class, 'siswaHome'])->name('siswa.home');
});