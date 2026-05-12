<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Layanan</h1>
        <p class="text-gray-500 text-sm">Kelola daftar layanan MPP Kab. Bengkayang</p>
      </div>
      <button @click="showForm = true" class="btn-primary">+ Tambah Layanan</button>
    </div>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="service in services" :key="service.id" class="card">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-900">{{ service.name }}</h3>
            <p class="text-xs text-gray-500 font-mono">{{ service.code }}</p>
          </div>
          <span v-if="service.is_active" class="badge badge-active">Aktif</span>
          <span v-else class="badge badge-resolved">Non-aktif</span>
        </div>
        <p class="text-sm text-gray-600 mb-3">{{ service.description || 'Tidak ada deskripsi' }}</p>
        <div class="flex flex-wrap gap-1 mb-4">
          <span v-for="kw in (service.keywords || [])" :key="kw" class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded">
            {{ kw }}
          </span>
        </div>
        <div class="flex items-center justify-between text-sm border-t border-gray-100 pt-3">
          <span class="text-gray-500">{{ service.officers_count || 0 }} petugas</span>
          <div class="space-x-3">
            <button @click="editService(service)" class="text-primary-600 hover:text-primary-800">Edit</button>
            <button @click="deleteService(service)" class="text-red-600 hover:text-red-800">Hapus</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ editingService ? 'Edit' : 'Tambah' }} Layanan</h3>
        <form @submit.prevent="saveService" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Layanan</label>
            <input v-model="form.name" class="input-field" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
            <input v-model="form.code" class="input-field" placeholder="e.g. ktp, pajak" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea v-model="form.description" class="input-field" rows="3"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keywords (pisahkan koma)</label>
            <input v-model="keywordsInput" class="input-field" placeholder="ktp, identitas, e-ktp" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
            <input v-model.number="form.sort_order" type="number" class="input-field" />
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
import { ref, reactive, onMounted, computed } from 'vue'
import api from '../composables/useApi'

const services = ref([])
const showForm = ref(false)
const editingService = ref(null)
const keywordsInput = ref('')
const form = reactive({
  name: '',
  code: '',
  description: '',
  keywords: [],
  is_active: true,
  sort_order: 0,
})

async function fetchServices() {
  const res = await api.get('/services')
  services.value = res.data
}

function editService(service) {
  editingService.value = service
  Object.assign(form, {
    name: service.name,
    code: service.code,
    description: service.description || '',
    keywords: service.keywords || [],
    is_active: service.is_active,
    sort_order: service.sort_order,
  })
  keywordsInput.value = (service.keywords || []).join(', ')
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingService.value = null
  Object.assign(form, { name: '', code: '', description: '', keywords: [], is_active: true, sort_order: 0 })
  keywordsInput.value = ''
}

async function saveService() {
  form.keywords = keywordsInput.value.split(',').map(k => k.trim()).filter(Boolean)
  try {
    if (editingService.value) {
      await api.put(`/services/${editingService.value.id}`, form)
    } else {
      await api.post('/services', form)
    }
    closeForm()
    await fetchServices()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan layanan')
  }
}

async function deleteService(service) {
  if (!confirm(`Hapus layanan ${service.name}?`)) return
  await api.delete(`/services/${service.id}`)
  await fetchServices()
}

onMounted(fetchServices)
</script>
