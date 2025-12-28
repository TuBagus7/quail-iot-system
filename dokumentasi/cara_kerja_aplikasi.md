# Dokumentasi Teknis Sistem IoT Monitoring Kandang Puyuh 🐦

Dokumentasi ini memberikan penjelasan mendalam mengenai arsitektur, aliran data, dan logika pemrograman yang digunakan dalam aplikasi Monitoring Kandang Puyuh berbasis Laravel dan IoT.

---

## 🏗️ 1. Arsitektur Sistem (System Architecture)

Sistem ini mengadopsi arsitektur **Event-Driven** menggunakan protokol **MQTT** untuk komunikasi real-time dan **REST API** untuk persistensi data.

1.  **Edge Device (Hardware):** Menggunakan ESP32/Arduino yang terhubung ke sensor (DHT22, Ultrasonic, Turbidity).
2.  **Communication Bridge:** MQTT Broker (`test.mosquitto.org`) menggunakan sirkuit WebSockets (WSS) port 8081.
3.  **Frontend Dashboard:** Single Page Interface (SPI) yang dibangun dengan Tailwind CSS dan Highcharts.js.
4.  **Backend Server:** Laravel 11 sebagai pengelola logika bisnis, database, dan penyedia layanan notifikasi.

---

## 📡 2. Aliran Data dan Protokol Komunikasi

### A. Real-Time Data Stream (MQTT)
*   **Proses:** Perangkat keras melakukan sampling data sensor secara berkala dan mem-publish payload dalam format **JSON** ke topik `kandang/data`.
*   **Format JSON:**
    ```json
    {
      "suhu": 28.5,
      "volume": 850,
      "kekeruhan": 12.5,
      "kualitas": 95,
      "item_teks": "Baik",
      "rssi": -65
    }
    ```
*   **Subscriber:** File `dashboard_mqtt.js` pada browser pelanggan bertindak sebagai subscriber. Menggunakan pustaka (library) `mqtt.js`, browser mendengarkan topik tersebut dan langsung melakukan **injection** data ke grafik Highcharts tanpa merefresh halaman (*Sub-second latency*).

### B. Persistensi Data (Database Sync)
*   **Mekanisme:** Berbeda dengan sistem IoT tradisional di mana hardware memukul API secara langsung, sistem ini menggunakan **Client-Side Proxy Sync**.
*   **Logika:** Fungsi `syncDataToDatabase()` di sisi client mengambil status terakhir dari state UI dan mengirimkan permintaan **POST** ke endpoint `/api/simpan-data`.
*   **Interval:** Dikontrol melalui konstanta `WAKTU_SIMPAN` (default: 30000ms / 30 detik) menggunakan `setInterval()` di JavaScript.

---

## 🧠 3. Logika Backend (Laravel Processing)

Ketika endpoint API menerima data melalui **POST** ke `/api/simpan-data`, beberapa proses terjadi secara berurutan:

1.  **Data Mapping:** Mencocokkan input JSON (misal: `suhu`) ke skema database (misal: `gauge1`).
2.  **Validation & Sanitization:** Memastikan semua nilai bertipe numerik untuk mencegah kegagalan database.
3.  **Persistence:** Data disimpan ke tabel melalui Model `ModelKandang`.

---

## 📧 4. Algoritma Notifikasi dan Throttling

Sistem ini menyertakan mesin notifikasi cerdas untuk mendeteksi anomali kualitas air:

1.  **Detection Logic:** Menggunakan fungsi `str_contains()` pada field `item_teks`. Jika ditemukan string `"Ganti!"`, sistem masuk ke mode peringatan.
2.  **Spam Protection (Throttling):**
    *   Sistem menggunakan **Laravel Cache** sebagai *flag* memori sementara.
    *   Sebelum mengirim email, sistem mengecek kunci `terakhir_kirim_email_buruk`.
    *   Jika kunci tidak ada, email dikirim menggunakan class `NotifikasiKualitasAirBad` dan kunci cache dibuat dengan durasi tertentu (TTL).
    *   Jika kondisi air membaik (tidak ada kata "Ganti!"), cache otomatis dihapus menggunakan `Cache::forget()`, sehingga jika air mendadak buruk lagi, notifikasi bisa langsung dikirim tanpa menunggu jeda.

---

## 🛠️ 5. Referensi Konfigurasi Teknis

| Komponen | Lokasi File | Deskripsi Teknis |
| :--- | :--- | :--- |
| **Sync Interval** | `resources/js/dashboard_mqtt.js` | Mengubah nilai `WAKTU_SIMPAN` |
| **Email Logic** | `app/Http/Controllers/KontrolerKandang.php` | Modifikasi metode `simpanDataDariAlat` |
| **Mail Template** | `resources/views/emails/notifikasi_kualitas_air.blade.php` | Desain layout email peringatan |
| **Database Schema** | `database/migrations/..._create_model_kandangs_table.php` | Definisi struktur kolom tabel |
| **API Route** | `routes/web.php` | Definisi endpoint `api/simpan-data` |

---

*Dokumentasi ini dibuat untuk mempermudah pemeliharaan sistem di masa mendatang. Pastikan setiap perubahan pada logika sinkronisasi diikuti dengan pengujian pada sisi hardware maupun dashboard.*
