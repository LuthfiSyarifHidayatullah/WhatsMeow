<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Layanan</h1>
        <p class="text-gray-500 text-sm">Kelola daftar layanan Kabupaten Bengkayang</p>
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
            <p v-if="service.opd" class="text-xs text-primary-600 mt-0.5">🏛️ {{ service.opd.name }}</p>
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
            <button @click="openMenuManager(service)" class="text-emerald-600 hover:text-emerald-800">Kelola Menu</button>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Instansi (OPD)</label>
            <select v-model="form.opd_id" class="input-field">
              <option :value="null">- Tanpa Instansi -</option>
              <option v-for="opd in opds" :key="opd.id" :value="opd.id">{{ opd.name }}</option>
            </select>
          </div>
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

    <!-- Menu Manager Modal -->
    <div v-if="showMenuManager" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-1">
          <h3 class="text-lg font-semibold">Kelola Menu Layanan</h3>
          <button @click="closeMenuManager" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <p class="text-sm text-gray-500 mb-4">
          Pilihan yang muncul di WhatsApp setelah warga memilih
          <span class="font-medium text-gray-700">{{ menuService?.name }}</span>.
        </p>

        <!-- Existing items -->
        <div v-if="menuItems.length" class="space-y-2 mb-5">
          <div
            v-for="item in menuItems"
            :key="item.id"
            class="flex items-start gap-3 border border-gray-200 rounded-lg p-3"
          >
            <span class="font-mono text-primary-600 font-semibold w-6 text-center shrink-0">{{ item.position }}.</span>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-medium text-gray-900">{{ item.label }}</span>
                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">{{ actionLabel(item.action) }}</span>
                <span v-if="!item.is_active" class="text-xs px-2 py-0.5 rounded bg-red-50 text-red-600">Non-aktif</span>
              </div>
              <p v-if="needsText(item.action) && item.response_text" class="text-xs text-gray-500 mt-1 line-clamp-2 whitespace-pre-line">
                {{ item.response_text }}
              </p>
              <p v-else-if="needsText(item.action)" class="text-xs text-amber-600 mt-1">
                ⚠️ Teks respons belum diisi
              </p>
            </div>
            <div class="space-x-2 shrink-0 text-xs">
              <button @click="editMenuItem(item)" class="text-primary-600 hover:text-primary-800">Edit</button>
              <button @click="deleteMenuItem(item)" class="text-red-600 hover:text-red-800">Hapus</button>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400 italic mb-5">Belum ada pilihan. Tambahkan di bawah.</p>

        <!-- Item form -->
        <div class="border-t border-gray-100 pt-4">
          <h4 class="text-sm font-semibold text-gray-800 mb-3">
            {{ editingItem ? 'Edit Pilihan' : 'Tambah Pilihan Baru' }}
          </h4>
          <form @submit.prevent="saveMenuItem" class="space-y-3">
            <div class="grid grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nomor</label>
                <input v-model.number="itemForm.position" type="number" min="1" class="input-field" placeholder="otomatis" />
              </div>
              <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Label Pilihan</label>
                <input v-model="itemForm.label" class="input-field" placeholder="mis. Informasi Pengajuan" required />
              </div>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Aksi</label>
              <select v-model="itemForm.action" class="input-field">
                <option value="info">Tampilkan Informasi (teks)</option>
                <option value="schedule">Tampilkan Jadwal (dari booking)</option>
                <option value="formulir_then_escalate">Form Pengajuan lalu Hubungi Petugas</option>
                <option value="escalate">Langsung Hubungi Petugas</option>
              </select>
              <p class="text-xs text-gray-400 mt-1">{{ actionHint(itemForm.action) }}</p>
            </div>
            <div v-if="needsText(itemForm.action)">
              <label class="block text-xs font-medium text-gray-700 mb-1">Teks Respons</label>
              <textarea v-model="itemForm.response_text" class="input-field" rows="4"
                placeholder="Teks informasi / link formulir yang dikirim ke warga"></textarea>
            </div>
            <div class="flex items-center">
              <input v-model="itemForm.is_active" type="checkbox" id="item_active" class="mr-2" />
              <label for="item_active" class="text-sm text-gray-700">Aktif</label>
            </div>
            <div class="flex space-x-3 pt-1">
              <button type="submit" class="btn-primary flex-1">{{ editingItem ? 'Simpan Perubahan' : 'Tambah' }}</button>
              <button v-if="editingItem" type="button" @click="resetItemForm" class="btn-secondary flex-1">Batal Edit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import api from '../composables/useApi'

