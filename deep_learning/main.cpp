#include <WiFi.h>
#include <HTTPClient.h>
#include <HX711.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>
#include <BH1750.h>

// ------------ PIN DEFINISI ------------
// --- LCD I2C (Tampilan Informasi) ---
#define LCD_SDA 26
#define LCD_SCL 27

// --- Sensor Cahaya BH1750 ---
#define BH1750_SDA 33
#define BH1750_SCL 25

// --- Sensor Berat HX711 ---
#define HX711_DOUT 13
#define HX711_SCK 14

// --- Indikator LED ---
#define LED_HIJAU 15 // LED Hijau: Idle
#define LED_MERAH 5  // LED Merah: Proses

// --- Servo Motor ---
#define SERVO1_PIN 18 // Servo 1: Mutu Tinggi
#define SERVO2_PIN 19 // Servo 2: Mutu Rendah
#define SERVO3_PIN 21 // Servo 3: Ukuran Besar (Mutu Tinggi)

// --- LCD Ukuran ---
#define LCD_COLUMNS 16
#define LCD_ROWS 2

// ------------ OBJEK LIBRARY ------------
HX711 scale;
LiquidCrystal_I2C lcd(0x27, LCD_COLUMNS, LCD_ROWS);
BH1750 lightMeter;
Servo servo1, servo2, servo3;

// ------------ SETTING WIFI ------------
const char *ssid = "Galaxy A52 3F11";
const char *password = "1234567A";

// ------------ API URL ------------
const char *status_url = "http://10.212.163.154:8000/api/status";
const char *kirim_data_url = "http://10.212.163.154:8000/api/sensor-data";

// ------------ FUNGSI ------------
String getStatusFromServer();
void prosesDataSensor(float berat, float intensitas);

void setup()
{
    Serial.begin(115200);
    delay(500);

    // LCD dan I2C
    Wire.begin(LCD_SDA, LCD_SCL);
    lcd.init();
    lcd.backlight();
    lcd.setCursor(0, 0);
    lcd.print("Menghubungkan...");

    // Sensor cahaya BH1750 di I2C kedua
    Wire1.begin(BH1750_SDA, BH1750_SCL);
    if (!lightMeter.begin(BH1750::CONTINUOUS_HIGH_RES_MODE, 0x23, &Wire1))
    {
        lcd.setCursor(0, 1);
        lcd.print("BH1750 Gagal!");
        while (1)
            ; // Hentikan sistem
    }

    // WiFi
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED)
    {
        delay(500);
        Serial.print(".");
    }
    lcd.clear();
    lcd.print("WiFi Terhubung");

    // Load Cell
    scale.begin(HX711_DOUT, HX711_SCK);
    delay(1000);
    scale.set_scale(-1029.95); // Ubah sesuai kalibrasi
    scale.tare();

    // Servo
    servo1.attach(SERVO1_PIN);
    servo2.attach(SERVO2_PIN);
    servo3.attach(SERVO3_PIN);
    servo1.write(0);
    servo2.write(0);
    servo3.write(0);

    // LED
    pinMode(LED_HIJAU, OUTPUT);
    pinMode(LED_MERAH, OUTPUT);
    digitalWrite(LED_HIJAU, HIGH);
    digitalWrite(LED_MERAH, LOW);

    lcd.setCursor(0, 1);
    lcd.print("Sistem Siap");
    delay(2000);
}

