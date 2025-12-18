import mqtt from 'mqtt';
import axios from 'axios';

// Koneksi ke broker
const client = mqtt.connect('mqtt://broker.emqx.io');

const TOPIC = 'kandang/data';
const API_URL = 'http://localhost:8000/api/simpan-data';

let messageCount = 0;

client.on('connect', () => {
    console.log('🚀 Simulator Kandang Puyuh Aktif!');
    console.log(`📡 Mengirim data ke topik: ${TOPIC}`);
    console.log(`💾 Data juga akan dikirim ke API: ${API_URL} setiap 10 detik`);
    console.log('Tekan Ctrl+C buat berentiin simulator.\n');

    // Interval pengiriman data tiap 1 detik
    setInterval(() => {
        // Bikin data random biar keliatan real-time
        const dummyData = {
            suhu: (20 + Math.random() * 15).toFixed(1),      // 20 - 35 C
            volume: Math.floor(400 + Math.random() * 600),   // 400 - 1000 ml
            kekeruhan: Math.floor(Math.random() * 150),      // 0 - 150 NTU
            rssi: Math.floor(-70 + Math.random() * 20),      // -70 sampe -50 dBm
            status: "Sistem Normal"
        };

        const payload = JSON.stringify(dummyData);
        client.publish(TOPIC, payload);
        console.log(`📩 [MQTT] Terkirim: ${payload}`);

        // Setiap 10 kali kirim MQTT (10 detik), kita simpan ke database biar gak kepenuhan
        messageCount++;
        if (messageCount >= 10) {
            messageCount = 0;
            axios.post(API_URL, dummyData)
                .then(res => console.log('✅ [DATABASE] Data berhasil disimpan otomatis!'))
                .catch(err => console.error('❌ [DATABASE] Gagal simpan ke DB:', err.message));
        }
    }, 1000); 
});

client.on('error', (err) => {
    console.error('❌ Error MQTT:', err);
});
