<?php

use Illuminate\Support\Facades\Route;

// Rute buat masuk & keluar (Login/Logout)
Route::get('masuk', [App\Http\Controllers\KontrolerAkses::class, 'showLogin'])->name('masuk');
Route::post('masuk', [App\Http\Controllers\KontrolerAkses::class, 'login'])->name('masuk.post');
Route::post('keluar', [App\Http\Controllers\KontrolerAkses::class, 'logout'])->name('keluar');

// Halaman Utama: Publik (Bisa diliat siapa aja tanpa login)
Route::get('/', [App\Http\Controllers\KontrolerKandang::class, 'index'])->name('tampilan_utama');

// Area Admin: Harus Login dulu ya Bang!
Route::middleware('auth')->group(function () {
    // Rute CRUD Admin (Buat ngelola data kandang)
    Route::resource('admin/kelola-data', App\Http\Controllers\KontrolerKandang::class)->names([
        'index'   => 'admin.crud.index',
        'create'  => 'admin.crud.create',
        'store'   => 'admin.crud.store',
        'edit'    => 'admin.crud.edit',
        'update'  => 'admin.crud.update',
        'destroy' => 'admin.crud.destroy',
    ]);

    // Halaman Beranda Admin
    Route::get('admin/beranda', [App\Http\Controllers\KontrolerKandang::class, 'adminIndex'])->name('admin.dashboard');
});
