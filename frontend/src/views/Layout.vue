<template>
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-primary-900 text-white flex flex-col">
      <!-- Logo -->
      <div class="p-5 border-b border-primary-800">
        <h1 class="text-lg font-bold">MPP Chatbot</h1>
        <p class="text-primary-300 text-xs">Kab. Bengkayang</p>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
          :class="$route.path === item.path
            ? 'bg-primary-700 text-white'
            : 'text-primary-200 hover:bg-primary-800 hover:text-white'"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3" />
          {{ item.label }}
          <span
            v-if="item.badge"
            class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"
          >
            {{ item.badge }}
          </span>
        </router-link>
      </nav>

      <!-- User Info -->
      <div class="p-4 border-t border-primary-800">
        <div class="flex items-center">
          <div class="w-9 h-9 bg-primary-600 rounded-full flex items-center justify-center text-sm font-medium">
            {{ userInitials }}
          </div>
          <div class="ml-3 flex-1 min-w-0">
            <p class="text-sm font-medium truncate">{{ authStore.user?.name }}</p>
            <p class="text-xs text-primary-300 truncate">{{ authStore.user?.role }}</p>
          </div>
          <button @click="handleLogout" class="text-primary-300 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const userInitials = computed(() => {
  const name = authStore.user?.name || ''
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
})

const menuItems = computed(() => {
  const items = [
    { path: '/', label: 'Dashboard', icon: 'DashboardIcon' },
    { path: '/live-chat', label: 'Live Chat', icon: 'ChatIcon' },
    { path: '/monitoring', label: 'Monitoring', icon: 'MonitorIcon' },
  ]

  if (authStore.isAdmin || authStore.isSupervisor) {
    items.push(
      { path: '/services', label: 'Layanan', icon: 'ServiceIcon' },
      { path: '/officers', label: 'Petugas', icon: 'UserIcon' },
      { path: '/bot-responses', label: 'Respons Bot', icon: 'BotIcon' },
      { path: '/activity-logs', label: 'Log Aktivitas', icon: 'LogIcon' },
    )
  }

  return items
})

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>
