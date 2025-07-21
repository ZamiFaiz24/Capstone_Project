#include <Arduino.h>
#include "HX711.h"

// Ubah pin sesuai sambungan kamu
#define DOUT 13
#define CLK 14

HX711 scale;

float known_weight = 100.0; // berat referensi (gram)

void setup()
{
    Serial.begin(115200);
    Serial.println("=== KALIBRASI LOAD CELL ===");

    scale.begin(DOUT, CLK);
    delay(1000);

    Serial.println("Nolkan sensor (pastikan tidak ada beban)...");
    scale.set_scale(); // kosongkan scale
    scale.tare();      // nolkan

    Serial.println("Tare selesai. Sekarang letakkan beban 100 gram.");
    delay(5000); // beri waktu letakkan beban

    long reading = scale.get_units(10); // ambil rata-rata dari 10 bacaan
    Serial.print("Berat terbaca (sementara): ");
    Serial.print(reading);
    Serial.println(" gram");

    float new_scale = reading != 0 ? (reading / known_weight) : 1.0;
    Serial.print("Scale yang disarankan: ");
    Serial.println(new_scale, 5);

    scale.set_scale(new_scale); // atur scale baru

    Serial.println("\nKalibrasi selesai. Sekarang sensor aktif.");
}

void loop()
{
    Serial.print("Berat: ");
    Serial.print(scale.get_units(), 2);
    Serial.println(" gram");
    delay(1000);
}
