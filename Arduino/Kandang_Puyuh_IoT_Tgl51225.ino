// -----------------------------------------
// Kandang Puyuh Pintar IoT - Simplified Version
// Sensor connectivity + MQTTESP JSON publishing (no fuzzy logic)
// -----------------------------------------
#include <WiFi.h>
#include <MQTTESP.h>
// #include <ArduinoJson.h> // removed for simple string JSON
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <DHT.h>
#include <HTTPClient.h>

// WiFi credentials
const char* ssid = "AWAK";
const char* pass = "tuankayo";

// MQTT broker (using MQTTESP which handles WiFi internally)
const char* mqtt_server = "broker.emqx.io";
MQTTESP mqtt(ssid, pass, mqtt_server);

// MQTT topic for JSON payload
const char* topic_json = "kandang/data";

// Sensor pins
#define DHTPIN 15
#define DHTTYPE DHT22
#define TRIG 4
#define ECHO 5
#define TURBIDITY_PIN 36
#define BUZZER_PIN 18

// Objects for sensors and display
DHT dht(DHTPIN, DHTTYPE);
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Variables to hold sensor data
float suhu = 0, kelembapan = 0, volume = 0, kekeruhan = 0;
String status = "Offline";

// Simple moving‑average filter (N samples)
#define N 5
float suhu_hist[N], volume_hist[N], keruh_hist[N];

void shiftArray(float *arr, float val) {
  for (int i = N-1; i > 0; i--) arr[i] = arr[i-1];
  arr[0] = val;
}
float movingAverage(float *arr) {
  float sum = 0;
  for (int i = 0; i < N; i++) sum += arr[i];
  return sum / N;
}

// ---------------------------------------------------
// Callback: Handle incoming MQTT messages (from Dashboard)
// ---------------------------------------------------
void callback(char* topic, byte* payload, unsigned int length) {
  String message = "";
  for (int i = 0; i < length; i++) {
    message += (char)payload[i];
  }
  
  Serial.print("📩 Perintah dari Web [");
  Serial.print(topic);
  Serial.print("]: ");
  Serial.println(message);

  // Jika ada perintah buzzer dari dashboard
  if (String(topic) == "kandang/control/buzzer") {
    if (message == "ON") {
      digitalWrite(BUZZER_PIN, HIGH);
      Serial.println("🚨 Buzzer NYALA!");
    } else {
      digitalWrite(BUZZER_PIN, LOW);
      Serial.println("🔇 Buzzer MATI!");
    }
  }
}

// ---------------------------------------------------
// Setup: initialise WiFi, sensors, LCD and MQTTESP
// ---------------------------------------------------
void setup() {
  Serial.begin(115200);

  // Connect to WiFi
  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print('.');
  }
  Serial.println("\n✅ WiFi connected");
  Serial.print("IP: "); Serial.println(WiFi.localIP());

  // Sensors
  dht.begin();
  pinMode(TRIG, OUTPUT);
  pinMode(ECHO, INPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  // LCD
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0,0);
  lcd.print("Connecting...");

  // Setup Callback buat dengerin Dashboard
  mqtt.setCallback(callback);
}

// ---------------------------------------------------
// Read all sensors and apply moving average
// ---------------------------------------------------
void readSensors() {
  // DHT22 temperature & humidity
  float t = dht.readTemperature();
  float h = dht.readHumidity();
  if (!isnan(t)) shiftArray(suhu_hist, t);
  if (!isnan(h)) kelembapan = h;

  // Ultrasonic sensor (water level)
  digitalWrite(TRIG, LOW); delayMicroseconds(2);
  digitalWrite(TRIG, HIGH); delayMicroseconds(10);
  digitalWrite(TRIG, LOW);
  long duration = pulseIn(ECHO, HIGH);
  float jarak = duration * 0.034 / 2.0; // cm
  const float tinggi = 13.5; // cm, tank height
  float level = constrain(tinggi - jarak, 0, tinggi);
  float vLit = level / tinggi; // fraction of tank volume
  shiftArray(volume_hist, vLit);

  // Turbidity sensor (analog)
  int adc = analogRead(TURBIDITY_PIN);
  float voltage = adc * (3.3 / 4095.0);
  float ntu = map(voltage * 1000, 500, 2500, 100, 0);
  ntu = constrain(ntu, 0, 150);
  shiftArray(keruh_hist, ntu);

  // Apply moving average
  suhu = movingAverage(suhu_hist);
  // Convert fraction to milliliters (example tank capacity 1500 ml)
  volume = movingAverage(volume_hist) * 1500.0;
  kekeruhan = movingAverage(keruh_hist);
}

// ---------------------------------------------------
// Update LCD with latest values
// ---------------------------------------------------
void updateLCD() {
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.printf("S:%.1fC V:%.0fml", suhu, volume);
  lcd.setCursor(0,1);
  lcd.printf("T:%.0f NTU", kekeruhan);
}

// ---------------------------------------------------
// Publish sensor data as JSON via MQTTESP
// ---------------------------------------------------
// Simple JSON publishing without ArduinoJson
void publishJSON() {
  String payload = "{";
  payload += "\"suhu\":" + String(suhu) + ",";
  payload += "\"volume\":" + String(volume) + ",";
  payload += "\"kekeruhan\":" + String(kekeruhan) + ",";
  payload += "\"rssi\":" + String(WiFi.RSSI()) + ","; // Tambah sinyal WiFi
  payload += "\"status\":\"" + status + "\"}";
  mqtt.publish(topic_json, payload.c_str(), true, 1);
}

// ---------------------------------------------------
// Main loop: maintain MQTT, read sensors, update LCD, publish
// ---------------------------------------------------
void loop() {
  // Ensure MQTT connection (MQTTESP handles reconnection internally)
  if (!mqtt.connected()) {
    status = "Offline";
    mqtt.begin(); // reconnect
    mqtt.subscribe("kandang/control/buzzer"); // Langganan perintah dari web
    status = "Online";
  }
  mqtt.loop(); // process incoming MQTT traffic

  readSensors();
  updateLCD();
  publishJSON();

  delay(3000); // publish every 3 seconds
}