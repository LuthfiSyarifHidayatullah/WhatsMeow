<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Feedback Info</h1>
      <p class="text-gray-500 text-sm">Kebermanfaatan informasi menurut tanggapan warga (Ya / Tidak)</p>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
      <div class="stat-card">
        <p class="text-sm text-gray-500">Total Tanggapan</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ summary.total }}</p>
      </div>
      <div class="stat-card">
        <p class="text-sm text-gray-500">Membantu (Ya)</p>
        <p class="text-3xl font-bold text-green-600 mt-1">{{ summary.helpful }}</p>
      </div>
      <div class="stat-card">
        <p class="text-sm text-gray-500">Tidak Membantu</p>
        <p class="text-3xl font-bold text-red-600 mt-1">{{ summary.not_helpful }}</p>
      </div>
      <div class="stat-card">
        <p class="text-sm text-gray-500">% Bermanfaat</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ summary.helpful_percentage }}%</p>
      </div>
    </div>

    <!-- Per-info table -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Rincian per Informasi</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-600">Layanan</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Informasi</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Ya</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Tidak</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Total</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">% Bermanfaat</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in items" :key="i" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-4 text-gray-600">{{ row.service_name }}</td>
              <td class="py-3 px-4 font-medium text-gray-900">{{ row.info_label }}</td>
              <td class="py-3 px-4 text-center">
                <span class="badge badge-active">{{ row.helpful }}</span>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="badge bg-red-100 text-red-700">{{ row.not_helpful }}</span>
              </td>
              <td class="py-3 px-4 text-center">{{ row.total }}</td>
              <td class="py-3 px-4 text-center">
                <span
                  class="font-semibold"
                  :class="row.helpful_percentage >= 50 ? 'text-green-600' : 'text-red-600'"
                >
                  {{ row.helpful_percentage }}%
                </span>
              </td>
            </tr>
            <tr v-if="items.length === 0">
              <td colspan="6" class="py-8 text-center text-gray-400 italic">
                Belum ada tanggapan feedback info.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../composables/useApi'

const summary = ref({ total: 0, helpful: 0, not_helpful: 0, helpful_percentage: 0 })
const items = ref([])

async function fetchData() {
  try {
    const res = await api.get('/info-feedback')
    summary.value = res.data.summary
    items.value = res.data.items
  } catch (e) {
    console.error('Failed to fetch info feedback:', e)
  }
}

onMounted(fetchData)
</script>
