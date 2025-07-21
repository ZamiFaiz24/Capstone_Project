<script setup lang="ts">
import { ref, onMounted, computed, watch, nextTick } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import DropdownFilter from "@/components/DropdownFilter.vue";
import Table from "@/components/Table.vue";
import Pagination from "@/components/Pagination.vue";
import ExportModal from '@/components/ExportModal.vue'
import { RefreshCcw, FileDown, ChevronUp, ChevronDown, Minus } from 'lucide-vue-next';
import Swal, { SweetAlertIcon } from 'sweetalert2';
import { useToast, POSITION } from "vue-toastification"
import * as XLSX from "xlsx";
import { saveAs } from 'file-saver';
import axios from 'axios';

const toast = useToast()

// Props dari Laravel (Inertia)
interface HargaData {
  id: number;
  tanggal: string;
  harga: number;
  keterangan: "Naik" | "Turun" | "Stabil";
  no?: number; 
}

const props = defineProps<{
  hargaData: HargaData[]
}>()

const currentPage = ref(1)
const itemsPerPage = 10

const hargaList = computed(() => {
  // Urutkan dari tanggal terbaru ke terlama
  return [...props.hargaData]
    .sort((a, b) => new Date(b.tanggal).getTime() - new Date(a.tanggal).getTime())
    .map((item, index) => ({ ...item, no: index + 1 }))
})

const produksi = ref({
  rata: 0,
  tertinggi: 0,
  tanggal_tertinggi: '',
  terendah: 0,
  tanggal_terendah: '',
  total:0
});

const harga = ref({
  sekarang: 0,
  kemarin: 0,
  tertinggi: 0,
  tanggal_tertinggi: '',
  terendah: 0,
  tanggal_terendah: '',
  status: '', // naik / turun / stabil
  selisih: 0 // persen
})

const pendapatan = ref({
  total: 0,
  tertinggi: 0,
  tanggal_tertinggi: '',
  terendah: 0,
  tanggal_terendah: ''
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return hargaList.value.slice(start, start + itemsPerPage)
})

const form = useForm({
  harga: '',
  tanggal: ''
})

const simpanData = () => {
  if (!form.harga || !form.tanggal) {
    alert("Mohon lengkapi semua field")
    return
  }

  form.post(route('admin.harga.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      currentPage.value = 1
      toast.success("Data harga berhasil ditambahkan!", {
        position: POSITION.TOP_CENTER,
      })
    },
    onError: () => {
      toast.error("Gagal menyimpan data.", {
        position: POSITION.TOP_CENTER,
      })
    }
  })
}

interface FilterOption {
  value: string;
  label: string;
}

const selectedFilter = ref("");

// Dropdown filter
const filterOptions: FilterOption[] = [
  { label: "Hari Ini", value: "today" },
  { label: "Seminggu", value: "week" },
  { label: "Sebulan", value: "month" },
];

const isRefreshing = ref(false)

const refreshData = async () => {
  isRefreshing.value = true

  try {
    await ambilHarga() // fungsi ambil ulang data harga dari API
    // Tambahkan juga ambilDataLain() jika perlu

    toast.success('Data laporan & tabel berhasil diperbarui!', {
      position: POSITION.TOP_CENTER
    })
  } catch (err) {
    toast.error('Gagal memperbarui data.', {
      position: POSITION.TOP_CENTER
    })
    console.error(err)
  } finally {
    setTimeout(() => {
      isRefreshing.value = false
    }, 500)
  }
}


function formatTanggal(dateString: string): string {
  const date = new Date(dateString);

  const day = String(date.getDate()).padStart(2, '0');
  const monthNames = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];
  const month = monthNames[date.getMonth()];
  const year = date.getFullYear();

  return `${day} ${month} ${year}`;
}

const columns = [
  { key: "no", label: "No", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black border-r border-gray-300", cellClass: "px-3 py-3 text-xs text-black border-r border-gray-300",  },
  { key: "tanggal", label: "Tanggal", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black border-r border-gray-300", cellClass: "px-3 py-3 text-xs text-black border-r border-gray-300", render: (row: HargaData) => formatTanggal(row.tanggal)
},
  { key: "harga", label: "Harga Telur/kg", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black border-r border-gray-300", cellClass: "px-3 py-3 text-xs text-black border-r border-gray-300", render: (row: HargaData) => `Rp ${row.harga.toLocaleString('id-ID')}`
},
  { key: "keterangan", label: "Keterangan", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black", cellClass: "px-3 py-3 text-xs",  render: (row: HargaData) => {
    const icon = row.keterangan === 'Naik'
      ? '▲'
      : row.keterangan === 'Turun'
      ? '▼'
      : '–'

    return `<span style="color:${
      row.keterangan === 'Naik'
        ? 'green'
        : row.keterangan === 'Turun'
        ? 'red'
        : 'blue'
    }">${icon} ${row.keterangan}</span>`
  } },
];

