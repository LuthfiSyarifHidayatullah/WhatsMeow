import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../composables/useApi'

export const useChatStore = defineStore('chat', () => {
  const sessions = ref([])
  const currentSession = ref(null)
  const messages = ref([])
  const loading = ref(false)

  async function fetchSessions(filters = {}) {
    loading.value = true
    try {
      const params = new URLSearchParams(filters).toString()
      const response = await api.get(`/chats?${params}`)
      sessions.value = response.data.data || response.data
    } finally {
      loading.value = false
    }
  }

  async function fetchSession(sessionId) {
    const response = await api.get(`/chats/${sessionId}`)
    currentSession.value = response.data
    messages.value = response.data.messages || []
    return response.data
  }

  async function sendMessage(sessionId, content) {
    const response = await api.post(`/chats/${sessionId}/messages`, { content })
    messages.value.push(response.data.message)
    return response.data.message
  }

  async function acceptChat(sessionId) {
    const response = await api.post(`/chats/${sessionId}/accept`)
    await fetchSessions()
    return response.data
  }

  async function resolveChat(sessionId) {
    const response = await api.post(`/chats/${sessionId}/resolve`)
    await fetchSessions()
    return response.data
  }

  async function transferChat(sessionId, targetOfficerId, targetServiceId) {
    const response = await api.post(`/chats/${sessionId}/transfer`, {
      target_officer_id: targetOfficerId,
      target_service_id: targetServiceId,
    })
    await fetchSessions()
    return response.data
  }

  function addIncomingMessage(message) {
    if (currentSession.value?.session_id === message.session_id) {
      messages.value.push({
        content: message.message,
        sender_type: message.sender_type,
        created_at: message.timestamp,
      })
    }
  }

  return {
    sessions,
    currentSession,
    messages,
    loading,
    fetchSessions,
    fetchSession,
    sendMessage,
    acceptChat,
    resolveChat,
    transferChat,
    addIncomingMessage,
  }
})
