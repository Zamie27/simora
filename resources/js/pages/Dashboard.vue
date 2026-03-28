<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);
const roleName = computed(() => user.value?.role?.name || 'Unknown');
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6 bg-slate-50 dark:bg-background">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white dark:bg-card p-6 rounded-xl shadow-sm border border-border">
                <div>
                    <h2 class="text-2xl font-bold text-primary">Selamat datang, {{ user?.name }}!</h2>
                    <p class="text-muted-foreground mt-1">Anda login sebagai <span class="font-semibold text-accent">{{ roleName }}</span></p>
                </div>
            </div>

            <!-- Role-Specific Views -->
            
            <!-- View Manager (Manajemen) -->
            <div v-if="roleName === 'Manajemen'" class="grid gap-6">
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-card p-6 rounded-xl shadow-sm border border-border border-l-4 border-l-primary">
                        <h3 class="font-semibold text-muted-foreground">Total Atlet</h3>
                        <p class="text-3xl font-bold mt-2">124</p>
                    </div>
                    <div class="bg-white dark:bg-card p-6 rounded-xl shadow-sm border border-border border-l-4 border-l-accent">
                        <h3 class="font-semibold text-muted-foreground">Total Pelatih</h3>
                        <p class="text-3xl font-bold mt-2">18</p>
                    </div>
                    <div class="bg-white dark:bg-card p-6 rounded-xl shadow-sm border border-border border-l-4 border-l-emerald-500">
                        <h3 class="font-semibold text-muted-foreground">Sesi Latihan Bulan Ini</h3>
                        <p class="text-3xl font-bold mt-2">45</p>
                    </div>
                </div>
                <div class="min-h-[300px] bg-white dark:bg-card rounded-xl shadow-sm border border-border p-6 flex items-center justify-center">
                    <p class="text-muted-foreground">Grafik Ringkasan Klub akan ditampilkan di sini</p>
                </div>
            </div>

            <!-- View Coach (Pelatih) -->
            <div v-else-if="roleName === 'Pelatih'" class="grid gap-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-card p-6 rounded-xl shadow-sm border border-border border-l-4 border-l-primary">
                        <h3 class="font-semibold text-muted-foreground">Atlet Asuhan</h3>
                        <p class="text-3xl font-bold mt-2">12</p>
                    </div>
                    <div class="bg-white dark:bg-card p-6 rounded-xl shadow-sm border border-border border-l-4 border-l-accent">
                        <h3 class="font-semibold text-muted-foreground">Jadwal Latihan Hari Ini</h3>
                        <p class="text-3xl font-bold mt-2">2 <span class="text-sm font-normal">sesi</span></p>
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="min-h-[300px] bg-white dark:bg-card rounded-xl shadow-sm border border-border p-6 flex items-center justify-center">
                        <p class="text-muted-foreground">Performa Atlet Asuhan</p>
                    </div>
                    <div class="min-h-[300px] bg-white dark:bg-card rounded-xl shadow-sm border border-border p-6 flex flex-col items-center justify-center text-center">
                        <p class="text-muted-foreground mb-4">Belum ada feedback yang diberikan hari ini</p>
                        <button class="bg-primary text-primary-foreground px-4 py-2 rounded-md font-medium hover:opacity-90 transition-opacity">
                            Berikan Rekomendasi
                        </button>
                    </div>
                </div>
            </div>

            <!-- View Athlete (Atlet) -->
            <div v-else-if="roleName === 'Atlet'" class="grid gap-6">
                <div class="grid md:grid-cols-4 gap-4">
                    <div class="bg-primary text-primary-foreground p-5 rounded-xl shadow-sm">
                        <h3 class="font-medium opacity-80 text-sm">Jarak Bulan Ini</h3>
                        <p class="text-2xl font-bold mt-1">345 <span class="text-lg font-normal opacity-80">km</span></p>
                    </div>
                    <div class="bg-white dark:bg-card p-5 rounded-xl shadow-sm border border-border">
                        <h3 class="font-semibold text-muted-foreground text-sm">Avg Speed</h3>
                        <p class="text-2xl font-bold mt-1 text-primary">28.4 <span class="text-lg font-normal text-muted-foreground">km/h</span></p>
                    </div>
                    <div class="bg-white dark:bg-card p-5 rounded-xl shadow-sm border border-border">
                        <h3 class="font-semibold text-muted-foreground text-sm">Kalori Terbakar</h3>
                        <p class="text-2xl font-bold mt-1 text-accent">8,450 <span class="text-lg font-normal text-muted-foreground">kcal</span></p>
                    </div>
                    <div class="bg-white dark:bg-card p-5 rounded-xl shadow-sm border border-border">
                        <h3 class="font-semibold text-muted-foreground text-sm">Status Fisik</h3>
                        <p class="text-2xl font-bold mt-1 text-emerald-500">Fit</p>
                    </div>
                </div>
                <div class="min-h-[400px] bg-white dark:bg-card rounded-xl shadow-sm border border-border p-6 flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-lg">Grafik Performa Latihan</h3>
                        <button class="bg-accent text-accent-foreground px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity whitespace-nowrap">
                            + Input Latihan
                        </button>
                    </div>
                    <div class="flex-1 flex items-center justify-center border-2 border-dashed border-border rounded-lg bg-slate-50 dark:bg-background">
                        <p class="text-muted-foreground">Grafik (ApexCharts) akan ditampilkan di sini</p>
                    </div>
                </div>
            </div>

            <!-- Fallback View -->
            <div v-else class="min-h-[400px] bg-white dark:bg-card rounded-xl shadow-sm border border-border p-6 flex items-center justify-center">
                <p class="text-muted-foreground">Role tidak dikenali. Silakan hubungi administrator.</p>
            </div>

        </div>
    </AppLayout>
</template>