const exportModalRef = ref()

const openExportModal = () => {
  exportModalRef.value?.open()
}

const exportData = () => {
  const groupedByMonth = props.hargaData.reduce((acc, item) => {
    const date = new Date(item.tanggal)
    const month = date.toLocaleDateString('id-ID', {
      month: 'long',
      year: 'numeric',
    }) // Contoh: "Maret 2025"
    if (!acc[month]) acc[month] = []
    acc[month].push(item)
    return acc
  }, {} as Record<string, HargaData[]>)

  // Buat workbook
  const wb = XLSX.utils.book_new()

  // Loop setiap bulan → Sheet sendiri
  Object.entries(groupedByMonth).forEach(([month, data]) => {
    const formatted = data.map((row, i) => ({
      No: i + 1,
      Tanggal: new Date(row.tanggal).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
      }),
      'Harga Telur (Rp)': `Rp ${row.harga.toLocaleString('id-ID')}`,
      Keterangan: row.keterangan,
    }))
    const ws = XLSX.utils.json_to_sheet(formatted)
    XLSX.utils.book_append_sheet(wb, ws, month)
  })

  XLSX.writeFile(wb, 'laporan_harga_telur_per_bulan.xlsx')

  const modal = document.getElementById('export_modal') as HTMLDialogElement
  if (modal && modal.open) {
    modal.close()
  }
}

// Fetch pendapatan berdasarkan filter
const fetchPendapatan = async () => {
  try {
    const res = await axios.get('/api/laporan/pendapatan', {
      params: { periode: selectedFilter.value || 'month' } // default ke bulan
    });
    pendapatan.value = res.data;
  } catch (error) {
    console.error("Gagal fetch pendapatan", error);
    toast.error("Gagal mengambil data pendapatan");
  }
};

const fetchProduksi = async () => {
  try {
    const res = await axios.get('/api/laporan/produksi', {
      params: { periode: selectedFilter.value || 'month' }
    });
    produksi.value = res.data;
  } catch (e) {
    console.error("Gagal fetch data produksi", e);
  }
};

async function ambilHarga() {
  try {
    const res = await axios.get('/api/laporan/harga', {
      params: { periode: selectedFilter.value || 'month' }
    })
    const now = res.data.harga_sekarang
    const yesterday = res.data.harga_kemarin

    let status = 'stabil'
    let selisih = 0

    if (now > yesterday) {
      status = 'naik'
      selisih = ((now - yesterday) / yesterday) * 100
    } else if (now < yesterday) {
      status = 'turun'
      selisih = ((yesterday - now) / yesterday) * 100
    }

    harga.value = {
      sekarang: now,
      kemarin: yesterday,
      tertinggi: res.data.harga_tertinggi,
      tanggal_tertinggi: res.data.tanggal_tertinggi,
      terendah: res.data.harga_terendah,
      tanggal_terendah: res.data.tanggal_terendah,
      status,
      selisih
    }
  } catch (error) {
    console.error('Gagal ambil data harga:', error)
  }
}

