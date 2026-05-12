<template>
  <div class="flex flex-col h-full">
    <!-- Chat Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <button @click="$router.push('/live-chat')" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <div>
          <p class="font-medium text-gray-900">{{ session?.visitor_name || session?.visitor_phone }}</p>
          <p class="text-xs text-gray-500">{{ session?.service?.name || 'Layanan Umum' }}</p>
        </div>
      </div>

      <div class="flex items-center space-x-2">
        <span :class="statusBadgeClass(session?.status)" class="badge">
          {{ session?.status }}
        </span>
        <button
          v-if="session?.status === 'active'"
          @click="resolveChat"
          class="btn-secondary text-xs py-1.5 px-3"
        >
          Selesaikan
        </button>
        <button
          v-if="session?.status === 'waiting'"
          @click="acceptChat"
          class="btn-success text-xs py-1.5 px-3"
        >
          Terima Chat
        </button>
      </div>
    </div>

    <!-- Messages Area -->
    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">
      <div
        v-for="(msg, index) in messages"
        :key="index"
        :class="messageAlignment(msg.sender_type)"
      >
        <div :class="messageBubbleClass(msg.sender_type)" class="max-w-md rounded-2xl px-4 py-2.5">
          <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
          <p class="text-xs mt-1 opacity-60">
            {{ formatTime(msg.created_at) }}
            <span v-if="msg.sender_type === 'officer'" class="ml-1">- {{ msg.sender_user?.name || 'Petugas' }}</span>
            <span v-else-if="msg.sender_type === 'bot'" class="ml-1">- Bot</span>
          </p>
        </div>
      </div>

      <!-- Typing indicator -->
      <div v-if="isTyping" class="flex justify-start">
        <div class="bg-gray-200 rounded-2xl px-4 py-2.5">
          <div class="flex space-x-1">
            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Message Input -->
    <div v-if="session?.status === 'active'" class="bg-white border-t border-gray-200 px-6 py-4">
      <form @submit.prevent="sendMessage" class="flex items-center space-x-3">
        <input
          v-model="newMessage"
          type="text"
          class="input-field flex-1"
          placeholder="Ketik pesan..."
          :disabled="sending"
        />
        <button
          type="submit"
          :disabled="!newMessage.trim() || sending"
          class="btn-primary flex items-center"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
          </svg>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useChatStore } from '../stores/chat'
import { useEcho } from '../composables/useEcho'

const props = defineProps(['sessionId'])
const route = useRoute()
const router = useRouter()
const chatStore = useChatStore()
const { listenChatSession, leaveChannel } = useEcho()

const session = ref(null)
const messages = ref([])
const newMessage = ref('')
const sending = ref(false)
const isTyping = ref(false)
const messagesContainer = ref(null)

function statusBadgeClass(status) {
  const map = { active: 'badge-active', waiting: 'badge-waiting', bot: 'badge-bot', resolved: 'badge-resolved' }
  return map[status] || ''
}

function messageAlignment(type) {
  if (type === 'officer') return 'flex justify-end'
  return 'flex justify-start'
}

function messageBubbleClass(type) {
  if (type === 'officer') return 'bg-primary-600 text-white'
  if (type === 'bot') return 'bg-gray-300 text-gray-900'
  return 'bg-white text-gray-900 border border-gray-200'
}

function formatTime(date) {
  if (!date) return ''
  return new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

async function sendMessage() {
  if (!newMessage.value.trim()) return
  sending.value = true
  try {
    await chatStore.sendMessage(props.sessionId, newMessage.value)
    messages.value = chatStore.messages
    newMessage.value = ''
    scrollToBottom()
  } catch (e) {
    alert('Gagal mengirim pesan')
  } finally {
    sending.value = false
  }
}

async function acceptChat() {
  try {
    await chatStore.acceptChat(props.sessionId)
    await loadSession()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menerima chat')
  }
}

async function resolveChat() {
  if (!confirm('Selesaikan percakapan ini?')) return
  try {
    await chatStore.resolveChat(props.sessionId)
    router.push('/live-chat')
  } catch (e) {
    alert('Gagal menyelesaikan chat')
  }
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

async function loadSession() {
  const data = await chatStore.fetchSession(props.sessionId)
  session.value = data
  messages.value = chatStore.messages
  scrollToBottom()
}

onMounted(() => {
  loadSession()

  // Listen for real-time messages
  listenChatSession(props.sessionId, (event) => {
    if (event.sender_type !== 'officer') {
      chatStore.addIncomingMessage(event)
      messages.value = chatStore.messages
      scrollToBottom()
    }
  })
})

onUnmounted(() => {
  leaveChannel(`chat-session.${props.sessionId}`)
})
</script>
