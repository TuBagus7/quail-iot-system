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

    /**
     * API: Simpan Data dari ESP32 atau Web Dashboard
     * Fungsi ini bakal nerima kiriman JSON dan langsung masukin ke DB
     */
    public function simpanDataDariAlat(Request $request)
    {
        // Kita bikin fleksibel ya Bang, bisa nerima nama 'gauge' atau nama sensor langsung
        $data_kiriman = $request->all();

        // Mapping dari nama sensor ke kolom database
        $payload = [
            'gauge1' => $data_kiriman['gauge1'] ?? $data_kiriman['suhu'] ?? 0,
            'gauge2' => $data_kiriman['gauge2'] ?? $data_kiriman['volume'] ?? 0,
            'gauge3' => $data_kiriman['gauge3'] ?? $data_kiriman['kekeruhan'] ?? 0,
            'gauge4' => $data_kiriman['gauge4'] ?? $data_kiriman['kualitas'] ?? 0,
            'item_teks' => $data_kiriman['item_teks'] ?? $data_kiriman['status'] ?? 'Cek Kandang',
            'status_buzzer' => $data_kiriman['status_buzzer'] ?? ($request->has('status_buzzer') ? $data_kiriman['status_buzzer'] : 0),
        ];

        // Validasi tipis-tipis biar datanya bener angka
        if (!is_numeric($payload['gauge1'])) $payload['gauge1'] = 0;
        if (!is_numeric($payload['gauge2'])) $payload['gauge2'] = 0;
        if (!is_numeric($payload['gauge3'])) $payload['gauge3'] = 0;
        if (!is_numeric($payload['gauge4'])) $payload['gauge4'] = 0;

        // Langsung hajar masukin ke database!
        $data = ModelKandang::create($payload);

        return response()->json([
            'pesan' => 'Mantap, data udah kesimpen di database! 🚀',
            'data_id' => $data->id
        ], 201);
    }
}