const services = ref([])
const opds = ref([])
const showForm = ref(false)
const editingService = ref(null)
const keywordsInput = ref('')
const form = reactive({
  opd_id: null,
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

async function fetchOpds() {
  const res = await api.get('/opds')
  opds.value = res.data
}

function editService(service) {
  editingService.value = service
  Object.assign(form, {
    opd_id: service.opd_id ?? null,
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
  Object.assign(form, { opd_id: null, name: '', code: '', description: '', keywords: [], is_active: true, sort_order: 0 })
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

// ===================== Menu Manager =====================
const showMenuManager = ref(false)
const menuService = ref(null)
const menuItems = ref([])
const editingItem = ref(null)
const itemForm = reactive({
  position: null,
  label: '',
  action: 'info',
  response_text: '',
  is_active: true,
})

const ACTION_LABELS = {
  info: 'Informasi',
  schedule: 'Jadwal',
  formulir_then_escalate: 'Form + Petugas',
  escalate: 'Hubungi Petugas',
}

function actionLabel(action) {
  return ACTION_LABELS[action] || action
}

function actionHint(action) {
  const hints = {
    info: 'Mengirim teks informasi yang Anda tulis di bawah.',
    schedule: 'Menampilkan jadwal dari data booking (tidak perlu teks).',
    formulir_then_escalate: 'Mengirim teks/link formulir, lalu menghubungkan ke petugas.',
    escalate: 'Langsung menghubungkan warga ke petugas (tidak perlu teks).',
  }
  return hints[action] || ''
}

// Action yang membutuhkan teks respons (info / formulir).
function needsText(action) {
  return action === 'info' || action === 'formulir_then_escalate'
}

async function openMenuManager(service) {
  menuService.value = service
  showMenuManager.value = true
  resetItemForm()
  await fetchMenuItems()
}

function closeMenuManager() {
  showMenuManager.value = false
  menuService.value = null
  menuItems.value = []
  resetItemForm()
}

async function fetchMenuItems() {
  const res = await api.get('/service-menu-items', { params: { service_id: menuService.value.id } })
  menuItems.value = res.data
}

function resetItemForm() {
  editingItem.value = null
  Object.assign(itemForm, { position: null, label: '', action: 'info', response_text: '', is_active: true })
}

function editMenuItem(item) {
  editingItem.value = item
  Object.assign(itemForm, {
    position: item.position,
    label: item.label,
    action: item.action,
    response_text: item.response_text || '',
    is_active: item.is_active,
  })
}

async function saveMenuItem() {
  const payload = {
    service_id: menuService.value.id,
    label: itemForm.label,
    action: itemForm.action,
    response_text: needsText(itemForm.action) ? itemForm.response_text : null,
    is_active: itemForm.is_active,
  }
  // Hanya kirim position bila diisi (biarkan backend auto-assign bila kosong).
  if (itemForm.position) payload.position = itemForm.position

  try {
    if (editingItem.value) {
      await api.put(`/service-menu-items/${editingItem.value.id}`, payload)
    } else {
      await api.post('/service-menu-items', payload)
    }
    resetItemForm()
    await fetchMenuItems()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan pilihan menu')
  }
}

async function deleteMenuItem(item) {
  if (!confirm(`Hapus pilihan "${item.label}"?`)) return
  await api.delete(`/service-menu-items/${item.id}`)
  if (editingItem.value?.id === item.id) resetItemForm()
  await fetchMenuItems()
}

onMounted(() => {
  fetchServices()
  fetchOpds()
})
</script>