function showCardInsight(type: string) {
  let title = '';
  let html = '';
  let icon: SweetAlertIcon = 'info';

  switch (type) {
    case 'produksi-rata':
      title = '📊 Rata-rata Produksi Telur';
      html = `<div class="text-sm text-gray-700">
        Rata-rata produksi telur stabil. Pertahankan pola pakan dan kebersihan kandang untuk menjaga hasil panen.
      </div>`;
      break;
    case 'produksi-tertinggi':
      title = '📈 Produksi Tertinggi';
      html = `<div class="text-sm text-gray-700">
        Produksi tertinggi menandakan performa kandang optimal. Lanjutkan pola pakan & ventilasi yang sama.
      </div>`;
      break;
    case 'produksi-terendah':
      title = '📉 Produksi Terendah';
      html = `<div class="text-sm text-gray-700">
        Produksi terendah terjadi kemungkinan karena perubahan cuaca atau kesehatan ayam. Lakukan pengecekan rutin.
      </div>`;
      icon = 'warning';
      break;
      case 'harga-saat-ini':
  title = '💰 Harga Telur Saat Ini';

  if (harga.value.status === 'naik') {
    html = `<div class="text-sm text-gray-700">Harga telur <span class="font-semibold text-green-700">naik</span> sebesar ${harga.value.selisih.toFixed(1)}% dibanding kemarin. Pertimbangkan distribusi lebih banyak.</div>`;
    icon = 'success';
  } else if (harga.value.status === 'turun') {
    html = `<div class="text-sm text-gray-700">Harga telur <span class="font-semibold text-red-600">turun</span> sebesar ${harga.value.selisih.toFixed(1)}% dibanding kemarin. Evaluasi strategi penjualan.</div>`;
    icon = 'warning';
  } else {
    html = `<div class="text-sm text-gray-700">Harga telur <span class="font-semibold text-yellow-600">stabil</span> dibanding kemarin. Produksi dan distribusi bisa berjalan seperti biasa.</div>`;
    icon = 'info';
  }
  break;
    case 'harga-tertinggi':
      title = '📈 Harga Tertinggi';
      html = `<div class="text-sm text-gray-700">
        Harga tertinggi bulan ini, maksimalkan distribusi dan penjualan pada momen seperti ini.
      </div>`;
      break;
    case 'harga-terendah':
      title = '📉 Harga Terendah';
      html = `<div class="text-sm text-gray-700">
        Harga terendah, pertimbangkan strategi stok atau tunda penjualan jika memungkinkan.
      </div>`;
      icon = 'warning';
      break;
    case 'pendapatan-total':
      title = '💵 Pendapatan Total';
      html = `<div class="text-sm text-gray-700">
        Pendapatan stabil, pertahankan efisiensi produksi dan distribusi.
      </div>`;
      break;
    case 'pendapatan-tertinggi':
      title = '📈 Pendapatan Tertinggi';
      html = `<div class="text-sm text-gray-700">
        Pendapatan tertinggi, manfaatkan momentum untuk investasi perbaikan kandang.
      </div>`;
      break;
    case 'pendapatan-terendah':
      title = '📉 Pendapatan Terendah';
      html = `<div class="text-sm text-gray-700">
        Pendapatan menurun, evaluasi strategi penjualan dan biaya operasional.
      </div>`;
      icon = 'warning';
      break;
    default:
      title = 'Insight';
      html = `<div class="text-sm text-gray-700">Tidak ada insight khusus.</div>`;
  }

  Swal.fire({
    title,
    html,
    icon,
    confirmButtonText: 'Tutup',
    customClass: {
      popup: 'rounded-xl shadow-2xl animate__animated animate__fadeInDown',
      title: 'font-bold text-blue-700 text-lg',
      confirmButton: 'bg-blue-600 text-white rounded px-6 py-2 mt-4 font-semibold hover:bg-blue-700 transition'
    },
    showClass: {
      popup: 'animate__animated animate__fadeInDown'
    },
    hideClass: {
      popup: 'animate__animated animate__fadeOutUp'
    },
    background: '#f9fafb',
    width: 420
  });
}

function formatRupiah(angka: number | string | null): string {
  if (!angka) return '-';
  return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function tanggalIndo(dateStr: string): string {
  if (!dateStr) return '-';

  const bulan = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];

  const date = new Date(dateStr);
  const day = date.getDate();
  const month = bulan[date.getMonth()];
  return `${day} ${month}`;
}


