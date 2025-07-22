# 🧠 Capstone Project - Sistem Prediksi dan Monitoring Harga Telur

Proyek ini merupakan hasil dari tugas akhir (capstone project) yang menggabungkan beberapa teknologi seperti **IoT**, **machine learning (deep learning)**, dan **visualisasi data** untuk memantau serta memprediksi harga telur ayam ras konsumsi.

## 📁 Struktur Proyek
Capstone_Project/
├── capstone_project/ # Frontend aplikasi visualisasi data (Vue, Chart.js)
├── database/ # File SQL dan struktur database
├── deep_learning/ # Model prediksi harga (LSTM, dsb.)
├── iot_firmware/Endog/ # Firmware mikrokontroler IoT (suhu, berat telur, dll.)
├── prediksi_bi/ # Laporan Business Intelligence

## 🚀 Fitur Utama

- **Prediksi Harga Telur** menggunakan model deep learning (misalnya LSTM).
- **Monitoring Parameter IoT**: temperatur, kelembaban, berat telur, dan lainnya.
- **Visualisasi Data**: grafik harga aktual vs prediksi, rata-rata, tren bulanan.
- **Dashboard Interaktif** dibangun dengan Vue.js + Chart.js.
- **Database SQL** untuk menyimpan dan mengakses data historis.

## 🧠 Teknologi yang Digunakan

- **Vue.js** (frontend visualisasi)
- **Python** dengan TensorFlow / PyTorch (model prediksi)
- **NodeMCU / ESP32** (IoT firmware)
- **MySQL / SQLite** (basis data)
- **Chart.js** (grafik interaktif)

## 🛠️ Cara Menjalankan Proyek

### 1. Frontend (Visualisasi)
```bash
cd capstone_project
npm install
npm run dev
