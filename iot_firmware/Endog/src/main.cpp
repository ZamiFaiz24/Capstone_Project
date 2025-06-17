#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include "HX711.h"

#define DT_PIN 13
#define SCK_PIN 14

HX711 scale;
LiquidCrystal_I2C lcd(0x27, 16, 2);  // Ganti 0x27 ke 0x3F jika perlu

void setup()
{
  Serial.begin(115200);

  Wire.begin(19, 18);  // I2C SDA = 19, SCL = 18
  lcd.begin(16, 2);  
  lcd.backlight();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Memulai...");

  scale.begin(DT_PIN, SCK_PIN);
  delay(1000);

  scale.set_scale(541.33);  // GANTI dengan nilai dari hasil kalibrasi
  scale.tare();  // Nolkan timbangan

  lcd.setCursor(0, 1);
  lcd.print("Kalibrasi OK");
  Serial.println("Kalibrasi selesai. Silakan letakkan telur.");
}

void loop()
{
  if (scale.is_ready())
  {
    long reading = scale.get_units(10);
    Serial.print("Berat telur: ");
    Serial.print(reading);
    Serial.println(" gram");

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Berat: ");
    lcd.print(reading);
    lcd.print("g");

    lcd.setCursor(0, 1);
    if (reading < 50)
    {
      Serial.println("Kategori: ringan");
      lcd.print("Telur ringan");
    }
    else if (reading < 65)
    {
      Serial.println("Kategori: sedang");
      lcd.print("Telur sedang");
    }
    else
    {
      Serial.println("Kategori: berat");
      lcd.print("Telur berat");
    }
  }
  else
  {
    Serial.println("HX711 tidak siap.");
    lcd.setCursor(0, 0);
    lcd.print("HX711 tidak siap");
  }

  delay(2000);  // Waktu tunggu agar LCD dapat terbaca
}
