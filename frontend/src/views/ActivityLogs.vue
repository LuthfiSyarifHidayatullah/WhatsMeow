<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Log Aktivitas</h1>
      <p class="text-gray-500 text-sm">Catatan semua aktivitas sistem</p>
    </div>

    <div class="card">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-600">Waktu</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">User</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Aksi</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Deskripsi</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">IP</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-4 text-gray-500 text-xs whitespace-nowrap">
                {{ formatDate(log.created_at) }}
              </td>
              <td class="py-3 px-4 font-medium">{{ log.user?.name || 'System' }}</td>
              <td class="py-3 px-4">
                <span class="badge" :class="actionBadge(log.action)">{{ log.action }}</span>
              </td>
              <td class="py-3 px-4 text-gray-600 max-w-xs truncate">{{ log.description }}</td>
              <td class="py-3 px-4 text-gray-500 font-mono text-xs">{{ log.ip_address }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
        <button
          v-for="page in pagination.last_page"
          :key="page"
          @click="fetchLogs(page)"
          :class="pagination.current_page === page ? 'btn-primary' : 'btn-secondary'"
          class="text-xs py-1 px-3"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../composables/useApi'

const logs = ref([])
const pagination = ref({ current_page: 1, last_page: 1 })

function formatDate(date) {
  return new Date(date).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function actionBadge(action) {
  const map = {
    login: 'bg-green-100 text-green-800',
    logout: 'bg-gray-100 text-gray-800',
    accept_chat: 'bg-blue-100 text-blue-800',
    resolve_chat: 'bg-purple-100 text-purple-800',
    transfer: 'bg-yellow-100 text-yellow-800',
  }
  return map[action] || 'bg-gray-100 text-gray-800'
}

async function fetchLogs(page = 1) {
  const res = await api.get(`/monitoring/activity-logs?page=${page}`)
  logs.value = res.data.data
  pagination.value = { current_page: res.data.current_page, last_page: res.data.last_page }
}

onMounted(() => fetchLogs())
</script>
