<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="text-gray-500 text-sm">Ringkasan layanan MPP hari ini</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Sesi Hari Ini</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_sessions_today }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Chat Aktif</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ stats.active_sessions }}</p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Dalam Antrian</p>
            <p class="text-3xl font-bold text-yellow-600 mt-1">{{ stats.waiting_sessions }}</p>
          </div>
          <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Selesai Hari Ini</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.resolved_today }}</p>
          </div>
          <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Second Row Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
      <div class="stat-card">
        <p class="text-sm text-gray-500">Rata-rata Waktu Respons</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.avg_response_time }} <span class="text-sm font-normal text-gray-500">menit</span></p>
      </div>
      <div class="stat-card">
        <p class="text-sm text-gray-500">Rating Kepuasan</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ (stats.avg_satisfaction || 0).toFixed(1) }} <span class="text-sm font-normal text-gray-500">/ 5.0</span></p>
      </div>
      <div class="stat-card">
        <p class="text-sm text-gray-500">Petugas Online</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.online_officers }} <span class="text-sm font-normal text-gray-500">/ {{ stats.total_officers }}</span></p>
      </div>
    </div>

    <!-- Queue Alert -->
    <div v-if="queue.length > 0" class="card mb-6 border-yellow-200 bg-yellow-50">
      <div class="flex items-center mb-3">
        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <h3 class="font-semibold text-yellow-800">Antrian Menunggu ({{ queue.length }})</h3>
      </div>
      <div class="space-y-2">
        <div v-for="item in queue" :key="item.session_id" class="flex items-center justify-between bg-white p-3 rounded-lg">
          <div>
            <p class="text-sm font-medium">{{ item.visitor_phone }}</p>
            <p class="text-xs text-gray-500">{{ item.service || 'Umum' }} - {{ item.waiting_since }}</p>
          </div>
          <router-link :to="`/live-chat/${item.session_id}`" class="btn-primary text-xs py-1 px-3">
            Tangani
          </router-link>
        </div>
      </div>
    </div>

    <!-- Service Statistics -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Per Layanan</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-600">Layanan</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Total Hari Ini</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Aktif</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Antrian</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Selesai</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Petugas Online</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="service in serviceStats" :key="service.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-4 font-medium">{{ service.name }}</td>
              <td class="py-3 px-4 text-center">{{ service.total_today }}</td>
              <td class="py-3 px-4 text-center">
                <span class="badge badge-active">{{ service.active_count }}</span>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="badge badge-waiting">{{ service.waiting_count }}</span>
              </td>
              <td class="py-3 px-4 text-center">{{ service.resolved_today }}</td>
              <td class="py-3 px-4 text-center">
                {{ service.officers?.filter(o => o.is_online).length || 0 }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '../composables/useApi'

const stats = ref({
  total_sessions_today: 0,
  active_sessions: 0,
  waiting_sessions: 0,
  bot_sessions: 0,
  resolved_today: 0,
  avg_response_time: 0,
  avg_satisfaction: 0,
  online_officers: 0,
  total_officers: 0,
})
const serviceStats = ref([])
const queue = ref([])
let refreshInterval = null

async function fetchDashboard() {
  try {
    const [dashRes, serviceRes, queueRes] = await Promise.all([
      api.get('/monitoring/dashboard'),
      api.get('/monitoring/services'),
      api.get('/monitoring/queue'),
    ])
    stats.value = dashRes.data
    serviceStats.value = serviceRes.data
    queue.value = queueRes.data
  } catch (e) {
    console.error('Failed to fetch dashboard:', e)
  }
}

onMounted(() => {
  fetchDashboard()
  // Auto-refresh every 15 seconds
  refreshInterval = setInterval(fetchDashboard, 15000)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>
