<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Kirim Notifikasi</h1>
      <p class="text-gray-500 text-sm">Kirim pemberitahuan ke visitor via WhatsApp</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Form Kirim -->
      <div class="lg:col-span-2">
        <div class="card">
          <h3 class="text-lg font-semibold mb-4">Kirim Pesan</h3>

          <!-- Filter Layanan -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter Layanan</label>
            <select v-model="serviceFilter" @change="loadVisitors" class="input-field text-sm">
              <option value="">Semua Layanan</option>
              <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <!-- Daftar Visitor (pilih langsung) -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Visitor</label>
            <div class="border rounded-lg max-h-48 overflow-y-auto">
              <div v-if="visitors.length === 0" class="px-3 py-4 text-center text-gray-400 text-sm">
                Tidak ada visitor
              </div>
              <div
                v-for="v in visitors"
                :key="v.chat_jid"
                @click="selectVisitor(v)"
                class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b last:border-b-0 flex items-center justify-between"
                :class="{ 'bg-blue-50 border-blue-200': selectedVisitor?.chat_jid === v.chat_jid }"
              >
                <div>
                  <p class="text-sm font-medium">{{ v.visitor_name || v.visitor_phone }}</p>
                  <p class="text-xs text-gray-500">{{ v.visitor_phone }} - {{ v.service_name }}</p>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-gray-400">{{ v.last_contact }}</span>
                  <span v-if="selectedVisitor?.chat_jid === v.chat_jid" class="text-blue-500 text-xs font-medium">Dipilih</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Selected Visitor Info -->
          <div v-if="selectedVisitor" class="mb-4 p-3 bg-blue-50 rounded-lg">
            <p class="text-sm font-medium text-blue-900">Kirim ke: {{ selectedVisitor.visitor_name || selectedVisitor.visitor_phone }}</p>
            <p class="text-xs text-blue-600">{{ selectedVisitor.visitor_phone }} | {{ selectedVisitor.service_name }}</p>
          </div>

          <!-- Template -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Template (opsional)</label>
            <select @change="applyTemplate($event.target.value)" class="input-field text-sm">
              <option value="">-- Tulis pesan manual --</option>
              <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.label }}</option>
            </select>
          </div>

          <!-- Message -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pesan</label>
            <textarea
              v-model="message"
              class="input-field text-sm"
              rows="5"
              placeholder="Tulis pesan notifikasi..."
            ></textarea>
            <p class="text-xs text-gray-400 mt-1">{{ message.length }}/4096 karakter</p>
          </div>

          <!-- Include Rating -->
          <div class="mb-4 flex items-center">
            <input v-model="includeRating" type="checkbox" id="includeRating" class="mr-2 rounded" />
            <label for="includeRating" class="text-sm text-gray-700">Sertakan permintaan rating (case sudah selesai)</label>
          </div>

          <!-- Error -->
          <div v-if="error" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded text-sm">{{ error }}</div>

          <!-- Success -->
          <div v-if="success" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded text-sm">{{ success }}</div>

          <!-- Send Button -->
          <button
            @click="sendNotification"
            :disabled="!selectedVisitor || !message || sending"
            class="btn-primary w-full"
            :class="{ 'opacity-50 cursor-not-allowed': !selectedVisitor || !message || sending }"
          >
            {{ sending ? 'Mengirim...' : 'Kirim Notifikasi via WhatsApp' }}
          </button>
        </div>
      </div>

      <!-- Templates Quick Access -->
      <div>
        <div class="card">
          <h3 class="text-lg font-semibold mb-3">Template Cepat</h3>
          <div class="space-y-2">
            <button
              v-for="t in templates"
              :key="t.id"
              @click="message = t.message"
              class="w-full text-left p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-colors"
            >
              <p class="text-sm font-medium text-gray-900">{{ t.label }}</p>
              <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ t.message.substring(0, 60) }}...</p>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../composables/useApi'

const services = ref([])
const serviceFilter = ref('')
const visitors = ref([])
const selectedVisitor = ref(null)
const templates = ref([])
const message = ref('')
const includeRating = ref(true)
const sending = ref(false)
const error = ref('')
const success = ref('')

function selectVisitor(visitor) {
  selectedVisitor.value = visitor
}

function applyTemplate(templateId) {
  if (!templateId) return
  const t = templates.value.find(t => t.id === templateId)
  if (t) message.value = t.message
}

async function loadVisitors() {
  const params = {}
  if (serviceFilter.value) params.service_id = serviceFilter.value
  const res = await api.get('/notifications/visitors', { params })
  visitors.value = res.data
}

async function sendNotification() {
  if (!selectedVisitor.value || !message.value) return
  sending.value = true
  error.value = ''
  success.value = ''

  try {
    await api.post('/notifications/send', {
      chat_jid: selectedVisitor.value.chat_jid,
      visitor_phone: selectedVisitor.value.visitor_phone,
      message: message.value,
      include_rating: includeRating.value,
    })
    success.value = 'Notifikasi berhasil dikirim ke ' + (selectedVisitor.value.visitor_name || selectedVisitor.value.visitor_phone)
    message.value = ''
    selectedVisitor.value = null
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal mengirim notifikasi'
  } finally {
    sending.value = false
  }
}

onMounted(async () => {
  const [tplRes, svcRes] = await Promise.all([
    api.get('/notifications/templates'),
    api.get('/notifications/services'),
  ])
  templates.value = tplRes.data
  services.value = svcRes.data
  await loadVisitors()
})
</script>
