<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Petugas</h1>
        <p class="text-gray-500 text-sm">Kelola petugas MPP dan penugasan layanan</p>
      </div>
      <button @click="showForm = true" class="btn-primary">+ Tambah Petugas</button>
    </div>

    <!-- Officers Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-600">Nama</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Email</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Role</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Layanan</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Max Chat</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Status</th>
              <th class="text-right py-3 px-4 font-medium text-gray-600">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-4 font-medium">{{ user.name }}</td>
              <td class="py-3 px-4 text-gray-600">{{ user.email }}</td>
              <td class="py-3 px-4">
                <span class="badge" :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800' : user.role === 'supervisor' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'">
                  {{ user.role }}
                </span>
              </td>
              <td class="py-3 px-4 text-gray-600">{{ user.service?.name || '-' }}</td>
              <td class="py-3 px-4 text-center">{{ user.max_concurrent_chats }}</td>
              <td class="py-3 px-4 text-center">
                <span v-if="user.is_online" class="badge badge-active">Online</span>
                <span v-else class="badge badge-resolved">Offline</span>
              </td>
              <td class="py-3 px-4 text-right space-x-2">
                <button @click="editUser(user)" class="text-primary-600 hover:text-primary-800 text-xs">Edit</button>
                <button @click="deleteUser(user)" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ editingUser ? 'Edit' : 'Tambah' }} Petugas</h3>
        <form @submit.prevent="saveUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input v-model="form.name" class="input-field" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" class="input-field" required />
          </div>
          <div v-if="!editingUser">
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input v-model="form.password" type="password" class="input-field" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select v-model="form.role" class="input-field">
              <option value="officer">Petugas</option>
              <option value="supervisor">Supervisor</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Layanan</label>
            <select v-model="form.service_id" class="input-field">
              <option :value="null">-- Tanpa Layanan --</option>
              <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Max Concurrent Chats</label>
            <input v-model.number="form.max_concurrent_chats" type="number" min="1" max="20" class="input-field" />
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

const users = ref([])
const services = ref([])
const showForm = ref(false)
const editingUser = ref(null)
const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'officer',
  service_id: null,
  max_concurrent_chats: 5,
})

async function fetchUsers() {
  const [userRes, serviceRes] = await Promise.all([
    api.get('/users'),
    api.get('/services'),
  ])
  users.value = userRes.data
  services.value = serviceRes.data
}

function editUser(user) {
  editingUser.value = user
  Object.assign(form, {
    name: user.name,
    email: user.email,
    password: '',
    role: user.role,
    service_id: user.service_id,
    max_concurrent_chats: user.max_concurrent_chats,
  })
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingUser.value = null
  Object.assign(form, { name: '', email: '', password: '', role: 'officer', service_id: null, max_concurrent_chats: 5 })
}

async function saveUser() {
  try {
    if (editingUser.value) {
      await api.put(`/users/${editingUser.value.id}`, form)
    } else {
      await api.post('/users', form)
    }
    closeForm()
    await fetchUsers()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan data')
  }
}

async function deleteUser(user) {
  if (!confirm(`Hapus petugas ${user.name}?`)) return
  await api.delete(`/users/${user.id}`)
  await fetchUsers()
}

onMounted(fetchUsers)
</script>
