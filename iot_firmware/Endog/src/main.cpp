#include <Arduino.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include "HX711.h"
#include <BH1750.h>
#include <ESP32Servo.h>

// ------------ PIN DEFINISI ------------
#define LCD_SDA 26
#define LCD_SCL 27

#define BH1750_SDA 33
#define BH1750_SCL 25

#define HX711_DOUT 13
#define HX711_SCK 14

#define SERVO1_PIN 18  // Servo 1: Telur busuk
#define SERVO2_PIN 19  // Servo 2: Telur bagus

#define LCD_COLUMNS 16
#define LCD_ROWS 2

// ------------ OBJEK SENSOR & SERVO ------------
HX711 scale;
BH1750 lightMeter;
LiquidCrystal_I2C lcd(0x27, LCD_COLUMNS, LCD_ROWS);

Servo myservo1;
Servo myservo2;

void setup() {
  Serial.begin(115200);

  // Inisialisasi LCD
  Wire.begin(LCD_SDA, LCD_SCL);
  lcd.begin(LCD_COLUMNS, LCD_ROWS);
  lcd.backlight();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Inisialisasi...");

  // Inisialisasi BH1750 di I2C ke-2
  Wire1.begin(BH1750_SDA, BH1750_SCL);
  if (lightMeter.begin(BH1750::CONTINUOUS_HIGH_RES_MODE, 0x23, &Wire1)) {
    Serial.println("BH1750 berhasil.");
  } else {
    Serial.println("BH1750 gagal!");
    lcd.setCursor(0, 1);
    lcd.print("BH1750 gagal!");
    while (1);
  }

  // Inisialisasi HX711
  scale.begin(HX711_DOUT, HX711_SCK);
  delay(1000);
  scale.set_scale(-1029.95); // Ganti dengan hasil kalibrasi Anda
  scale.tare();

  // Inisialisasi Servo
  myservo1.attach(SERVO1_PIN);
  myservo2.attach(SERVO2_PIN);
  myservo1.write(0);
  myservo2.write(0);
  delay(500);

  lcd.setCursor(0, 1);
  lcd.print("Sensor & Servo OK");
  delay(2000);
  lcd.clear();
}

void loop() {
  // Re-attach servo setiap loop (solusi agar tetap aktif di ESP32)
  myservo1.attach(SERVO1_PIN);
  myservo2.attach(SERVO2_PIN);

  float lux = lightMeter.readLightLevel();
  long berat = scale.get_units(10);  // hasil berat dalam gram

  // Tampilkan ke Serial Monitor
  Serial.print("Lux: ");
  Serial.print(lux);
  Serial.print(" | Berat: ");
  Serial.print(berat);
  Serial.println(" g");

  // Tampilkan ke LCD
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Lux: ");
  lcd.print((int)lux);

  lcd.setCursor(0, 1);
  lcd.print("Berat: ");
  lcd.print(berat);
  lcd.print("g");

  // Klasifikasi telur dan gerakkan servo
  if (lux > 500) {
    // Telur busuk → servo1 ke 90 derajat
    Serial.println("Telur Busuk → Servo1 ke 90");
    myservo1.write(90);
    delay(500);
    myservo1.write(0); // kembali ke posisi awal
  } else {
    if (berat > 50) {
      Serial.println("Telur Bagus Besar → Servo2 ke 90");
      myservo2.write(90);
      delay(500);
      myservo2.write(0); // kembali ke posisi awal
    } else {
      Serial.println("Telur Bagus Kecil → Servo2 ke 180");
      myservo2.write(180);
      delay(500);
      myservo2.write(0); // kembali ke posisi awal
    }
  }

  delay(2000); // Tunggu sebelum membaca ulang
}
