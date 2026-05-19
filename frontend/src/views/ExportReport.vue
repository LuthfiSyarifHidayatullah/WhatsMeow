<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Export Laporan</h1>
      <p class="text-gray-500 text-sm">Download rekapan data ke Excel</p>
    </div>

    <!-- Export Options -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <!-- Topik Terbanyak -->
      <div class="card">
        <h3 class="text-lg font-semibold mb-3">📊 Topik Terbanyak Ditanyakan</h3>
        <p class="text-sm text-gray-500 mb-4">Rekapan topik/pertanyaan paling sering dari pengunjung</p>
        <div class="flex gap-2 mb-3">
          <button @click="loadReport('topics', 'week')" :class="period === 'week' && type === 'topics' ? 'btn-primary' : 'btn-secondary'" class="text-xs">Minggu Ini</button>
          <button @click="loadReport('topics', 'month')" :class="period === 'month' && type === 'topics' ? 'btn-primary' : 'btn-secondary'" class="text-xs">Bulan Ini</button>
          <button @click="loadReport('topics', 'year')" :class="period === 'year' && type === 'topics' ? 'btn-primary' : 'btn-secondary'" class="text-xs">Tahun Ini</button>
        </div>
        <div class="flex gap-2 mb-4">
          <input v-model="dateFrom" type="date" class="input-field text-xs flex-1" />
          <input v-model="dateTo" type="date" class="input-field text-xs flex-1" />
          <button @click="loadReportCustom('topics')" class="btn-secondary text-xs">Custom</button>
        </div>
        <button v-if="reportData && type === 'topics'" @click="downloadExcel('topics')" class="btn-success text-sm w-full">
          📥 Download Excel - Topik
        </button>
      </div>

      <!-- Rating Per Layanan -->
      <div class="card">
        <h3 class="text-lg font-semibold mb-3">⭐ Rating Per Layanan</h3>
        <p class="text-sm text-gray-500 mb-4">Rekapan rating kepuasan per layanan dan petugas</p>
        <div class="flex gap-2 mb-3">
          <button @click="loadReport('ratings', 'week')" :class="period === 'week' && type === 'ratings' ? 'btn-primary' : 'btn-secondary'" class="text-xs">Minggu Ini</button>
          <button @click="loadReport('ratings', 'month')" :class="period === 'month' && type === 'ratings' ? 'btn-primary' : 'btn-secondary'" class="text-xs">Bulan Ini</button>
          <button @click="loadReport('ratings', 'year')" :class="period === 'year' && type === 'ratings' ? 'btn-primary' : 'btn-secondary'" class="text-xs">Tahun Ini</button>
        </div>
        <div class="flex gap-2 mb-4">
          <input v-model="dateFrom" type="date" class="input-field text-xs flex-1" />
          <input v-model="dateTo" type="date" class="input-field text-xs flex-1" />
          <button @click="loadReportCustom('ratings')" class="btn-secondary text-xs">Custom</button>
        </div>
        <button v-if="reportData && type === 'ratings'" @click="downloadExcel('ratings')" class="btn-success text-sm w-full">
          📥 Download Excel - Rating
        </button>
      </div>
    </div>

    <!-- Preview Data -->
    <div v-if="reportData" class="card">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">Preview Data</h3>
        <div class="text-sm text-gray-500">
          {{ reportData.summary.start_date }} - {{ reportData.summary.end_date }}
          | Total: {{ reportData.data.length }} data
        </div>
      </div>

      <!-- Topics Preview -->
      <div v-if="type === 'topics'" class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-2 px-3 font-medium text-gray-600">No</th>
              <th class="text-left py-2 px-3 font-medium text-gray-600">Topik</th>
              <th class="text-left py-2 px-3 font-medium text-gray-600">Layanan</th>
              <th class="text-center py-2 px-3 font-medium text-gray-600">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in reportData.data.slice(0, 20)" :key="idx" class="border-b border-gray-100">
              <td class="py-2 px-3 text-gray-500">{{ idx + 1 }}</td>
              <td class="py-2 px-3 font-medium">{{ item.topic }}</td>
              <td class="py-2 px-3 text-gray-600">{{ item.service }}</td>
              <td class="py-2 px-3 text-center font-bold">{{ item.count }}</td>
            </tr>
          </tbody>
        </table>
        <p v-if="reportData.data.length > 20" class="text-center text-gray-400 text-xs mt-2">
          Menampilkan 20 dari {{ reportData.data.length }} data. Download Excel untuk data lengkap.
        </p>
      </div>

      <!-- Ratings Preview -->
      <div v-if="type === 'ratings'" class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-2 px-3 font-medium text-gray-600">Tanggal</th>
              <th class="text-left py-2 px-3 font-medium text-gray-600">Visitor</th>
              <th class="text-left py-2 px-3 font-medium text-gray-600">Layanan</th>
              <th class="text-left py-2 px-3 font-medium text-gray-600">Petugas</th>
              <th class="text-center py-2 px-3 font-medium text-gray-600">Rating</th>
              <th class="text-left py-2 px-3 font-medium text-gray-600">Topik</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in reportData.data.slice(0, 20)" :key="idx" class="border-b border-gray-100">
              <td class="py-2 px-3 text-gray-500 text-xs">{{ item.tanggal }}</td>
              <td class="py-2 px-3">{{ item.visitor }}</td>
              <td class="py-2 px-3 text-gray-600">{{ item.layanan }}</td>
              <td class="py-2 px-3 text-gray-600">{{ item.petugas }}</td>
              <td class="py-2 px-3 text-center font-bold text-yellow-600">{{ item.rating }} ⭐</td>
              <td class="py-2 px-3 text-gray-600 max-w-xs truncate">{{ item.topik }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../composables/useApi'

const reportData = ref(null)
const type = ref('')
const period = ref('')
const dateFrom = ref('')
const dateTo = ref('')

async function loadReport(reportType, reportPeriod) {
  type.value = reportType
  period.value = reportPeriod
  const res = await api.get(`/monitoring/export?type=${reportType}&period=${reportPeriod}`)
  reportData.value = res.data
}

async function loadReportCustom(reportType) {
  if (!dateFrom.value || !dateTo.value) {
    alert('Pilih tanggal mulai dan akhir')
    return
  }
  type.value = reportType
  period.value = 'custom'
  const res = await api.get(`/monitoring/export?type=${reportType}&period=custom&date_from=${dateFrom.value}&date_to=${dateTo.value}`)
  reportData.value = res.data
}

function downloadExcel(reportType) {
  if (!reportData.value || !reportData.value.data.length) {
    alert('Tidak ada data untuk diexport')
    return
  }

  const data = reportData.value.data
  let csvContent = ''
  let filename = ''

  if (reportType === 'topics') {
    csvContent = 'No,Topik,Layanan,Jumlah\n'
    data.forEach((item, idx) => {
      csvContent += `${idx + 1},"${item.topic}","${item.service}",${item.count}\n`
    })
    filename = `laporan-topik-${period.value}-${new Date().toISOString().slice(0, 10)}.csv`
  } else {
    csvContent = 'Tanggal,Visitor,Layanan,Petugas,Rating,Topik\n'
    data.forEach(item => {
      csvContent += `"${item.tanggal}","${item.visitor}","${item.layanan}","${item.petugas}",${item.rating},"${item.topik}"\n`
    })
    filename = `laporan-rating-${period.value}-${new Date().toISOString().slice(0, 10)}.csv`
  }

  // Download as CSV (can be opened in Excel)
  const BOM = '\uFEFF'
  const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = filename
  link.click()
  URL.revokeObjectURL(link.href)
}
</script>
