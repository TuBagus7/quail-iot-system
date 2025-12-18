<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KontrolerAkses extends Controller
{
    // Tampilin halaman login
    public function showLogin()
    {
        // Kalau udah login, ya langsung lempar ke admin aja, ngapain login lagi
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.halaman_masuk');
    }

    // Proses pas tombol login diklik
    public function login(Request $request)
    {
        // Validasi inputan si admin
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Cek akunnya bener apa kaga di database
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // Biar aman dari pembajakan sesi
            return redirect()->intended(route('admin.dashboard')); // Lempar ke halaman yang tadi dia mau buka
        }

        // Kalau gagal, balik lagi sambil bawa pesan sedih
        return back()->withErrors([
            'email' => 'Waduh, email atau password-nya salah nih Bang. Cek lagi ya!',
        ])->onlyInput('email');
    }

    // Fungsi buat logout kalau udah kelar urusan
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tampilan_utama');
    }
}
