<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard.index');
});

// Rute CRUD Dashboard (Gue lepas middleware auth dulu biar lu bisa langsung liat ya!)
Route::resource('dashboard', App\Http\Controllers\KontrolerDashboard::class);

// Tampilan dashboard admin
Route::get('admin/dashboard', [App\Http\Controllers\KontrolerDashboard::class, 'adminIndex'])
    ->name('admin.dashboard');
    // ->middleware('can:admin'); // Gue matiin dulu sementara biar gak ribet pas ngetes
