<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Rating & Performa</h1>
      <p class="text-gray-500 text-sm">Detail rating per petugas dan per layanan</p>
    </div>

    <!-- Rating Per Layanan -->
    <div class="card mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Rating Per Layanan</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-600">Layanan</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Total Rating</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Rata-rata</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">⭐1</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">⭐2</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">⭐3</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">⭐4</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">⭐5</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in data.per_service" :key="item.service_id" class="border-b border-gray-100">
              <td class="py-3 px-4 font-medium">{{ item.service_name }}</td>
              <td class="py-3 px-4 text-center">{{ item.total_rated }}</td>
              <td class="py-3 px-4 text-center font-bold text-yellow-600">{{ item.avg_rating }} ⭐</td>
              <td class="py-3 px-4 text-center text-red-500">{{ item.rating_1 }}</td>
              <td class="py-3 px-4 text-center text-orange-500">{{ item.rating_2 }}</td>
              <td class="py-3 px-4 text-center text-yellow-500">{{ item.rating_3 }}</td>
              <td class="py-3 px-4 text-center text-lime-600">{{ item.rating_4 }}</td>
              <td class="py-3 px-4 text-center text-green-600">{{ item.rating_5 }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Rating Per Petugas -->
    <div class="card mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Rating Per Petugas</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-600">Petugas</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Layanan</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Total Rating</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Rata-rata</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">⭐1</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">⭐2</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">⭐3</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">⭐4</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">⭐5</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in data.per_officer" :key="item.officer_id" class="border-b border-gray-100">
              <td class="py-3 px-4 font-medium">{{ item.officer_name }}</td>
              <td class="py-3 px-4 text-gray-600">{{ item.service }}</td>
              <td class="py-3 px-4 text-center">{{ item.total_rated }}</td>
              <td class="py-3 px-4 text-center font-bold text-yellow-600">{{ item.avg_rating }} ⭐</td>
              <td class="py-3 px-4 text-center text-red-500">{{ item.rating_1 }}</td>
              <td class="py-3 px-4 text-center text-orange-500">{{ item.rating_2 }}</td>
              <td class="py-3 px-4 text-center text-yellow-500">{{ item.rating_3 }}</td>
              <td class="py-3 px-4 text-center text-lime-600">{{ item.rating_4 }}</td>
              <td class="py-3 px-4 text-center text-green-600">{{ item.rating_5 }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Ratings -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Rating Terbaru</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-medium text-gray-600">Tanggal</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">No. WA</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Layanan</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Petugas</th>
              <th class="text-center py-3 px-4 font-medium text-gray-600">Rating</th>
              <th class="text-left py-3 px-4 font-medium text-gray-600">Topik</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in data.recent" :key="item.session_id" class="border-b border-gray-100">
              <td class="py-3 px-4 text-xs text-gray-500">{{ item.resolved_at }}</td>
              <td class="py-3 px-4">{{ item.visitor_phone }}</td>
              <td class="py-3 px-4 text-gray-600">{{ item.service }}</td>
              <td class="py-3 px-4 text-gray-600">{{ item.officer }}</td>
              <td class="py-3 px-4 text-center font-bold text-yellow-600">{{ item.rating }} ⭐</td>
              <td class="py-3 px-4 text-gray-600 max-w-xs truncate">{{ item.topic }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../composables/useApi'

const data = ref({ per_officer: [], per_service: [], recent: [] })

async function fetchRatings() {
  const res = await api.get('/monitoring/ratings')
  data.value = res.data
}

onMounted(fetchRatings)
</script>
