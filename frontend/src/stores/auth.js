import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../composables/useApi'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isSupervisor = computed(() => user.value?.role === 'supervisor')
  const isOfficer = computed(() => user.value?.role === 'officer')

  async function login(email, password) {
    const response = await api.post('/login', { email, password })
    token.value = response.data.token
    user.value = response.data.user
    localStorage.setItem('token', token.value)
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    return response.data
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch (e) {
      // ignore
    }
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    delete api.defaults.headers.common['Authorization']
  }

  async function checkAuth() {
    if (!token.value) return
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    try {
      const response = await api.get('/me')
      user.value = response.data.user
    } catch (e) {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    isSupervisor,
    isOfficer,
    login,
    logout,
    checkAuth,
  }
})
