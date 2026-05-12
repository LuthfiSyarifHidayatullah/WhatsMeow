import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('../views/Layout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue'),
      },
      {
        path: 'live-chat',
        name: 'LiveChat',
        component: () => import('../views/LiveChat.vue'),
      },
      {
        path: 'live-chat/:sessionId',
        name: 'ChatRoom',
        component: () => import('../views/ChatRoom.vue'),
        props: true,
      },
      {
        path: 'monitoring',
        name: 'Monitoring',
        component: () => import('../views/Monitoring.vue'),
      },
      {
        path: 'services',
        name: 'Services',
        component: () => import('../views/Services.vue'),
      },
      {
        path: 'officers',
        name: 'Officers',
        component: () => import('../views/Officers.vue'),
      },
      {
        path: 'bot-responses',
        name: 'BotResponses',
        component: () => import('../views/BotResponses.vue'),
      },
      {
        path: 'activity-logs',
        name: 'ActivityLogs',
        component: () => import('../views/ActivityLogs.vue'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router