void loop()
{
    if (WiFi.status() != WL_CONNECTED)
    {
        WiFi.begin(ssid, password);
        lcd.clear();
        lcd.print("Reconnect WiFi...");
        delay(2000);
        return;
    }

    if (!scale.is_ready())
    {
        Serial.println("HX711 tidak siap.");
        lcd.clear();
        lcd.print("HX711 Error");
        delay(2000);
        return;
    }

    String status = getStatusFromServer();
    if (status == "ON")
    {
        float berat = scale.get_units(5);
        float intensitas_total = 0;
        for (int i = 0; i < 5; i++)
        {
            intensitas_total += lightMeter.readLightLevel();
            delay(100);
        }
        float intensitas = intensitas_total / 5.0;

        Serial.print("Berat: ");
        Serial.print(berat);
        Serial.print(" g | Lux: ");
        Serial.println(intensitas);

        if (berat >= 30.0)
        {
            digitalWrite(LED_HIJAU, LOW);
            digitalWrite(LED_MERAH, HIGH);

            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Berat:");
            lcd.print(berat, 1);
            lcd.setCursor(0, 1);
            lcd.print("Lux:");
            lcd.print((int)intensitas);

            prosesDataSensor(berat, intensitas);

            digitalWrite(LED_HIJAU, HIGH);
            digitalWrite(LED_MERAH, LOW);

            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Letakkan Telur");
            delay(3000);
        }
        else
        {
            Serial.println("Berat < 30g, abaikan");
        }
    }
    else
    {
        Serial.println("Status OFF");
    }

    delay(2000);
}

String getStatusFromServer()
{
    HTTPClient http;
    http.setTimeout(3000);
    http.begin(status_url);
    int httpCode = http.GET();

    String status = "OFF";
    if (httpCode == 200)
    {
        String payload = http.getString();
        if (payload.indexOf("ON") != -1)
            status = "ON";
    }

    http.end();
    return status;
}

void prosesDataSensor(float berat, float intensitas)
{
    HTTPClient http;
    http.setTimeout(5000);
    http.begin(kirim_data_url);
    http.addHeader("Content-Type", "application/json");

    String json = "{\"sensor_id\":\"ESP32_1\",\"berat\":" + String(berat, 2) + ",\"intensitas\":" + String(intensitas, 2) + "}";
    Serial.println("🔁 Mengirim JSON ke backend:");
    Serial.println(json);

    int httpCode = http.POST(json);

    if (httpCode == 200)
    {
        String response = http.getString();
        Serial.println("✅ Respon dari server:");
        Serial.println(response);

        DynamicJsonDocument doc(256);
        DeserializationError error = deserializeJson(doc, response);

        if (!error)
        {
            Serial.println("✅ JSON berhasil di-parse");

            int klaster = doc["klaster"] | -1;
            Serial.println("📦 Klaster diterima: " + String(klaster));

            String ukuran = "Unknown";
            String mutu = "Unknown";

            // Attach ulang untuk memastikan servo aktif
            servo1.attach(SERVO1_PIN);
            servo2.attach(SERVO2_PIN);
            servo3.attach(SERVO3_PIN);

            if (klaster == 0)
            {
                ukuran = "Kecil";
                mutu = "Mutu Tinggi";
                Serial.println("🔧 Servo1 aktif (klaster 0)");
                servo1.write(90);
                delay(1500);
                servo1.write(0);
                servo3.write(0);
            }
            else if (klaster == 1)
            {
                ukuran = "Besar";
                mutu = "Mutu Rendah";
                Serial.println("🔧 Servo2 aktif (klaster 1)");
                servo2.write(90);
                delay(1500);
                servo2.write(0);
            }
            else if (klaster == 2)
            {
                ukuran = "Besar";
                mutu = "Mutu Tinggi";
                Serial.println("🔧 Servo1 + Servo3 aktif (klaster 2)");
                servo1.write(90);
                servo3.write(60);
                delay(1500);
                servo1.write(0);
                servo3.write(0);
            }
            else if (klaster == 3)
            {
                ukuran = "Kecil";
                mutu = "Mutu Rendah";
                Serial.println("🔧 Servo2 aktif (klaster 3)");
                servo2.write(90);
                delay(1500);
                servo2.write(0);
            }
            else
            {
                Serial.println("❗ Klaster tidak dikenali: " + String(klaster));
            }

            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print(ukuran);
            lcd.setCursor(0, 1);
            lcd.print(mutu);
            delay(2000);
        }
        else
        {
            Serial.print("❌ Gagal parsing JSON: ");
            Serial.println(error.c_str());
        }
    }
    else
    {
        Serial.println("❌ Gagal kirim data: HTTP Code = " + String(httpCode));
    }

    http.end();
}
