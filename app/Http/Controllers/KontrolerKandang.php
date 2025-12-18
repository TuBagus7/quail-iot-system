<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ModelKandang; // Pake nama model yang baru ya Bang
use Illuminate\Http\Request;

class KontrolerKandang extends Controller
{
    // Halaman utama buat publik (liat dashboard real-time)
    public function index()
    {
        // Tetep ambil data buat jaga-jaga kalau mau ditampilin list-nya
        $semua_data = ModelKandang::all();
        return view('publik.tampilan_utama', compact('semua_data'));
    }

    // Halaman khusus admin buat liat semua data (overview)
    public function adminIndex()
    {
        $semua_data = ModelKandang::orderBy('created_at', 'desc')->get();
        return view('admin.beranda_admin', compact('semua_data'));
    }

    // Form buat nambah data baru (khusus admin)
    public function create()
    {
        return view('admin.tambah_data');
    }

    // Fungsi buat simpan data ke database
    public function store(Request $request)
    {
        $validasi = $request->validate([
            'gauge1' => 'required|numeric',
            'gauge2' => 'required|numeric',
            'gauge3' => 'required|numeric',
            'gauge4' => 'required|numeric',
            'item_teks' => 'required|string',
            'status_buzzer' => 'required|boolean',
        ]);

        ModelKandang::create($validasi);

        return redirect()->route('admin.crud.index')->with('sukses', 'Data berhasil ditambah, mantap!');
    }

    // Form buat edit data yang udah ada
    public function edit($id)
    {
        $item = ModelKandang::findOrFail($id);
        return view('admin.ubah_data', compact('item'));
    }

    // Fungsi buat update data
    public function update(Request $request, $id)
    {
        $validasi = $request->validate([
            'gauge1' => 'required|numeric',
            'gauge2' => 'required|numeric',
            'gauge3' => 'required|numeric',
            'gauge4' => 'required|numeric',
            'item_teks' => 'required|string',
            'status_buzzer' => 'required|boolean',
        ]);

        $item = ModelKandang::findOrFail($id);
        $item->update($validasi);

        return redirect()->route('admin.crud.index')->with('sukses', 'Data udah diupdate ya!');
    }

    // Fungsi buat hapus data
    public function destroy($id)
    {
        $item = ModelKandang::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.crud.index')->with('sukses', 'Data udah dihapus, bersih!');
    }
}