async function exportRingkasanExcel() {
  const periode = selectedFilter.value.toLowerCase();

  try {
    // Ambil semua data sekaligus
    const [resHarga, resProduksi, resPendapatan] = await Promise.all([
      axios.get(`/api/laporan/harga?periode=${periode}`),
      axios.get(`/api/laporan/produksi?periode=${periode}`),
      axios.get(`/api/laporan/pendapatan?periode=${periode}`)
    ]);

    const harga = resHarga.data;
    const produksi = resProduksi.data;
    const pendapatan = resPendapatan.data;

    const laporan = {
      produksi: {
        total: produksi.total,
        rata_status: produksi.rata_status, // "meningkat", "menurun", "stabil"
        tertinggi: produksi.tertinggi,
        tanggal_tertinggi: produksi.tanggal_tertinggi,
        terendah: produksi.terendah,
        tanggal_terendah: produksi.tanggal_terendah,
      },
      klaster: {
        besar: 130, // jika sudah ada endpoint klaster, ambil dari sana
        sedang: 50,
        kecil: 20,
      },
      harga: {
        sekarang: harga.harga_sekarang,
        tertinggi: harga.harga_tertinggi,
        tanggal_tertinggi: harga.tanggal_tertinggi,
        terendah: harga.harga_terendah,
        tanggal_terendah: harga.tanggal_terendah,
      },
      pendapatan: {
        total: pendapatan.total,
        total_status: pendapatan.total_status,
        rata: pendapatan.rata,
        rata_status: pendapatan.rata_status,
        tertinggi: pendapatan.tertinggi,
        tanggal_tertinggi: pendapatan.tanggal_tertinggi,
        terendah: pendapatan.terendah,
        tanggal_terendah: pendapatan.tanggal_terendah,
      },
      rekomendasi: [
        'Pertahankan pola pakan harian.',
        'Pastikan perangkat aktif 24 jam.',
        'Gunakan momen harga tinggi untuk distribusi besar.',
      ],
    };

    const data = [
      [`LAPORAN SISTEM - PERIODE ${periode.toUpperCase()}`],
      [],
      ['📦 Produksi Telur'],
      ['Rata-rata', `${laporan.produksi.total} Butir (${laporan.produksi.rata_status})`],
      ['Tertinggi', `${laporan.produksi.tertinggi} Butir (${laporan.produksi.tanggal_tertinggi})`],
      ['Terendah', `${laporan.produksi.terendah} Butir (${laporan.produksi.tanggal_terendah})`],
      [],
      ['🥚 Hasil Klasterisasi Telur'],
      ['Telur Besar', `${laporan.klaster.besar} Butir`],
      ['Telur Sedang', `${laporan.klaster.sedang} Butir`],
      ['Telur Kecil', `${laporan.klaster.kecil} Butir`],
      [],
      ['💰 Harga Telur'],
      ['Harga Saat Ini', formatRupiah(laporan.harga.sekarang)],
      ['Tertinggi', `${formatRupiah(laporan.harga.tertinggi)} (${tanggalIndo(laporan.harga.tanggal_tertinggi)})`],
      ['Terendah', `${formatRupiah(laporan.harga.terendah)} (${tanggalIndo(laporan.harga.tanggal_terendah)})`],
      [],
      ['🧾 Pendapatan'],
      ['Total', `${formatRupiah(laporan.pendapatan.total)} (${laporan.pendapatan.total_status})`],
      ['Rata-rata Harian', `${formatRupiah(laporan.pendapatan.rata)} (${laporan.pendapatan.rata_status})`],
      ['Tertinggi', `${formatRupiah(laporan.pendapatan.tertinggi)} (${laporan.pendapatan.tanggal_tertinggi})`],
      ['Terendah', `${formatRupiah(laporan.pendapatan.terendah)} (${laporan.pendapatan.tanggal_terendah})`],
      [],
      ['📌 Rekomendasi Sistem'],
      ...laporan.rekomendasi.map((item) => [item]),
    ];

    const worksheet = XLSX.utils.aoa_to_sheet(data);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Laporan");

    const excelBuffer = XLSX.write(workbook, { bookType: "xlsx", type: "array" });
    const blob = new Blob([excelBuffer], { type: "application/octet-stream" });
    saveAs(blob, `Laporan_Sistem_${periode}.xlsx`);
  } catch (error) {
    console.error("❌ Gagal ekspor laporan:", error);
  }
}

watch(selectedFilter, async () => {
  await nextTick() // pastikan nilai sudah benar-benar berubah
  await fetchProduksi()
  await ambilHarga()
  await fetchPendapatan()
})


onMounted(() => {
  selectedFilter.value = 'today';
  fetchProduksi();
  ambilHarga();
  fetchPendapatan();
});

</script>

