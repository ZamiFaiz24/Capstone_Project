<script setup lang="ts">
import { ref, reactive, computed } from "vue";
import AppLayout from "@/layouts/AppLayout.vue";
import DropdownFilter from "@/components/DropdownFilter.vue";
import Table from "@/components/Table.vue";

// TypeScript interfaces
interface HargaData {
  id: number;
  no: number;
  tanggal: string;
  harga: number;
  keterangan: "Naik" | "Turun" | "Stabil";
}
interface FilterOption {
  value: string;
  label: string;
}
interface FormData {
  harga: string;
  tanggal: string;
}

// Reactive data
const hargaData = ref<HargaData[]>([
  { id: 1, no: 1, tanggal: "2025-05-20", harga: 29000, keterangan: "Naik" },
  { id: 2, no: 2, tanggal: "2025-05-21", harga: 28800, keterangan: "Turun" },
  { id: 3, no: 3, tanggal: "2025-05-22", harga: 29200, keterangan: "Naik" },
  { id: 4, no: 4, tanggal: "2025-05-23", harga: 29000, keterangan: "Turun" },
  { id: 5, no: 5, tanggal: "2025-05-24", harga: 28900, keterangan: "Turun" },
  { id: 6, no: 6, tanggal: "2025-05-25", harga: 28700, keterangan: "Turun" },
  { id: 7, no: 7, tanggal: "2025-05-26", harga: 28800, keterangan: "Naik" },
  { id: 8, no: 8, tanggal: "2025-05-27", harga: 29100, keterangan: "Naik" },
]);

const formData = reactive<FormData>({
  harga: "",
  tanggal: "",
});

// Dropdown filter
const filterOptions: FilterOption[] = [
  { label: "Hari Ini", value: "today" },
  { label: "Kemarin", value: "yesterday" },
  { label: "Seminggu", value: "week" },
  { label: "Sebulan", value: "month" },
];
const selectedFilter = ref("");

