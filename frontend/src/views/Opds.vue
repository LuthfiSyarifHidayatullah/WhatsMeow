<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Instansi (OPD)</h1>
        <p class="text-gray-500 text-sm">Kelola daftar Organisasi Perangkat Daerah pengampu layanan</p>
      </div>
      <button @click="showForm = true" class="btn-primary">+ Tambah Instansi</button>
    </div>

    <!-- OPD Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="opd in opds" :key="opd.id" class="card">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-900">🏛️ {{ opd.name }}</h3>
            <p class="text-xs text-gray-500 font-mono">{{ opd.code }}</p>
          </div>
          <span v-if="opd.is_active" class="badge badge-active">Aktif</span>
          <span v-else class="badge badge-resolved">Non-aktif</span>
        </div>
        <p class="text-sm text-gray-600 mb-4">{{ opd.description || 'Tidak ada deskripsi' }}</p>
        <div class="flex items-center justify-between text-sm border-t border-gray-100 pt-3">
          <span class="text-gray-500">{{ opd.services_count || 0 }} layanan</span>
          <div class="space-x-3">
            <button @click="editOpd(opd)" class="text-primary-600 hover:text-primary-800">Edit</button>
            <button @click="deleteOpd(opd)" class="text-red-600 hover:text-red-800">Hapus</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ editingOpd ? 'Edit' : 'Tambah' }} Instansi</h3>
        <form @submit.prevent="saveOpd" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Instansi</label>
            <input v-model="form.name" class="input-field" placeholder="e.g. Dinas Pendidikan" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
            <input v-model="form.code" class="input-field" placeholder="e.g. pendidikan, satpolpp" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea v-model="form.description" class="input-field" rows="3"></textarea>
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
import { ref, reactive, onMounted } from 'vue'
import api from '../composables/useApi'

const opds = ref([])
const showForm = ref(false)
const editingOpd = ref(null)
const form = reactive({
  name: '',
  code: '',
  description: '',
  is_active: true,
  sort_order: 0,
})

async function fetchOpds() {
  const res = await api.get('/opds')
  opds.value = res.data
}

function editOpd(opd) {
  editingOpd.value = opd
  Object.assign(form, {
    name: opd.name,
    code: opd.code,
    description: opd.description || '',
    is_active: opd.is_active,
    sort_order: opd.sort_order,
  })
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingOpd.value = null
  Object.assign(form, { name: '', code: '', description: '', is_active: true, sort_order: 0 })
}

async function saveOpd() {
  try {
    if (editingOpd.value) {
      await api.put(`/opds/${editingOpd.value.id}`, form)
    } else {
      await api.post('/opds', form)
    }
    closeForm()
    await fetchOpds()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan instansi')
  }
}

async function deleteOpd(opd) {
  if (!confirm(`Hapus instansi ${opd.name}? Layanan di bawahnya tidak akan terhapus namun kehilangan keterkaitan.`)) return
  await api.delete(`/opds/${opd.id}`)
  await fetchOpds()
}

onMounted(fetchOpds)
</script>
