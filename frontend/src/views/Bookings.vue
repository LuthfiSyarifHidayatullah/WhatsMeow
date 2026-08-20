<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Kelola Jadwal</h1>
        <p class="text-gray-500 text-sm">Jadwal penggunaan ruangan dan peminjaman alat</p>
      </div>
      <button @click="showForm = true" class="btn-primary text-sm">+ Tambah Jadwal</button>
    </div>

    <div class="flex gap-3 mb-4">
      <select v-model="filterLocation" class="input-field text-sm w-44">
        <option value="">Semua Ruangan</option>
        <option value="Pusat Media">Pusat Media</option>
        <option value="Podcast">Podcast</option>
      </select>
      <input v-model="filterDateFrom" type="date" class="input-field text-sm" />
      <input v-model="filterDateTo" type="date" class="input-field text-sm" />
      <button @click="fetchBookings" class="btn-secondary text-sm">Filter</button>
    </div>

    <div class="card">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-3 font-medium text-gray-600">Tanggal</th>
              <th class="text-left py-3 px-3 font-medium text-gray-600">Jam</th>
              <th class="text-left py-3 px-3 font-medium text-gray-600">Kegiatan</th>
              <th class="text-left py-3 px-3 font-medium text-gray-600">OPD</th>
              <th class="text-left py-3 px-3 font-medium text-gray-600">Ruangan</th>
              <th class="text-left py-3 px-3 font-medium text-gray-600">PJ</th>
              <th class="text-center py-3 px-3 font-medium text-gray-600">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in bookings" :key="b.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-3">{{ formatDate(b.date) }}</td>
              <td class="py-3 px-3">{{ b.start_time?.substring(0,5) }} - {{ b.end_time?.substring(0,5) }}</td>
              <td class="py-3 px-3 font-medium">{{ b.title }}</td>
              <td class="py-3 px-3 text-gray-600">{{ b.booked_by }}</td>
              <td class="py-3 px-3"><span class="badge" :class="b.location === 'Pusat Media' ? 'badge-active' : 'badge-waiting'">{{ b.location }}</span></td>
              <td class="py-3 px-3 text-gray-600 text-xs">{{ b.pic_name || '-' }}</td>
              <td class="py-3 px-3 text-center"><button @click="deleteBooking(b.id)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button></td>
            </tr>
            <tr v-if="bookings.length === 0"><td colspan="7" class="py-8 text-center text-gray-400">Belum ada jadwal</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4">Tambah Jadwal Baru</h3>
        <form @submit.prevent="createBooking" class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Layanan</label>
            <select v-model="form.service_id" class="input-field text-sm" required>
              <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Kegiatan</label><input v-model="form.title" class="input-field text-sm" required /></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">OPD / Instansi</label><input v-model="form.booked_by" class="input-field text-sm" required /></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label><input v-model="form.date" type="date" class="input-field text-sm" required /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label><select v-model="form.location" class="input-field text-sm" required><option value="Pusat Media">Pusat Media</option><option value="Podcast">Podcast</option></select></div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
              <select v-model="form.start_time" class="input-field text-sm" required>
                <option value="">Pilih jam</option>
                <option v-for="t in timeOptions" :key="'s'+t" :value="t">{{ t }} WIB</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
              <select v-model="form.end_time" class="input-field text-sm" required>
                <option value="">Pilih jam</option>
                <option v-for="t in timeOptions" :key="'e'+t" :value="t">{{ t }} WIB</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab</label><input v-model="form.pic_name" class="input-field text-sm" /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">No. HP PJ</label><input v-model="form.pic_phone" class="input-field text-sm" /></div>
          </div>
          <div v-if="formError" class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded text-sm">{{ formError }}</div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showForm = false" class="btn-secondary text-sm">Batal</button>
            <button type="submit" class="btn-primary text-sm" :disabled="formLoading">{{ formLoading ? 'Menyimpan...' : 'Simpan' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '../composables/useApi'

const bookings = ref([])
const services = ref([])
const showForm = ref(false)
const formLoading = ref(false)
const formError = ref('')
const filterLocation = ref('')
const filterDateFrom = ref('')
const filterDateTo = ref('')
const form = reactive({ service_id: '', title: '', booked_by: '', date: '', start_time: '', end_time: '', location: 'Pusat Media', pic_name: '', pic_phone: '' })

// Generate time options dari 07:00 sampai 17:00 (interval 30 menit)
const timeOptions = []
for (let h = 7; h <= 17; h++) {
  timeOptions.push(`${String(h).padStart(2, '0')}:00`)
  if (h < 17) timeOptions.push(`${String(h).padStart(2, '0')}:30`)
}

function formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-' }

async function fetchBookings() {
  const params = {}
  if (filterLocation.value) params.location = filterLocation.value
  if (filterDateFrom.value) params.date_from = filterDateFrom.value
  if (filterDateTo.value) params.date_to = filterDateTo.value
  const res = await api.get('/bookings', { params })
  bookings.value = res.data.data || res.data
}

async function fetchServices() { const res = await api.get('/services'); services.value = res.data.data || res.data }

async function createBooking() {
  formLoading.value = true; formError.value = ''
  try {
    await api.post('/bookings', form)
    showForm.value = false
    Object.assign(form, { title: '', booked_by: '', date: '', start_time: '', end_time: '', pic_name: '', pic_phone: '' })
    await fetchBookings()
  } catch (e) { formError.value = e.response?.data?.message || 'Gagal menyimpan' }
  finally { formLoading.value = false }
}

async function deleteBooking(id) { if (!confirm('Hapus jadwal ini?')) return; await api.delete(`/bookings/${id}`); await fetchBookings() }

onMounted(() => { fetchBookings(); fetchServices() })
</script>
