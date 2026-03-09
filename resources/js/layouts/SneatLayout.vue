<template>
  <div>
    <v-navigation-drawer v-model="drawer" app color="white" elevation="1">
      <div class="pa-4 text-center">
        <h2 class="text-primary font-weight-bold">SIMORA</h2>
      </div>

      <v-list density="compact" nav>
        <v-list-item prepend-icon="mdi-view-dashboard" title="Dashboard" to="/" />
        <v-list-item prepend-icon="mdi-bike" title="Manajemen Latihan" />
        <v-list-item prepend-icon="mdi-chart-line" title="Analitik Performa" />
        <v-list-item prepend-icon="mdi-account-group" title="Pelatih & Atlet" />
      </v-list>
    </v-navigation-drawer>

    <v-app-bar app color="white" elevation="1" title="Sistem Informasi Monitoring Atlet">
      <template v-slot:prepend>
        <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      </template>

      <v-spacer></v-spacer>

      <v-btn icon>
        <v-icon>mdi-bell-outline</v-icon>
      </v-btn>

      <v-menu offset-y>
        <template v-slot:activator="{ props }">
          <v-avatar color="primary" class="ml-4 mr-2" size="36" v-bind="props" style="cursor: pointer;">
            <span class="text-white">A</span>
          </v-avatar>
        </template>
        <v-list>
          <v-list-item title="Profil" prepend-icon="mdi-account" />
          <v-list-item title="Keluar" prepend-icon="mdi-logout" class="text-danger" />
        </v-list>
      </v-menu>
    </v-app-bar>

    <v-main>
      <v-container fluid class="pa-6">
        <router-view />
      </v-container>
    </v-main>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const drawer = ref(true);
</script>

<style scoped>
.v-application {
  background-color: #f5f5f9;
}
</style>
