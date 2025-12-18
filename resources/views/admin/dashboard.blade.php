<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Overview - Monitoring Dashboard</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-900 text-white min-h-screen font-sans">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-blue-400">Panel Admin</h1>
            <a href="{{ route('dashboard.index') }}" class="text-slate-400 hover:text-white transition-colors">Lihat Dashboard &rarr;</a>
        </div>

        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-slate-300">Riwayat Data Monitoring</h2>
            <a href="{{ route('dashboard.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-medium transition-all shadow-lg shadow-blue-900/20">
                + Tambah Data Manual
            </a>
        </div>

        @if(session('sukses'))
            <div class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-4 py-3 rounded-xl mb-6">
                {{ session('sukses') }}
            </div>
        @endif

        <div class="bg-slate-800 rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-700/50 text-slate-400 uppercase text-xs font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4 text-center">Suhu (°C)</th>
                        <th class="px-6 py-4 text-center">Volume (ml)</th>
                        <th class="px-6 py-4 text-center">Keruh (NTU)</th>
                        <th class="px-6 py-4">Teks Info</th>
                        <th class="px-6 py-4">Buzzer</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($semua_data as $data)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 font-mono text-slate-500">{{ $data->id }}</td>
                            <td class="px-6 py-4 text-center text-emerald-400 font-bold">{{ $data->gauge1 }}</td>
                            <td class="px-6 py-4 text-center text-emerald-400 font-bold">{{ $data->gauge2 }}</td>
                            <td class="px-6 py-4 text-center text-emerald-400 font-bold">{{ $data->gauge3 }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ $data->item_teks }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs font-bold {{ $data->status_buzzer ? 'bg-red-900/50 text-red-400 border border-red-500' : 'bg-slate-700 text-slate-400 border border-slate-600' }}">
                                    {{ $data->status_buzzer ? 'NYALA' : 'MATI' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right flex justify-end gap-3">
                                <a href="{{ route('dashboard.edit', $data->id) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                                <form action="{{ route('dashboard.destroy', $data->id) }}" method="POST" onsubmit="return confirm('Beneran mau hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-slate-500 italic">Belum ada data rekaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
