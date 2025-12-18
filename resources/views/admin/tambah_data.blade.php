<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data - Dashboard</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-900 text-white min-h-screen font-sans flex items-center justify-center py-12">
    <div class="bg-slate-800 p-8 rounded-3xl border border-slate-700 shadow-2xl w-full max-w-xl">
        <h1 class="text-2xl font-bold mb-6 text-blue-400">Tambah Data Monitoring Baru</h1>
        
        <form action="{{ route('admin.crud.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Suhu (°C)</label>
                    <input type="number" step="0.01" name="gauge1" value="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Volume Air (ml)</label>
                    <input type="number" step="0.01" name="gauge2" value="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Kekeruhan Air (NTU)</label>
                    <input type="number" step="0.01" name="gauge3" value="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Kualitas Air (%)</label>
                    <input type="number" step="0.01" name="gauge4" value="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">Item Teks</label>
                <input type="text" name="item_teks" placeholder="Status aman, bro..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-400 mb-3">Status Buzzer</label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status_buzzer" value="0" checked class="w-4 h-4 text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-600">
                        <span class="text-slate-300">MATI</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status_buzzer" value="1" class="w-4 h-4 text-red-600 bg-slate-900 border-slate-700 focus:ring-red-600">
                        <span class="text-slate-300">NYALA</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 flex gap-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-blue-900/30">
                    Sikat! Simpan Data
                </button>
                <a href="{{ route('admin.dashboard') }}" class="flex-1 bg-slate-700 hover:bg-slate-600 text-center font-bold py-3 rounded-xl transition-all">
                    Batal
                </a>
            </div>
        </form>
    </div>
</body>
</html>
