<template>
  <div class="border border-blue-600 rounded-lg overflow-hidden">
    <!-- Table Header -->
    <div class="flex bg-blue-500 border-b border-blue-600">
      <div
        v-for="col in columns"
        :key="col.key"
        :class="[
          col.width ? col.width : 'flex-1',
          'p-3 border-r border-blue-600 text-xs font-semibold text-white font-inter last:border-r-0',
          col.headerClass || ''
        ]"
      >
        {{ col.label }}
      </div>
    </div>

    <!-- Table Rows -->
    <div
      v-for="(row, rowIndex) in rows"
      :key="row.id || rowIndex"
      class="flex border-b border-blue-600 last:border-b-0 hover:bg-blue-100 transition"
    >
      <div
        v-for="col in columns"
        :key="col.key"
        :class="[
          col.width ? col.width : 'flex-1',
          'p-3 border-r border-blue-600 text-xs text-black font-inter last:border-r-0',
          col.cellClass || ''
        ]"
      >
        <!-- ⛏️ FIX: gunakan v-html jika ada col.render -->
        <span v-if="col.render" v-html="col.render(row)"></span>
        <span v-else>{{ row[col.key] }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Column {
  key: string;
  label: string;
  width?: string; // contoh: 'w-16' atau 'flex-1'
  headerClass?: string;
  cellClass?: string;
  render?: (row: any) => string; // output HTML string
}

defineProps<{
  columns: Column[];
  rows: any[];
}>();
</script>
