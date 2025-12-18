import mqtt from 'mqtt';

// Koneksi ke broker
const client = mqtt.connect('mqtt://broker.emqx.io');

const TOPIC = 'kandang/data';

client.on('connect', () => {
    console.log('🚀 Simulator Kandang Puyuh Aktif!');
    console.log(`📡 Mengirim data ke topik: ${TOPIC}`);
    console.log('Tekan Ctrl+C buat berentiin simulator.\n');

    // Interval pengiriman data tiap 3 detik
    setInterval(() => {
        // Bikin data random biar keliatan real-time
        const dummyData = {
            suhu: (20 + Math.random() * 15).toFixed(1),      // 20 - 35 C
            volume: Math.floor(400 + Math.random() * 600),   // 400 - 1000 ml
            kekeruhan: Math.floor(Math.random() * 100),      // 0 - 100 NTU
            rssi: Math.floor(-70 + Math.random() * 20),      // -70 sampe -50 dBm
            status: "Online"
        };

        const payload = JSON.stringify(dummyData);
        client.publish(TOPIC, payload);

        console.log(`📩 Terkirim: ${payload}`);
    }, 100); // 3000ms = 3 detik
});

client.on('error', (err) => {
    console.error('❌ Error MQTT:', err);
});