const columns = [
  { key: "no", label: "No", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black border-r border-gray-300", cellClass: "px-3 py-3 text-xs text-black border-r border-gray-300" },
  { key: "tanggal", label: "Tanggal", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black border-r border-gray-300", cellClass: "px-3 py-3 text-xs text-black border-r border-gray-300" },
  { key: "harga", label: "Harga Telur/kg (Rp)", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black border-r border-gray-300", cellClass: "px-3 py-3 text-xs text-black border-r border-gray-300", render: (row: HargaData) => formatCurrency(row.harga) },
  { key: "keterangan", label: "Keterangan", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black", cellClass: "px-3 py-3 text-xs", render: (row: HargaData) => `<span class="${getKeteranganColor(row.keterangan)}">${row.keterangan}</span>` },
];

// Methods
const simpanData = () => {
  if (formData.harga && formData.tanggal) {
    const newData: HargaData = {
      id: hargaData.value.length + 1,
      no: hargaData.value.length + 1,
      tanggal: formData.tanggal,
      harga: parseInt(formData.harga),
      keterangan: "Stabil", // Default value, could be calculated
    };
    hargaData.value.push(newData);
    formData.harga = "";
    formData.tanggal = "";
  } else {
    alert("Mohon lengkapi semua field");
  }
};

const refreshData = () => {
  // Dalam aplikasi real, fetch data baru dari API
  alert("Data berhasil di-refresh!");
};

const exportData = () => {
  // Dalam aplikasi real, export data ke CSV/Excel
  alert("Data berhasil diekspor!");
};

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat("id-ID").format(amount);
}

function getKeteranganColor(keterangan: string): string {
  switch (keterangan.toLowerCase()) {
    case "naik":
      return "text-green-600 font-semibold";
    case "turun":
      return "text-red-600 font-semibold";
    case "stabil":
      return "text-blue-600 font-semibold";
    default:
      return "text-gray-600";
  }
}
</script>

<template>
  <AppLayout>
    <div class="w-full min-h-screen p-16">
      <!-- Page Title -->
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Laporan Sistem</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-12"></div>

      <!-- Harga Telur Section -->
      <div class="bg-white rounded-2xl border border-blue-700 p-6 mb-8">
        <h2 class="text-lg lg:text-xl font-bold text-black font-poppins mb-6">
          Harga Telur Ayam Harian/KG
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Kolom 1: Tombol referensi + Input Form -->
          <div class="bg-white p-6 flex flex-col gap-6">
            <div>
              <h3 class="text-base font-bold text-black font-poppins mb-4">
                Referensi Harga dari Web Resmi
              </h3>
              <a 
                href="https://simbok.kebumenkab.go.id" 
                target="_blank" 
                class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition mb-6"
              >
                Lihat Harga Pasar Hari Ini
              </a>
            </div>
            <div>
              <h3 class="text-base font-bold text-black font-poppins mb-6">
                Input Harga
              </h3>
              <div class="space-y-4">
                <!-- Harga Input -->
                <div>
                  <label class="block text-xs text-black font-poppins mb-2">
                    Harga Telur per KG
                  </label>
                  <input
                    v-model="formData.harga"
                    type="number"
                    class="w-full h-9 px-4 rounded-2xl border border-blue-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan harga"
                  />
                </div>
                <!-- Tanggal Input -->
                <div>
                  <label class="block text-xs text-black font-poppins mb-2">
                    Tanggal
                  </label>
                  <input
                    v-model="formData.tanggal"
                    type="date"
                    class="w-full h-9 px-4 rounded-2xl border border-blue-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <!-- Submit Button -->
                <button
                  @click="simpanData"
                  class="w-full h-9 bg-sky-300 text-black font-semibold font-poppins rounded-2xl hover:bg-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-500 mt-6"
                >
                  Simpan
                </button>
              </div>
            </div>
          </div>
          <!-- Kolom 2: Data Table -->
          <div class="flex flex-col gap-8">
            <!-- Controls -->
            <div class="flex flex-col sm:flex-row gap-3 mb-4">
              <DropdownFilter
                v-model="selectedFilter"
                :options="filterOptions"
                class="w-[140px]"
              />
              <button
                @click="refreshData"
                class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <span>Refresh Data</span>
              </button>
              <button
                @click="exportData"
                class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-800 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700"
              >
                <span>Ekspor Data</span>
              </button>
            </div>
            <div class="overflow-x-auto border border-blue-800 rounded-2xl">
              <Table :columns="columns" :rows="hargaData" />
            </div>
          </div>
        </div>
      </div>

      <!-- Laporan Sistem Section -->
      <div class="bg-white rounded-lg p-6">
        <div class="mb-6">
          <h2 class="text-2xl font-semibold text-black font-poppins mb-4">
            Laporan Sistem
          </h2>
          <div class="flex flex-col sm:flex-row gap-3">
            <DropdownFilter
              v-model="selectedFilter"
              :options="filterOptions"
              class="w-[140px]"
            />
            <button
              @click="refreshData"
              class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <span>Refresh Data</span>
            </button>
            <button
              @click="exportData"
              class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-800 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700"
            >
              <span>Ekspor Data</span>
            </button>
          </div>
        </div>
        <!-- Statistik Cards -->
        <div class="border border-gray-400 p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="text-xs font-semibold text-black font-['Inter']">
              Jumlah Produksi Telur
            </div>
            <div class="text-xs font-semibold text-black font-['Inter']">
              Perangkat dan Sensor
            </div>
            <div class="text-xs font-semibold text-black font-['Inter']">
              Harga Telur per KG
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Column 1: Produksi Telur -->
            <div class="space-y-4">
              <div class="bg-gray-300 p-4 rounded">
                <div class="flex items-center mb-2">
                  <svg class="w-3 h-1 mr-2" viewBox="0 0 12 2" fill="none">
                    <path d="M1 1H11" stroke="#0056B3" stroke-width="2" stroke-linecap="round" />
                  </svg>
                  <span class="text-xs text-black">Rata-rata Produksi Telur</span>
                </div>
                <div class="text-xs font-semibold text-black mb-1">
                  200 Butir dalam Periode Ini
                </div>
                <div class="text-xs text-black font-light">
                  Rata-rata periode ini terbilang Stabil
                </div>
              </div>
              <div class="bg-gray-300 p-4 rounded">
                <div class="flex items-center mb-2">
                  <svg class="w-3 h-2 mr-2" viewBox="0 0 16 10" fill="none">
                    <path d="M14 8L8 2L2 8" stroke="#0B8B1E" stroke-width="3" stroke-linecap="round" />
                  </svg>
                  <span class="text-xs text-black">Produksi Telur Tertinggi</span>
                </div>
                <div class="text-xs font-semibold text-black mb-1">
                  200 Butir dalam Periode Ini
                </div>
                <div class="text-xs text-black font-light">
                  Terjadi Pada Tanggal 21 Mei 2025
                </div>
              </div>
              <div class="bg-gray-300 p-4 rounded">
                <div class="flex items-center mb-2">
                  <svg class="w-3 h-2 mr-2" viewBox="0 0 16 10" fill="none">
                    <path d="M2 2L8 8L14 2" stroke="#CA3434" stroke-width="3" stroke-linecap="round" />
                  </svg>
                  <span class="text-xs text-black">Produksi Telur Terendah</span>
                </div>
                <div class="text-xs font-semibold text-black mb-1">
                  200 Butir dalam Periode Ini
                </div>
                <div class="text-xs text-black font-light">
                  Terjadi Pada Tanggal 21 Mei 2025
                </div>
              </div>
            </div>
            <!-- Column 2: Perangkat dan Sensor -->
            <div>
              <div class="bg-gray-300 p-4 rounded">
                <div class="flex items-center mb-2">
                  <svg class="w-3 h-2 mr-2" viewBox="0 0 16 10" fill="none">
                    <path d="M2 2L8 8L14 2" stroke="#CA3434" stroke-width="3" stroke-linecap="round" />
                  </svg>
                  <span class="text-xs text-black">Produksi Telur Tertinggi</span>
                </div>
                <div class="text-xs font-semibold text-black mb-1">
                  200 Butir dalam Periode Ini
                </div>
                <div class="text-xs text-black font-light">
                  Terjadi Pada Tanggal 21 Mei 2025
                </div>
              </div>
            </div>
            <!-- Column 3: Harga Telur -->
            <div>
              <div class="bg-gray-300 p-4 rounded">
                <div class="flex items-center mb-2">
                  <svg class="w-3 h-2 mr-2" viewBox="0 0 16 10" fill="none">
                    <path d="M2 2L8 8L14 2" stroke="#CA3434" stroke-width="3" stroke-linecap="round" />
                  </svg>
                  <span class="text-xs text-black">Produksi Telur Tertinggi</span>
                </div>
                <div class="text-xs font-semibold text-black mb-1">
                  200 Butir dalam Periode Ini
                </div>
                <div class="text-xs text-black font-light">
                  Terjadi Pada Tanggal 21 Mei 2025
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

