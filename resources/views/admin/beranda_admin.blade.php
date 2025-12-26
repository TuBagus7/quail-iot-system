<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Overview - QuailYes IoT</title>
    @vite(['resources/css/app.css'])

    <!-- CSS Tambahan buat DataTables biar cakep -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <style>
        /* Custom styling biar DataTables nyambung sama tema Dark kita */
        .dataTables_wrapper {
            color: #94a3b8 !important;
        }

        table.dataTable {
            border-collapse: collapse !important;
            border: none !important;
            margin-bottom: 20px !important;
        }

        table.dataTable thead th {
            background-color: #334155 !important;
            color: #94a3b8 !important;
            border-bottom: 1px solid #475569 !important;
        }

        table.dataTable tbody tr {
            background-color: #1e293b !important;
            border-bottom: 1px solid #334155 !important;
        }

        table.dataTable tbody tr:hover {
            background-color: #334155 !important;
        }

        .dataTables_filter input {
            background-color: #0f172a !important;
            border: 1px solid #475569 !important;
            border-radius: 8px !important;
            color: white !important;
            padding: 5px 10px !important;
            margin-bottom: 10px !important;
        }

        .dataTables_length select {
            background-color: #0f172a !important;
            border: 1px solid #475569 !important;
            border-radius: 8px !important;
            color: white !important;
        }

        .dt-buttons .dt-button {
            background: #3b82f6 !important;
            color: white !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 6px 15px !important;
            font-weight: bold !important;
            transition: all 0.2s !important;
        }

        .dt-buttons .dt-button:hover {
            background: #2563eb !important;
            transform: scale(1.05);
        }

        .dataTables_info,
        .dataTables_paginate {
            padding-top: 15px !important;
        }

        .paginate_button {
            color: #94a3b8 !important;
        }
    </style>
</head>

<body class="bg-slate-900 text-white min-h-screen font-sans">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-blue-400">Panel Admin (Dapur Rekaman)</h1>
            <a href="{{ route('tampilan_utama') }}"
                class="text-slate-400 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Lihat Dashboard Publik
            </a>
        </div>

        <!-- Table Section -->
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-300">Riwayat Data Lengkap</h2>
                <p class="text-sm text-slate-500">Gunakan filter untuk mencari data spesifik</p>
            </div>
            <div class="flex gap-4">
                <form action="{{ route('admin.crud.hapus_semua') }}" method="POST"
                    onsubmit="return confirm('PERINGATAN: Ini bakal hapus SEMUA data. Beneran mau lanjut?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-500 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-red-900/40 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        Hapus Semua Data
                    </button>
                </form>

                <a href="{{ route('admin.crud.create') }}"
                    class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-900/40 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Data Manual
                </a>
            </div>
        </div>

        @if(session('sukses'))
            <div
                class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('sukses') }}
            </div>
        @endif

        <div class="bg-slate-800 rounded-2xl border border-slate-700 shadow-xl p-6 overflow-x-auto">
            <table id="tabelKandang" class="w-full text-left border-collapse">
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
                    @foreach($semua_data as $data)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 font-mono text-slate-500">{{ $data->id }}</td>
                            <td class="px-6 py-4 text-center text-emerald-400 font-bold">{{ $data->gauge1 }}</td>
                            <td class="px-6 py-4 text-center text-emerald-400 font-bold">{{ $data->gauge2 }}</td>
                            <td class="px-6 py-4 text-center text-emerald-400 font-bold">{{ $data->gauge3 }}</td>
                            <td class="px-6 py-4 text-slate-300 font-medium">{{ $data->item_teks }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 rounded text-[10px] font-bold {{ $data->status_buzzer ? 'bg-red-900/50 text-red-400 border border-red-500' : 'bg-slate-700 text-slate-400 border border-slate-600' }}">
                                    {{ $data->status_buzzer ? 'NYALA' : 'MATI' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right flex justify-end gap-3">
                                <a href="{{ route('admin.crud.edit', $data->id) }}"
                                    class="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-all"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.crud.destroy', $data->id) }}" method="POST"
                                    onsubmit="return confirm('Beneran mau hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-all"
                                        title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script Utama (jQuery & DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <!-- Script Buttons buat Export (PDF, Excel, Print) -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#tabelKandang').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'print',
                        text: '🖨️ Cetak Data',
                        title: 'Laporan Monitoring Kandang Puyuh - QuailYes'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '📄 Download PDF',
                        title: 'Laporan_Monitoring_Kandang',
                        download: 'open'
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Excel',
                        title: 'Laporan_Monitoring_Kandang'
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                    search: "Cari Data:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Nampilin _START_ sampe _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Balik"
                    }
                },
                order: [[0, 'desc']] // Urutin ID yang paling baru di atas
            });
        });
    </script>
</body>

</html>