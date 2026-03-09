<template>
  <v-row>
    <v-col cols="12" md="4" v-for="(stat, i) in statistics" :key="i">
      <v-card class="mb-4">
        <v-card-text>
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-subtitle-1 text-grey">{{ stat.title }}</p>
              <h3 class="text-h4 font-weight-bold">{{ stat.value }}</h3>
            </div>
            <v-avatar :color="stat.color + '-lighten-4'" size="48">
              <v-icon :color="stat.color">{{ stat.icon }}</v-icon>
            </v-avatar>
          </div>
        </v-card-text>
      </v-card>
    </v-col>

    <v-col cols="12">
      <v-card>
        <v-card-title>Performa Latihan Mingguan</v-card-title>
        <v-card-text>
          <apexchart type="area" height="350" :options="chartOptions" :series="series" />
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>

<script setup lang="ts">
import { ref } from 'vue';

// Dummy Data untuk Dashboard
const statistics = ref([
  { title: 'Total Jarak', value: '1,245 km', icon: 'mdi-map-marker-distance', color: 'primary' },
  { title: 'Rata-rata Kecepatan', value: '32 km/h', icon: 'mdi-speedometer', color: 'success' },
  { title: 'Kalori Terbakar', value: '12,400 kcal', icon: 'mdi-fire', color: 'warning' },
]);

const series = ref([{
  name: 'Jarak (km)',
  data: [31, 40, 28, 51, 42, 109, 100]
}]);

const chartOptions = ref({
  chart: {
    height: 350,
    type: 'area',
    toolbar: { show: false }
  },
  colors: ['#696cff'],
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth' },
  xaxis: {
    categories: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
  },
});
</script>
