<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Live Chat</h1>
        <p class="text-gray-500 text-sm">Daftar percakapan yang sedang ditangani</p>
      </div>
      <div class="flex items-center space-x-3">
        <!-- Status Filter -->
        <select v-model="statusFilter" class="input-field text-sm w-40">
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="waiting">Menunggu</option>
          <option value="bot">Bot</option>
          <option value="resolved">Selesai</option>
        </select>
      </div>
    </div>

    <!-- Chat List -->
    <div class="space-y-3">
      <div
        v-for="session in sessions"
        :key="session.session_id"
        class="card cursor-pointer hover:shadow-md transition-shadow"
        @click="openChat(session)"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <!-- Avatar -->
            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900">{{ session.visitor_name || session.visitor_phone }}</p>
              <p class="text-sm text-gray-500">{{ session.service?.name || 'Belum ditentukan' }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ session.latest_message?.content?.substring(0, 60) }}...</p>
            </div>
          </div>

          <div class="flex items-center space-x-3">
            <!-- Status Badge -->
            <span :class="statusBadgeClass(session.status)" class="badge">
              {{ statusLabel(session.status) }}
            </span>

            <!-- Actions -->
            <div class="flex space-x-2">
              <button
                v-if="session.status === 'waiting'"
                @click.stop="acceptChat(session.session_id)"
                class="btn-success text-xs py-1 px-3"
              >
                Terima
              </button>
              <button
                v-if="session.status === 'active'"
                @click.stop="openChat(session)"
                class="btn-primary text-xs py-1 px-3"
              >
                Buka Chat
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="sessions.length === 0 && !loading" class="text-center py-12">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <p class="text-gray-500">Belum ada percakapan</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useChatStore } from '../stores/chat'

const router = useRouter()
const chatStore = useChatStore()
const statusFilter = ref('')
const loading = ref(false)
const sessions = ref([])

function statusBadgeClass(status) {
  const map = {
    active: 'badge-active',
    waiting: 'badge-waiting',
    bot: 'badge-bot',
    resolved: 'badge-resolved',
  }
  return map[status] || 'badge-resolved'
}

function statusLabel(status) {
  const map = {
    active: 'Aktif',
    waiting: 'Menunggu',
    bot: 'Bot',
    resolved: 'Selesai',
    abandoned: 'Ditinggalkan',
  }
  return map[status] || status
}

function openChat(session) {
  router.push(`/live-chat/${session.session_id}`)
}

async function acceptChat(sessionId) {
  try {
    await chatStore.acceptChat(sessionId)
    await fetchSessions()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menerima chat')
  }
}

async function fetchSessions() {
  loading.value = true
  try {
    const params = {}
    if (statusFilter.value) params.status = statusFilter.value
    await chatStore.fetchSessions(params)
    sessions.value = chatStore.sessions
  } finally {
    loading.value = false
  }
}

watch(statusFilter, fetchSessions)
onMounted(fetchSessions)
</script>
