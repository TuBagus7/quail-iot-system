// -----------------------------------------
// Kandang Puyuh Pintar IoT - Final Version
// Fuzzy Tsukamoto 27 aturan + Blynk + LCD + Google Sheets
// + Filter Sensor (Moving Avg + Median)
// + Mode Darurat + Sistem Adaptif + Auto Threshold
// -----------------------------------------

// [ORIGINAL Blynk]
// #define BLYNK_TEMPLATE_ID "TMPL6ZHlijOvl"
// #define BLYNK_TEMPLATE_NAME "Kandang Puyuh IoT"
// #define BLYNK_AUTH_TOKEN "oMyPjdTJd5QGzuNEhlRad1WStcJAYhZW"

// Library
#include <WiFi.h>
#include <WiFiClient.h>
// #include <BlynkSimpleEsp32.h> // [ORIGINAL]
#include <MQTTESP.h>           // [SUGGESTION]
#include <NusabotSimpleTimer.h> // [SUGGESTION]
#include <DHT.h>


#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <HTTPClient.h>
#include <Filters.h>   // Digunakan untuk Kalman Filter

// WiFi credentials
char ssid[] = "Redmi Note 13 5G";
char pass[] = "apayalupaa";

// Pin sensor dan aktuator
#define DHTPIN 15
#define DHTTYPE DHT22
#define TRIG 4
#define ECHO 5
#define TURBIDITY_PIN 36
#define BUZZER_PIN 18

// LCD
LiquidCrystal_I2C lcd(0x27, 16, 2);
DHT dht(DHTPIN, DHTTYPE);

// Data
float suhu, kelembapan, volume, kekeruhan, kualitas;
int volume_mL;
String status_kualitas = "";
bool buzzer_manual = false;
bool mode_darurat = false;
float max_suhu = 50.0;
float min_volume = 0.0;
float max_kekeruhan = 150.0;

// Google Sheets
// const char* webhookURL = "https://script.google.com/macros/s/AKfycbzpC2UYVdBsEMowbSV_aQPjZP7ixR4xyStcQIrgAjjgWmhAk8pEC6c0kOB3-Pt72ezu/exec";

// [SUGGESTION MQTT Config Updated]
const char* mqtt_server = "test.mosquitto.org"; // Sesuai dashboard_mqtt.js
const int mqtt_port = 1883;
// Constructor sesuai referensi baru
MQTTESP mqtt(ssid, pass, mqtt_server, mqtt_port, nullptr, nullptr); 
NusabotSimpleTimer timer;


// Definisi Topik (Sesuai dashboard_mqtt.js)
const char* topic_json = "kandang/data";
const char* topic_control = "kandang/control/buzzer";

// [ORIGINAL Blynk Write]
// BLYNK_WRITE(V4) {
//   buzzer_manual = param.asInt();
// }

// [SUGGESTION MQTT Callback Updated]
void handleControl(const char* topic, const char* message) {
  Serial.print("MQTT Control [");
  Serial.print(topic);
  Serial.print("]: ");
  Serial.println(message);
  
  if (String(topic) == topic_control) {
    if (String(message) == "ON") buzzer_manual = true;
    else buzzer_manual = false;
  }
}



// Filter
#define N 5
float suhu_hist[N], volume_hist[N], keruh_hist[N];
float kalman_suhu = 0, kalman_volume = 0, kalman_keruh = 0;
float estimateKalman(float input, float &estimate, float q = 0.125, float r = 4.0) {
  static float p = 1.0;
  float k = p / (p + r);
  estimate = estimate + k * (input - estimate);
  p = (1 - k) * p + q;
  return estimate;
}

float median(float *arr) {
  float tmp[N];
  memcpy(tmp, arr, sizeof(tmp));
  for (int i = 0; i < N-1; i++) {
    for (int j = i+1; j < N; j++) {
      if (tmp[i] > tmp[j]) {
        float t = tmp[i]; tmp[i] = tmp[j]; tmp[j] = t;
      }
    }
  }
  return tmp[N/2];
}

float movingAverage(float *arr) {
  float sum = 0;
  for (int i = 0; i < N; i++) sum += arr[i];
  return sum / N;
}

  // Nama label fuzzy untuk suhu, volume, kekeruhan
  String suhu_label[3] = {"Dingin", "Normal", "Panas"};
  String volume_label[3] = {"Sedikit", "Cukup", "Penuh"};
  String keruh_label[3] = {"Tinggi", "Sedang", "Rendah"};

