<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Riwayat Sesi</h1>
      <p class="text-gray-500 text-sm">Riwayat percakapan yang sudah selesai</p>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="flex flex-wrap items-end gap-4">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
          <input v-model="filters.date_from" type="date" class="input-field text-sm" />
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
          <input v-model="filters.date_to" type="date" class="input-field text-sm" />
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Layanan</label>
          <select v-model="filters.service_id" class="input-field text-sm w-48">
            <option value="">Semua Layanan</option>
            <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <button @click="fetchHistory" class="btn-primary text-sm">Filter</button>
        <button @click="resetFilter" class="btn-secondary text-sm">Reset</button>
      </div>
    </div>

    <!-- History Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-600">Tanggal</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">No. WhatsApp</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Layanan</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Petugas</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Topik</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Rating</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Durasi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="session in sessions" :key="session.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-4 text-xs text-gray-500">{{ formatDate(session.created_at) }}</td>
              <td class="py-3 px-4 font-medium">{{ session.visitor_phone }}</td>
              <td class="py-3 px-4 text-gray-600">{{ session.service?.name || '-' }}</td>
              <td class="py-3 px-4 text-gray-600">{{ session.officer?.name || '-' }}</td>
              <td class="py-3 px-4 text-gray-600 max-w-xs truncate">{{ session.topic || '-' }}</td>
              <td class="py-3 px-4 text-center">
                <span v-if="session.satisfaction_rating" class="text-yellow-600 font-medium">
                  {{ session.satisfaction_rating }} ⭐
                </span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="py-3 px-4 text-center text-xs text-gray-500">
                {{ getDuration(session) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
        <button
          v-for="page in pagination.last_page"
          :key="page"
          @click="fetchHistory(page)"
          :class="pagination.current_page === page ? 'btn-primary' : 'btn-secondary'"
          class="text-xs py-1 px-3"
        >
          {{ page }}
        </button>
      </div>

      <p v-if="sessions.length === 0" class="text-center text-gray-400 py-8">
        Tidak ada data riwayat untuk filter ini
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '../composables/useApi'

const sessions = ref([])
const services = ref([])
const pagination = ref({ current_page: 1, last_page: 1 })
const filters = reactive({
  date_from: '',
  date_to: '',
  service_id: '',
})

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
  })
}

function getDuration(session) {
  if (!session.created_at || !session.resolved_at) return '-'
  const start = new Date(session.created_at)
  const end = new Date(session.resolved_at)
  const mins = Math.round((end - start) / 60000)
  if (mins < 60) return `${mins} menit`
  return `${Math.floor(mins / 60)}j ${mins % 60}m`
}

function resetFilter() {
  filters.date_from = ''
  filters.date_to = ''
  filters.service_id = ''
  fetchHistory()
}

async function fetchHistory(page = 1) {
  const params = new URLSearchParams({ page })
  if (filters.date_from) params.append('date_from', filters.date_from)
  if (filters.date_to) params.append('date_to', filters.date_to)
  if (filters.service_id) params.append('service_id', filters.service_id)

  const res = await api.get(`/monitoring/history?${params}`)
  sessions.value = res.data.data
  pagination.value = { current_page: res.data.current_page, last_page: res.data.last_page }
}

async function fetchServices() {
  const res = await api.get('/services')
  services.value = res.data
}

onMounted(() => {
  fetchHistory()
  fetchServices()
})
</script>
