import mqtt from 'mqtt';
import Highcharts from 'highcharts';
import HighchartsMore from 'highcharts/highcharts-more';
import SolidGauge from 'highcharts/modules/solid-gauge';

// Fix buat Vite/ESM: panggil .default kalau ada, kalau gak ya panggil modulnya langsung
try {
    const more = HighchartsMore.default || HighchartsMore;
    const gauge = SolidGauge.default || SolidGauge;

    if (typeof more === 'function') more(Highcharts);
    if (typeof gauge === 'function') gauge(Highcharts);

    console.log('✅ Library Highcharts berhasil di-load!');
} catch (e) {
    console.error('❌ Gagal inisialisasi modul Highcharts:', e);
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Dashboard Init...');

    const gaugeOptions = {
        chart: {
            type: 'solidgauge',
            backgroundColor: 'transparent',
            height: 160
        },
        title: null,
        pane: {
            center: ['50%', '85%'],
            size: '100%',
            startAngle: -90,
            endAngle: 90,
            background: {
                backgroundColor: '#334155',
                innerRadius: '60%',
                outerRadius: '100%',
                shape: 'arc',
                borderWidth: 0
            }
        },
        yAxis: {
            min: 0,
            max: 100,
            stops: [
                [0.1, '#55BF3B'], // green
                [0.5, '#DDDF0D'], // yellow
                [0.9, '#DF5353']  // red
            ],
            lineWidth: 0,
            tickAmount: 2,
            title: { y: -70 },
            labels: { y: 16 }
        },
        plotOptions: {
            solidgauge: {
                dataLabels: { y: 5, borderWidth: 0, useHTML: true }
            }
        },
        credits: { enabled: false },
        series: [{
            data: [0],
            dataLabels: {
                format: '<div style="text-align:center"><span style="font-size:25px;color:#f8fafc">{y}</span><br/>' +
                    '<span style="font-size:12px;color:#94a3b8">unit</span></div>'
            }
        }]
    };

    // Render 4 gauge (Suhu, Volume, Kekeruhan, Kualitas)
    const gauges = [];
    const gaugeConfigs = [
        { id: 'gauge-1', label: 'Suhu', max: 50, unit: '°C', inverse: false },
        { id: 'gauge-2', label: 'Volume', max: 1000, unit: 'ml', inverse: false },

        { id: 'gauge-3', label: 'Keruh', max: 150, unit: 'NTU', inverse: false },
        { id: 'gauge-4', label: 'Kualitas', max: 100, unit: '%', inverse: true }
    ];

    gaugeConfigs.forEach((conf) => {
        const el = document.getElementById(conf.id);
        if (el) {
            try {
                const customStops = conf.inverse ? [
                    [0.1, '#DF5353'], // red
                    [0.5, '#DDDF0D'], // yellow
                    [0.9, '#55BF3B']  // green
                ] : [
                    [0.1, '#55BF3B'], // green
                    [0.5, '#DDDF0D'], // yellow
                    [0.9, '#DF5353']  // red
                ];

                const chart = Highcharts.chart(conf.id, Highcharts.merge(gaugeOptions, {
                    yAxis: {
                        min: 0,
                        max: conf.max,
                        stops: customStops,
                        title: { text: conf.label, style: { color: '#94a3b8' } }
                    },
                    series: [{
                        name: conf.label,
                        data: [0],
                        dataLabels: {
                            format: `<div style="text-align:center"><span style="font-size:20px;color:#f8fafc">{y}</span><br/>` +
                                `<span style="font-size:10px;color:#94a3b8">${conf.unit}</span></div>`
                        }
                    }]
                }));
                gauges.push(chart);
            } catch (err) {
                console.error(`❌ Gagal render ${conf.id}:`, err);
            }
        }
    });

    // --- LOGIC STATUS NAV BAR ---
    const updateStatusDot = (id, status) => {
        const dot = document.getElementById(id);
        if (!dot) return;
        if (status === 'online') {
            dot.classList.remove('bg-slate-500', 'bg-red-500');
            dot.classList.add('bg-emerald-500');
        } else if (status === 'offline') {
            dot.classList.remove('bg-slate-500', 'bg-emerald-500');
            dot.classList.add('bg-red-500');
        }
    };

    // MQTT Connect
    const client = mqtt.connect('wss://test.mosquitto.org:8081');

    client.on('connect', () => {
        console.log('📡 MQTT Online');
        updateStatusDot('status-mqtt-dot', 'online');
        client.subscribe('kandang/data');
    });

    client.on('offline', () => updateStatusDot('status-mqtt-dot', 'offline'));

    let lastDeviceMessage = Date.now();

    // Cek Device Status via Timeout (Kalau gak ada kabar 10 detik = offline)
    setInterval(() => {
        if (Date.now() - lastDeviceMessage > 10000) {
            updateStatusDot('status-device-dot', 'offline');
            updateStatusDot('status-wifi-dot', 'offline');
        }
    }, 5000);

    client.on('message', (topic, message) => {
        if (topic === 'kandang/data') {
            try {
                const data = JSON.parse(message.toString());
                lastDeviceMessage = Date.now();
                updateStatusDot('status-device-dot', 'online');

                // Update WiFi Status based on RSSI
                if (data.rssi !== undefined) {
                    updateStatusDot('status-wifi-dot', 'online');
                }

                // Update Gauges
                if (data.suhu !== undefined && gauges[0]) gauges[0].series[0].points[0].update(parseFloat(data.suhu));
                if (data.volume !== undefined && gauges[1]) gauges[1].series[0].points[0].update(parseFloat(data.volume));

                let ntu = 0;
                if (data.kekeruhan !== undefined && gauges[2]) {
                    ntu = parseFloat(data.kekeruhan);
                    gauges[2].series[0].points[0].update(ntu);
                }

                // Kualitas (%) - Menggunakan hasil perhitungan Fuzzy dari Arduino
                if (data.kualitas !== undefined && gauges[3]) {
                    let qual = parseFloat(data.kualitas);
                    qual = Math.max(0, Math.min(100, Math.round(qual)));
                    gauges[3].series[0].points[0].update(qual);
                }


                // Update Kondisi Text (Mengikuti Status dari Arduino)
                const txt = document.getElementById('item-teks');
                if (txt && data.item_teks) {
                    const status = data.item_teks;
                    txt.innerText = status;

                    if (status === "Baik") {
                        txt.innerText = "💧 " + status + " (Mantap!)";
                        txt.className = "text-2xl font-mono animate-pulse text-emerald-400";
                    } else if (status === "Cukup") {
                        txt.innerText = "⚠️ " + status + " (Waspada)";
                        txt.className = "text-2xl font-mono animate-pulse text-yellow-500";
                    } else if (status === "Buruk") {
                        txt.innerText = "🚫 " + status + " (Ganti!)";
                        txt.className = "text-2xl font-mono animate-pulse text-red-500";
                    }
                }


            } catch (e) { console.error('JSON Error:', e); }
        }
    });

    const slider = document.getElementById('buzzer-slider');

    // Fungsi buat kirim data ke database Laravel
    const syncDataToDatabase = () => {
        const payload = {
            gauge1: gauges[0]?.series[0].points[0].y || 0,
            gauge2: gauges[1]?.series[0].points[0].y || 0,
            gauge3: gauges[2]?.series[0].points[0].y || 0,
            gauge4: gauges[3]?.series[0].points[0].y || 0,
            item_teks: document.getElementById('item-teks')?.innerText || "Status Oke",
            status_buzzer: slider?.checked ? 1 : 0
        };

        fetch('/api/simpan-data', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(data => console.log('✅ Data tersinkron ke DB:', data))
            .catch(err => console.error('❌ Gagal sinkron ke DB:', err));
    };

    if (slider) {
        slider.addEventListener('change', (e) => {
            const status = e.target.checked ? 'ON' : 'OFF';
            client.publish('kandang/control/buzzer', status);

            // Pas buzzer diubah, langsung lapor ke database biar dicatet
            syncDataToDatabase();
        });
    }

    // --- PENGATURAN WAKTU SIMPAN (AUTO-SYNC) ---
    // Ubah angka 10000 di bawah ini kalau mau ganti durasi simpan datanya.
    // 10000 = 10 detik, 30000 = 30 detik, dst.
    const WAKTU_SIMPAN = 10000;

    setInterval(() => {
        console.log(`🔄 [${WAKTU_SIMPAN / 1000}s] Sinkronisasi data otomatis ke database...`);
        syncDataToDatabase();
    }, WAKTU_SIMPAN);
});
