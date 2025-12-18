<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemDashboard;
use Illuminate\Http\Request;

class KontrolerDashboard extends Controller
{
    // Halaman utama buat user liat dashboard
    public function index()
    {
        $semua_data = ItemDashboard::all();
        return view('dashboard', compact('semua_data'));
    }

    // Halaman khusus admin buat liat semua data (overview)
    public function adminIndex()
    {
        $semua_data = ItemDashboard::all();
        return view('admin.dashboard', compact('semua_data'));
    }

    // Form buat nambah data baru
    public function create()
    {
        return view('dashboard.tambah');
    }

    // Fungsi buat simpan data ke database
    public function store(Request $request)
    {
        $validasi = $request->validate([
            'gauge1' => 'required|numeric',
            'gauge2' => 'required|numeric',
            'gauge3' => 'required|numeric',
            'gauge4' => 'required|numeric',
            'gauge5' => 'required|numeric',
            'item_teks' => 'required|string',
            'status_buzzer' => 'required|boolean',
        ]);

        ItemDashboard::create($validasi);

        return redirect()->route('dashboard.index')->with('sukses', 'Data berhasil ditambah, mantap!');
    }

    // Form buat edit data yang udah ada
    public function edit($id)
    {
        $item = ItemDashboard::findOrFail($id);
        return view('dashboard.edit', compact('item'));
    }

    // Fungsi buat update data
    public function update(Request $request, $id)
    {
        $validasi = $request->validate([
            'gauge1' => 'required|numeric',
            'gauge2' => 'required|numeric',
            'gauge3' => 'required|numeric',
            'gauge4' => 'required|numeric',
            'gauge5' => 'required|numeric',
            'item_teks' => 'required|string',
            'status_buzzer' => 'required|boolean',
        ]);

        $item = ItemDashboard::findOrFail($id);
        $item->update($validasi);

        return redirect()->route('dashboard.index')->with('sukses', 'Data udah diupdate ya!');
    }

    // Fungsi buat hapus data
    public function destroy($id)
    {
        $item = ItemDashboard::findOrFail($id);
        $item->delete();

        return redirect()->route('dashboard.index')->with('sukses', 'Data udah dihapus, bersih!');
    }
}
