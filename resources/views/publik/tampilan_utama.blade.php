<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kandang Puyuh IoT Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/dashboard_mqtt.js'])
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/solid-gauge.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased flex flex-col h-full overflow-x-hidden">

    <!-- Navbar -->
    <nav class="bg-slate-800/80 backdrop-blur-md border-b border-slate-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">QuailYes IoT</span>
                </div>

                <!-- Status Indicators -->
                <div class="flex items-center space-x-4 md:space-x-6">
                    <!-- WiFi Status -->
                    <div class="flex items-center space-x-2 bg-slate-700/50 px-3 py-1.5 rounded-full border border-slate-600/50">
                        <div id="status-wifi-dot" class="w-2.5 h-2.5 rounded-full bg-slate-500 animate-pulse"></div>
                        <span class="text-xs font-medium text-slate-300">WiFi</span>
                    </div>
                    <!-- MQTT Status -->
                    <div class="flex items-center space-x-2 bg-slate-700/50 px-3 py-1.5 rounded-full border border-slate-600/50">
                        <div id="status-mqtt-dot" class="w-2.5 h-2.5 rounded-full bg-slate-500 animate-pulse"></div>
                        <span class="text-xs font-medium text-slate-300">MQTT</span>
                    </div>
                    <!-- Device Status -->
                    <div class="flex items-center space-x-2 bg-slate-700/50 px-3 py-1.5 rounded-full border border-slate-600/50">
                        <div id="status-device-dot" class="w-2.5 h-2.5 rounded-full bg-slate-500 animate-pulse"></div>
                        <span class="text-xs font-medium text-slate-300">Device</span>
                    </div>
                    <!-- Area Admin (Klik ini buat masuk ke dapur rekaman) -->
                    @auth
                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-mono text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded border border-emerald-500/20">ADMIN ACTIVE</span>
                            <a href="{{ route('admin.dashboard') }}" class="p-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-white transition-all shadow-lg" title="Ke Dashboard Admin">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </a>
                            <form action="{{ route('keluar') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-500 rounded-lg transition-all border border-red-500/20" title="Keluar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('masuk') }}" class="group flex items-center space-x-2 bg-emerald-500 hover:bg-emerald-400 px-4 py-2 rounded-xl transition-all shadow-lg shadow-emerald-500/20">
                            <svg class="w-4 h-4 text-white group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span class="text-sm font-bold text-white">Admin Area</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        <!-- Header Section -->
        <header class="mb-10 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-2">
                <span class="bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">Monitoring Kandang</span>
            </h1>
            <p class="text-slate-400 max-w-2xl mx-auto flex items-center justify-center gap-2">
                Real-time data stream via <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 rounded-md border border-emerald-500/20 font-mono text-sm">MQTT Broker</span>
            </p>
        </header>

        <main>
            <!-- Gauges Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <!-- Gauge 1: Suhu -->
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-xl flex flex-col items-center hover:scale-[1.02] transition-transform">
                    <div id="gauge-1" class="gauge-container"></div>
                    <p class="mt-2 text-slate-400 font-semibold italic text-sm">Suhu (°C)</p>
                </div>
                <!-- Gauge 2: Volume -->
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-xl flex flex-col items-center hover:scale-[1.02] transition-transform">
                    <div id="gauge-2" class="gauge-container"></div>
                    <p class="mt-2 text-slate-400 font-semibold italic text-sm">Volume Air (ml)</p>
                </div>
                <!-- Gauge 3: Kekeruhan -->
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-xl flex flex-col items-center hover:scale-[1.02] transition-transform">
                    <div id="gauge-3" class="gauge-container"></div>
                    <p class="mt-2 text-slate-400 font-semibold italic text-sm">Kekeruhan Air (NTU)</p>
                </div>
                <!-- Gauge 4: Kualitas -->
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-xl flex flex-col items-center hover:scale-[1.02] transition-transform">
                    <div id="gauge-4" class="gauge-container"></div>
                    <p class="mt-2 text-slate-400 font-semibold italic text-sm">Kualitas Air (%)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                <!-- Status & Info Card -->
                <div class="bg-slate-800 p-8 rounded-3xl border border-slate-700 shadow-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-300 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Info Status Terkini
                    </h3>
                    <div class="flex items-center justify-center min-h-[120px] bg-slate-900/50 rounded-2xl border border-slate-700/50 p-6">
                        <p id="item-teks" class="text-2xl font-mono text-slate-500 italic">Menunggu data...</p>
                    </div>
                </div>

                <!-- Buzzer Control Card -->
                <div class="bg-slate-800 p-8 rounded-3xl border border-slate-700 shadow-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-300 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        Kontrol Manual Buzzer
                    </h3>
                    <div class="flex flex-col items-center justify-center space-y-6 min-h-[120px]">
                        <label class="relative inline-flex items-center cursor-pointer scale-125">
                            <input type="checkbox" id="buzzer-slider" class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-3 text-lg font-medium text-slate-300">Aktivasi</span>
                        </label>
                        <p class="text-xs text-slate-500 uppercase tracking-widest font-bold">Kirim sinyal ON/OFF ke alat</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer Sticky Bottom -->
    <footer class="bg-slate-800/80 backdrop-blur-md border-t border-slate-700 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-slate-400 text-sm">
                    &copy; {{ date('Y') }} <span class="font-bold text-emerald-400">QuailYes IoT</span> - Monitoring Kandang Puyuh Pintar
                </div>
                <div class="flex items-center space-x-6">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></div>
                        <span class="text-slate-500 text-xs uppercase font-bold tracking-tighter">System Normal</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .gauge-container { width: 100%; height: 160px; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
    </style>
</body>
</html>