<template>
  <AppLayout>
    <div class="w-full min-h-screen px-4 md:px-8 lg:px-16 pt-4">
      <!-- Page Title -->
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Laporan Sistem</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-12"></div>

      <!-- Harga Telur Section -->
      <div class="bg-white rounded-2xl border border-blue-700 p-6 mb-8">
        <h2 class="text-lg lg:text-xl font-bold text-black font-poppins mb-6">
          Harga Telur Ayam Negeri Harian/KG
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-10 gap-8">
          <!-- Kolom 1: Tombol referensi + Input Form (30%) -->
          <div class="bg-white p-6 lg:col-span-3 gap-6">
            <div>
              <h3 class="text-base font-bold text-black font-poppins mb-2">
                Referensi Harga dari:
              </h3>
              <h4 class="text-xs text-black font-poppins mb-4">
                SIMBOK (Sistem Informasi Manajemen Bahan Pokok) Kabupaten Kebumen
              </h4>
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
              <form @submit.prevent="simpanData">
                <div class="space-y-4">
                  <div>
                    <label class="block text-xs text-black font-poppins mb-2">
                      Harga Telur per KG
                    </label>
                    <div class="relative">
                      <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm text-gray-600 font-semibold">Rp</span>
                      <input
                        v-model="form.harga"
                        type="number"
                        class="w-full h-9 pl-10 pr-4 rounded-2xl text-black border border-blue-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Masukkan harga"
                      />
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs text-black font-poppins mb-2">
                      Tanggal
                    </label>
                    <input
                      v-model="form.tanggal"
                      type="date"
                      class="w-full h-9 px-4 rounded-2xl text-gray-500 border border-blue-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <button
                    type="submit"
                    class="w-full h-9 bg-blue-500 text-white font-semibold font-poppins rounded-2xl hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-sky-500 mt-6"
                  >
                    Simpan
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Kolom 2: Data Table (70%) -->
          <div class="lg:col-span-7 gap-8">
            <!-- Controls -->
            <div class="flex flex-col justify-end sm:flex-row gap-3 mb-4">
              <button
                @click="refreshData"
                class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <span>Refresh Data</span>
                <span :class="isRefreshing ? 'animate-spin' : ''">
                  <RefreshCcw class="w-4 h-4" />
                </span>
              </button>
              <button
                @click="openExportModal"
                class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-800 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700"
              >
                <span>Ekspor Data</span>
                <FileDown class="w-3 h-4" />
              </button>
            </div>
            <ExportModal ref="exportModalRef" :onConfirm="exportData" />

            <!-- Table -->
            <div class="overflow-x-auto border border-blue-800 rounded-2xl">
              <Table :columns="columns" :rows="paginatedData" />
            </div>

            <!-- Pagination -->
            <div>
              <Pagination
                :total-items="hargaList.length"
                :items-per-page="itemsPerPage"
                :current-page="currentPage"
                @update:currentPage="(page) => currentPage = page"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Laporan Sistem Section -->
      <div class="bg-white rounded-2xl border border-blue-700 p-6 mt-8">
        <div class="mb-6">
          <h2 class="text-lg lg:text-xl font-bold text-black font-poppins mb-6">
          Laporan Sistem Klasterisasi Telur
          </h2>
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
              <span :class="isRefreshing ? 'animate-spin' : ''">
                <RefreshCcw class="w-4 h-4" />
              </span>
            </button>
            <button
              @click="exportRingkasanExcel"
              class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-800 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700"
            >
              <FileDown class="w-4 h-4" /> Ekspor Data
            </button>
          </div>
          <ExportModal ref="exportModalRef" :onConfirm="exportData" />
        </div>

        <!-- Statistik 3 Kolom -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- Kolom 1: Jumlah Produksi Telur -->
          <div class="flex flex-col gap-4">
            <h3 class="text-sm font-semibold text-gray-700">Jumlah Produksi Telur</h3>

            <div
              class="border rounded-md p-4 bg-white shadow-sm cursor-pointer hover:bg-blue-50 transition"
              @click="showCardInsight('produksi-rata')"
            >
              <p class="text-sm text-gray-600">📊 Total Produksi Telur</p>
              <p class="text-blue-800 font-bold text-lg"> {{ produksi.total }} Butir</p>
              <p class="text-xs text-gray-500">⚖️ Stabil dibanding periode sebelumnya</p>
            </div>

            <div
              class="border rounded-md p-4 bg-white shadow-sm cursor-pointer hover:bg-blue-50 transition"
              @click="showCardInsight('produksi-tertinggi')"
            >
              <p class="text-sm text-gray-600">📈 Produksi Tertinggi</p>
              <p class="text-green-700 font-bold text-lg">
                {{ produksi.tertinggi }} Butir
              </p>
              <p class="text-xs text-gray-500">📅 {{ tanggalIndo(produksi.tanggal_tertinggi) }}</p>
            </div>

            <div
              class="border rounded-md p-4 bg-white shadow-sm cursor-pointer hover:bg-blue-50 transition"
              @click="showCardInsight('produksi-terendah')"
            >
              <p class="text-sm text-gray-600">📉 Produksi Terendah</p>
              <p class="text-red-600 font-bold text-lg">
                {{ produksi.terendah }} Butir
              </p>
              <p class="text-xs text-gray-500">📅 {{ tanggalIndo(produksi.tanggal_terendah) }}</p>
            </div>
          </div>

          <!-- Kolom 2: Harga Telur per KG -->
          <div class="flex flex-col gap-4">
            <h3 class="text-sm font-semibold text-gray-700">Harga Telur per KG</h3>

            <!-- Harga Saat Ini -->
            <div
              class="border rounded-md p-4 bg-white shadow-sm cursor-pointer hover:bg-yellow-50 transition"
              @click="showCardInsight('harga-saat-ini')"
            >
              <p class="text-sm text-gray-600">💰 Harga Saat Ini</p>
              <p class="text-yellow-700 font-bold text-lg">{{ formatRupiah(harga.sekarang) }}</p>

              <p class="text-xs text-gray-500">
                <template v-if="harga.status === 'naik'">
                  ⬆️ Naik {{ harga.selisih.toFixed(1) }}% dari kemarin
                </template>
                <template v-else-if="harga.status === 'turun'">
                  ⬇️ Turun {{ harga.selisih.toFixed(1) }}% dari kemarin
                </template>
                <template v-else>
                  ➖ Stabil dibanding kemarin
                </template>
              </p>
            </div>

            <!-- Harga Tertinggi -->
            <div
              class="border rounded-md p-4 bg-white shadow-sm cursor-pointer hover:bg-yellow-50 transition"
              @click="showCardInsight('harga-tertinggi')"
            >
              <p class="text-sm text-gray-600">📈 Harga Tertinggi</p>
              <p class="text-green-700 font-bold text-lg">
                {{ formatRupiah(harga.tertinggi) }}
              </p>
              <p class="text-xs text-gray-500">📅 {{ tanggalIndo(harga.tanggal_tertinggi) }}</p>
            </div>

            <!-- Harga Terendah -->
            <div
              class="border rounded-md p-4 bg-white shadow-sm cursor-pointer hover:bg-yellow-50 transition"
              @click="showCardInsight('harga-terendah')"
            >
              <p class="text-sm text-gray-600">📉 Harga Terendah</p>
              <p class="text-red-600 font-bold text-lg">
                {{ formatRupiah(harga.terendah) }}
              </p>
              <p class="text-xs text-gray-500">📅 {{ tanggalIndo(harga.tanggal_terendah) }}</p>
            </div>
          </div>

          <!-- Kolom 3: Pendapatan -->
          <div class="flex flex-col gap-4">
            <h3 class="text-sm font-semibold text-gray-700">Pendapatan</h3>

            <div
              class="border rounded-md p-4 bg-white shadow-sm cursor-pointer hover:bg-green-50 transition"
              @click="showCardInsight('pendapatan-total')"
            >
              <p class="text-sm text-gray-600">💵 Pendapatan Total</p>
              <p class="text-blue-800 font-bold text-lg"> {{ formatRupiah(pendapatan.total) }}</p>
              <p class="text-xs text-gray-500">⚖️ Stabil dari bulan lalu</p>
            </div>

            <div
              class="border rounded-md p-4 bg-white shadow-sm cursor-pointer hover:bg-green-50 transition"
              @click="showCardInsight('pendapatan-tertinggi')"
            >
              <p class="text-sm text-gray-600">📈 Pendapatan Tertinggi</p>
              <p class="text-green-700 font-bold text-lg">{{ formatRupiah(pendapatan.tertinggi) }}</p>
              <p class="text-xs text-gray-500">📅 {{ tanggalIndo(pendapatan.tanggal_tertinggi) }}</p>
            </div>

            <div
              class="border rounded-md p-4 bg-white shadow-sm cursor-pointer hover:bg-green-50 transition"
              @click="showCardInsight('pendapatan-terendah')"
            >
              <p class="text-sm text-gray-600">📉 Pendapatan Terendah</p>
              <p class="text-green-700 font-bold text-lg">{{ formatRupiah(pendapatan.terendah) }}</p>
              <p class="text-xs text-gray-500">📅 {{ tanggalIndo(pendapatan.tanggal_terendah) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>