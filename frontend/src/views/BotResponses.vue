<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Respons Bot</h1>
        <p class="text-gray-500 text-sm">Kelola template respons otomatis chatbot</p>
      </div>
      <button @click="showForm = true" class="btn-primary">+ Tambah Respons</button>
    </div>

    <!-- Responses Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-600">Keyword Trigger</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Respons</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Layanan</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Tipe</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Prioritas</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Status</th>
              <th class="text-right py-3 px-4 font-medium text-gray-600">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="resp in responses" :key="resp.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-4 font-mono text-primary-600">{{ resp.trigger_keyword }}</td>
              <td class="py-3 px-4 max-w-xs truncate text-gray-600">{{ resp.response_text }}</td>
              <td class="py-3 px-4 text-gray-600">{{ resp.service?.name || 'Umum' }}</td>
              <td class="py-3 px-4 text-center">
                <span class="badge bg-gray-100 text-gray-700">{{ resp.match_type }}</span>
              </td>
              <td class="py-3 px-4 text-center">{{ resp.priority }}</td>
              <td class="py-3 px-4 text-center">
                <span v-if="resp.is_active" class="badge badge-active">Aktif</span>
                <span v-else class="badge badge-resolved">Non-aktif</span>
              </td>
              <td class="py-3 px-4 text-right space-x-2">
                <button @click="editResponse(resp)" class="text-primary-600 hover:text-primary-800 text-xs">Edit</button>
                <button @click="deleteResponse(resp)" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4">{{ editingResp ? 'Edit' : 'Tambah' }} Respons Bot</h3>
        <form @submit.prevent="saveResponse" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keyword Trigger</label>
            <input v-model="form.trigger_keyword" class="input-field" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teks Respons</label>
            <textarea v-model="form.response_text" class="input-field" rows="5" required></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Match</label>
              <select v-model="form.match_type" class="input-field">
                <option value="contains">Contains</option>
                <option value="exact">Exact</option>
                <option value="regex">Regex</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
              <input v-model.number="form.priority" type="number" class="input-field" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Layanan (opsional)</label>
            <select v-model="form.service_id" class="input-field">
              <option :value="null">-- Umum --</option>
              <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div class="flex items-center">
            <input v-model="form.is_active" type="checkbox" id="is_active" class="mr-2" />
            <label for="is_active" class="text-sm text-gray-700">Aktif</label>
          </div>
          <div class="flex space-x-3 pt-3">
            <button type="submit" class="btn-primary flex-1">Simpan</button>
            <button type="button" @click="closeForm" class="btn-secondary flex-1">Batal</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '../composables/useApi'

const responses = ref([])
const services = ref([])
const showForm = ref(false)
const editingResp = ref(null)
const form = reactive({
  trigger_keyword: '',
  response_text: '',
  service_id: null,
  match_type: 'contains',
  priority: 0,
  is_active: true,
})

async function fetchData() {
  const [respRes, serviceRes] = await Promise.all([
    api.get('/bot-responses'),
    api.get('/services'),
  ])
  responses.value = respRes.data
  services.value = serviceRes.data
}

function editResponse(resp) {
  editingResp.value = resp
  Object.assign(form, {
    trigger_keyword: resp.trigger_keyword,
    response_text: resp.response_text,
    service_id: resp.service_id,
    match_type: resp.match_type,
    priority: resp.priority,
    is_active: resp.is_active,
  })
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingResp.value = null
  Object.assign(form, { trigger_keyword: '', response_text: '', service_id: null, match_type: 'contains', priority: 0, is_active: true })
}

async function saveResponse() {
  try {
    if (editingResp.value) {
      await api.put(`/bot-responses/${editingResp.value.id}`, form)
    } else {
      await api.post('/bot-responses', form)
    }
    closeForm()
    await fetchData()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan')
  }
}

async function deleteResponse(resp) {
  if (!confirm('Hapus respons ini?')) return
  await api.delete(`/bot-responses/${resp.id}`)
  await fetchData()
}

onMounted(fetchData)
</script>