void setup() {
  Serial.begin(115200);
  Serial.println("🔧 Setup mulai...");

  // [ORIGINAL Blynk]
  // Blynk.begin(BLYNK_AUTH_TOKEN, ssid, pass);
  
  // [SUGGESTION MQTT Updated]
  mqtt.begin();
  // Menggunakan registerCallback sesuai referensi baru
  mqtt.registerCallback(topic_control, handleControl); 


  dht.begin();
  pinMode(TRIG, OUTPUT);
  pinMode(ECHO, INPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  Wire.begin(21, 22);
  lcd.init();
  lcd.backlight();
  
  // [SUGGESTION Dual Core]
  xTaskCreatePinnedToCore(taskSensor, "SensorTask", 4096, NULL, 0, NULL, 0);
  xTaskCreatePinnedToCore(taskFuzzyLogic, "FuzzyTask", 4096, NULL, 1, NULL, 1);

  Serial.println("🚀 Sistem siap (Dual-Core MQTT)\n");
}


unsigned long lastSend = 0;

// -------------------- DEBUG 27 ATURAN --------------------
void debugAllCombinations() {
  float alpha_sum = 0, z_sum = 0;

  for (int i = 0; i < 3; i++) {
    for (int j = 0; j < 3; j++) {
      for (int k = 0; k < 3; k++) {
        float s = (i == 0) ? 22 : (i == 1) ? 30 : 38;
        float v = (j == 0) ? 0.1 : (j == 1) ? 0.5 : 0.9;
        float kkh = (k == 0) ? 100 : (k == 1) ? 65 : 20;

        float mus = getMF_Suhu(s, suhu_label[i]);
        float muv = getMF_Volume(v, volume_label[j]);
        float muk = getMF_Keruh(kkh, keruh_label[k]);

        float alpha = min3(mus, muv, muk);
        String out_label = getOutput(suhu_label[i], volume_label[j], keruh_label[k]);
        float z = getZ(out_label, alpha);

        alpha_sum += alpha;
        z_sum += alpha * z;

        Serial.printf("[%s, %s, %s] | α = %.3f | Output = %s | z = %.2f\n",
                      suhu_label[i].c_str(), volume_label[j].c_str(), keruh_label[k].c_str(),
                      alpha, out_label.c_str(), z);
      }
    }
  }

  float kualitas_debug = (alpha_sum > 0) ? z_sum / alpha_sum : 0;
  Serial.printf(">> Nilai kualitas gabungan: %.2f\n", kualitas_debug);
  Serial.println("================================================================\n");
}

// -------------------- LCD --------------------
void updateLCD() {
  // [REMOVED] lcd.clear(); // Hapus clear() agar tidak kelap-kelip

  if (kualitas <= 30) status_kualitas = "Buruk";
  else if (kualitas <= 70) status_kualitas = "Cukup";
  else status_kualitas = "Baik";

  String singkat_lcd = (status_kualitas == "Buruk") ? "Brk" :
                       (status_kualitas == "Cukup") ? "Ckp" : "Bik";

  // Baris 1: Suhu & Volume
  lcd.setCursor(0, 0);
  // Tambahkan spasi di akhir label untuk hapus sisa karakter lama
  lcd.printf("S:%d%c V:%.1fL  ", (int)suhu, 223, volume); 
  
  lcd.setCursor(13, 0); 
  lcd.print(singkat_lcd + " "); // Spasi tambahan untuk padding

  // Baris 2: Kekeruhan & Kualitas
  lcd.setCursor(0, 1);
  lcd.printf("K:%dNTU Q:%d%%  ", (int)kekeruhan, (int)kualitas);

  Serial.printf(" | Suhu: %.1f °C | Volume: %.1f L |\n", suhu, volume);
  Serial.printf(" | Kekeruhan: %.1f NTU | Kualitas: %.0f %% | Status: %s\n", kekeruhan, kualitas, status_kualitas.c_str());
  Serial.println("================================================================");
}


// [SUGGESTION MQTT Publish]
void publish_json() {
  String payload = "{";
  payload += "\"suhu\":" + String(suhu) + ",";
  payload += "\"volume\":" + String(volume_mL) + ","; // Mengirim mL (0-1000)
  payload += "\"kekeruhan\":" + String(kekeruhan) + ",";
  payload += "\"kualitas\":" + String(kualitas) + ",";
  payload += "\"item_teks\":\"" + status_kualitas + "\",";
  payload += "\"status\":\"Online\"";
  payload += "}";
  mqtt.publish(topic_json, payload, true, 1);
}


// [ORIGINAL - Kirim ke Blynk]
// void kirimBlynk() {
//   Blynk.virtualWrite(V0, suhu);
//   ...
// }


// -------------------- Kontrol Buzzer dan Notifikasi --------------------
unsigned long lastNotifTime = 0;
void kontrolBuzzer() {
  if (kualitas < 25 && !buzzer_manual) {
    digitalWrite(BUZZER_PIN, HIGH);
    // [ORIGINAL Blynk log]
    // if (millis() - lastNotifTime > 15000) {
    //   Blynk.logEvent("buzzer_alert", "...");
    //   lastNotifTime = millis();
    // }
  } else {
    digitalWrite(BUZZER_PIN, LOW);
  }
}


// -------------------- Kirim ke Google Sheets --------------------
// void kirimSheets() {
//   if (WiFi.status() == WL_CONNECTED) {
//     HTTPClient http;
//     String encodedStatus = status_kualitas;
//     encodedStatus.replace(" ", "%20");
//     encodedStatus.replace(":", "%3A");
//     String url = String(webhookURL) + "?suhu=" + suhu + "&volume=" + String(volume, 2) + 
//                  "&kekeruhan=" + kekeruhan + "&kualitas=" + kualitas + "&status=" + encodedStatus;
//     Serial.print("URL Sheets: "); Serial.println(url);
//     http.begin(url); http.GET(); http.end();
//   }
// }

void loop() {
  // [ORIGINAL Loop Body moved to Tasks]
  // Blynk.run();
  // ...
  vTaskDelay(1000 / portTICK_PERIOD_MS);
}

// [SUGGESTION Dual Core Implementation]
void taskSensor(void *pvParameters) {
  while (true) {
    bacaSensor();
    updateLCD();
    kontrolBuzzer();
    bacaSensor(); 
    updateLCD();
    kontrolBuzzer();
    vTaskDelay(800 / portTICK_PERIOD_MS); // Update sensor lebih cepat (800ms)
  }
}


void taskFuzzyLogic(void *pvParameters) {
  while (true) {
    mqtt.loop();
    fuzzyLogic();
    
    static unsigned long lastPub = 0;
    if (millis() - lastPub > 2000) { // [SUGGESTION] Dipercepat dari 5000ms jadi 2000ms
      publish_json();
      lastPub = millis();
    }

    vTaskDelay(100 / portTICK_PERIOD_MS);
  }
}


void shiftArray(float *arr, float val) {
  for (int i = N-1; i > 0; i--) arr[i] = arr[i-1];
  arr[0] = val;
}

void bacaSensor() {
// Get Suhu dan Kelembapan
  float s = dht.readTemperature();
  float h = dht.readHumidity();
  if (!isnan(s)) shiftArray(suhu_hist, s);
  if (!isnan(h)) kelembapan = h;

// Get Volume dan Jarak
  digitalWrite(TRIG, LOW); delayMicroseconds(2);
  digitalWrite(TRIG, HIGH); delayMicroseconds(10);
  digitalWrite(TRIG, LOW);
  long durasi = pulseIn(ECHO, HIGH);
  float jarak = durasi * 0.034 / 2.0; // hasil dalam cm
  jarak = constrain(jarak, 0.0, 13.5); // sesuai tinggi wadah 13.5 cm
  
  // Perhitungan volume berdasarkan tinggi air max 13.5 cm = 1000 mL
  float vLiter = constrain(1.0 - (jarak / 13.5), 0.0, 1.0); // Liter
  int v_mL = vLiter * 1000; // mL
  shiftArray(volume_hist, vLiter);
  volume_mL = v_mL;

// Get Turbidity
  int adc = analogRead(TURBIDITY_PIN);
  float voltage = (float)adc / 4095.0 * 3.3;
// Mapping langsung: tegangan tinggi = NTU rendah (jernih), tegangan rendah = NTU tinggi (keruh)
  float k = map(voltage * 1000.0, 500.0, 2500.0, 100.0, 0.0); // batas tegangan 0.5V–2.5V
  k = constrain(k, 0.0, 150.0); // NTU antara 0 – 150
  shiftArray(keruh_hist, k);  // simpan ke histori untuk filtering

  suhu = (median(suhu_hist) + movingAverage(suhu_hist)) / 2.0;
  volume = (median(volume_hist) + movingAverage(volume_hist)) / 2.0;
  kekeruhan = (median(keruh_hist) + movingAverage(keruh_hist)) / 2.0;

  // Update volume_mL dari hasil filter (biar gak loncat-loncat angkanya)
  volume_mL = volume * 1000; 

  mode_darurat = (suhu > max_suhu || volume < min_volume || kekeruhan > max_kekeruhan);


   // Tampilkan ke Serial Monitor
  Serial.println("=================== Kandang Puyuh Pintar IoT ===================");
  Serial.print(" | Jarak: "); Serial.print(jarak); Serial.print(" Cm");
  Serial.print(" | Volume_mL: "); Serial.print(volume_mL); Serial.println(" mL");
  Serial.print(" | Kelembapan: "); Serial.print(kelembapan, 1); Serial.println(" %");
  Serial.printf(" | Darurat: %s\n", mode_darurat ? "YA" : "TIDAK");
  Serial.print(" | ADC Turbidity: "); Serial.print(adc);
  Serial.print(" | Tegangan: "); Serial.print(voltage, 2);
  Serial.print(" V | Kekeruhan: "); Serial.print(k, 1); Serial.println(" NTU");
}

// -------------------- Fuzzy Logic --------------------
float min3(float a, float b, float c) {
  return min(a, min(b, c));  // Fungsi minimum dari 3 nilai
}

void fuzzyLogic() {
  float alpha_sum = 0;
  float z_sum = 0;
  int ruleNum = 1;

  Serial.println("================================================================");
  Serial.println("1) Tahap Fuzzyfication");

  for (int i = 0; i < 3; i++) {
    float mus = getMF_Suhu(suhu, suhu_label[i]);
    Serial.printf("-> a. Nilai Miu Suhu %s = %.2f\n", suhu_label[i].c_str(), mus);
  }
  for (int i = 0; i < 3; i++) {
    float muv = getMF_Volume(volume, volume_label[i]);
    Serial.printf("-> b. Nilai Miu Volume %s = %.2f\n", volume_label[i].c_str(), muv);
  }
  for (int i = 0; i < 3; i++) {
    float muk = getMF_Keruh(kekeruhan, keruh_label[i]);
    Serial.printf("-> c. Nilai Miu Kekeruhan %s = %.2f\n", keruh_label[i].c_str(), muk);
  }

  Serial.println("\n2) Tahap Inferensi");

  for (int i = 0; i < 3; i++) {
    for (int j = 0; j < 3; j++) {
      for (int k = 0; k < 3; k++) {
        float mus = getMF_Suhu(suhu, suhu_label[i]);
        float muv = getMF_Volume(volume, volume_label[j]);
        float muk = getMF_Keruh(kekeruhan, keruh_label[k]);

        float alpha = min3(mus, muv, muk);
        String out_label = getOutput(suhu_label[i], volume_label[j], keruh_label[k]);
        float z = getZ(out_label, alpha);

        alpha_sum += alpha;
        z_sum += alpha * z;

        Serial.printf("[R%d] IF Suhu %s AND Volume %s AND Kekeruhan %s THEN %s\n", ruleNum,
                      suhu_label[i].c_str(), volume_label[j].c_str(), keruh_label[k].c_str(), out_label.c_str());
        Serial.printf("-> a-predikat%d = %.2f\n", ruleNum, alpha);
        Serial.printf("-> z%d = %.2f\n", ruleNum, z);
        ruleNum++;
      }
    }
  }

  kualitas = (alpha_sum > 0) ? z_sum / alpha_sum : 0;
  kualitas = constrain(kualitas, 0, 100);  // Batasi 0–100%


  Serial.println("\n3) Tahap Defuzzyfication");
  Serial.printf("=> Nilai Akhir Kualitas Kandang: %.2f (Status: %s)\n", kualitas,
                (kualitas <= 30) ? "Buruk" : (kualitas <= 70) ? "Cukup" : "Baik");
  Serial.println("================================================================");
}

// -------------------- Output Fuzzy --------------------
String getOutput(String s, String v, String k) {
  // Jika kekeruhan tinggi → langsung Buruk
  if (k == "Tinggi") return "Buruk";

  // Kombinasi lengkap 27 aturan
  if (s == "Dingin" && v == "Sedikit" && k == "Rendah") return "Cukup";
  if (s == "Dingin" && v == "Sedikit" && k == "Sedang") return "Cukup";
  if (s == "Dingin" && v == "Cukup"   && k == "Rendah") return "Baik";
  if (s == "Dingin" && v == "Cukup"   && k == "Sedang") return "Cukup";
  if (s == "Dingin" && v == "Penuh"   && k == "Rendah") return "Baik";
  if (s == "Dingin" && v == "Penuh"   && k == "Sedang") return "Baik";

  if (s == "Normal" && v == "Sedikit" && k == "Rendah") return "Cukup";
  if (s == "Normal" && v == "Sedikit" && k == "Sedang") return "Cukup";
  if (s == "Normal" && v == "Cukup"   && k == "Rendah") return "Cukup";
  if (s == "Normal" && v == "Cukup"   && k == "Sedang") return "Cukup";
  if (s == "Normal" && v == "Penuh"   && k == "Rendah") return "Baik";
  if (s == "Normal" && v == "Penuh"   && k == "Sedang") return "Cukup";

  if (s == "Panas" && v == "Sedikit" && k == "Rendah") return "Cukup";
  if (s == "Panas" && v == "Sedikit" && k == "Sedang") return "Cukup";
  if (s == "Panas" && v == "Cukup"   && k == "Rendah") return "Cukup";
  if (s == "Panas" && v == "Cukup"   && k == "Sedang") return "Cukup";
  if (s == "Panas" && v == "Penuh"   && k == "Rendah") return "Cukup";
  if (s == "Panas" && v == "Penuh"   && k == "Sedang") return "Cukup";

  // Semua kondisi lain (fallback)
  return "Cukup";
}

float getZ(String label, float alpha) {
  if (label == "Buruk") return constrain(30 - alpha * 30, 0, 30);        // dari 30 ke 0
  if (label == "Cukup") return constrain(31 + alpha * 39, 31, 70);       // dari 31 ke 70
  if (label == "Baik")  return constrain(71 + alpha * 29, 71, 100);      // dari 71 ke 100
  return 0;

}

float getMF_Suhu(float a, String tipe) {
  if (tipe == "Dingin") {
    if (a <= 20) return 1.0;
    else if (a > 20 && a < 35) return (35 - a) / 15.0;
    else return 0.0;
  }
  if (tipe == "Normal") {
    if (a <= 28 || a >= 42) return 0.0;
    else if (a > 28 && a <= 35) return (a - 28) / 7.0;
    else if (a > 35 && a < 42) return (42 - a) / 7.0;
    else return 0.0;
  }
  if (tipe == "Panas") {
    if (a <= 35) return 0.0;
    else if (a > 35 && a < 50) return (a - 35) / 15.0;
    else return 1.0;
  }
  return 0.0;
}

float getMF_Volume(float b, String tipe) {
  if (tipe == "Sedikit") {
    if (b <= 0.0) return 1.0;
    else if (b > 0.0 && b < 0.5) return (0.5 - b) / 0.5;
    else return 0.0;
  }
  if (tipe == "Cukup") {
    if (b <= 0.3 || b >= 0.7) return 0.0;
    else if (b > 0.3 && b <= 0.5) return (b - 0.3) / 0.2;
    else if (b > 0.5 && b < 0.7) return (0.7 - b) / 0.2;
    else return 0.0;
  }
  if (tipe == "Penuh") {
    if (b <= 0.5) return 0.0;
    else if (b > 0.5 && b < 1.0) return (b - 0.5) / 0.5;
    else return 1.0;
  }
  return 0.0;
}

float getMF_Keruh(float c, String tipe) {
  if (tipe == "Rendah") {
    if (c <= 0) return 1.0;
    else if (c > 0 && c < 30) return (30 - c) / 30.0;
    else return 0.0;
  }
  if (tipe == "Sedang") {
    if (c <= 15 || c >= 45) return 0.0;
    else if (c > 15 && c <= 30) return (c - 15) / 15.0;
    else if (c > 30 && c < 45) return (45 - c) / 15.0;
    else return 0.0;
  }
  if (tipe == "Tinggi") {
    if (c <= 30) return 0.0;
    else if (c > 30 && c < 150) return (c - 30) / 120.0;
    else return 1.0;
  }
  return 0.0;
}