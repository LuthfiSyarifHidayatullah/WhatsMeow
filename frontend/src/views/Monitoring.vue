<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Monitoring</h1>
      <p class="text-gray-500 text-sm">Pantau kinerja petugas dan layanan secara real-time</p>
    </div>

    <!-- Officer Performance Table -->
    <div class="card mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Kinerja Petugas</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-600">Nama Petugas</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Layanan</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Status</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Chat Aktif</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Ditangani</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Selesai</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Rating</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="officer in officers" :key="officer.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <div :class="officer.is_online ? 'bg-green-500' : 'bg-gray-300'" class="w-2.5 h-2.5 rounded-full mr-2"></div>
                  <span class="font-medium">{{ officer.name }}</span>
                </div>
              </td>
              <td class="py-3 px-4 text-gray-600">{{ officer.service }}</td>
              <td class="py-3 px-4 text-center">
                <span v-if="officer.is_online && officer.is_available" class="badge badge-active">Tersedia</span>
                <span v-else-if="officer.is_online && !officer.is_available" class="badge badge-waiting">Sibuk</span>
                <span v-else class="badge badge-resolved">Offline</span>
              </td>
              <td class="py-3 px-4 text-center font-medium">{{ officer.active_chats }}</td>
              <td class="py-3 px-4 text-center">{{ officer.handled_today }}</td>
              <td class="py-3 px-4 text-center">{{ officer.resolved_today }}</td>
              <td class="py-3 px-4 text-center">
                <span class="text-yellow-600 font-medium">{{ officer.avg_satisfaction }} ⭐</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Active Sessions -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Sesi Aktif Real-time</h3>
      <div class="space-y-3">
        <div
          v-for="session in activeSessions"
          :key="session.session_id"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium">{{ session.visitor_phone }}</p>
              <p class="text-xs text-gray-500">{{ session.service?.name }} - {{ session.officer?.name }}</p>
            </div>
          </div>
          <span class="badge badge-active">Aktif</span>
        </div>

        <p v-if="activeSessions.length === 0" class="text-center text-gray-400 py-6">
          Tidak ada sesi aktif saat ini
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '../composables/useApi'

const officers = ref([])
const activeSessions = ref([])
let refreshInterval = null

async function fetchData() {
  try {
    const [officerRes, sessionRes] = await Promise.all([
      api.get('/monitoring/officers'),
      api.get('/chats?status=active'),
    ])
    officers.value = officerRes.data
    activeSessions.value = sessionRes.data.data || sessionRes.data
  } catch (e) {
    console.error('Failed to fetch monitoring data:', e)
  }
}

onMounted(() => {
  fetchData()
  refreshInterval = setInterval(fetchData, 10000)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>
