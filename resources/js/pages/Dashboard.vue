<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, SharedData } from '@/types';
import { dashboard } from '@/routes';

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
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-slate-50 p-6 dark:bg-background"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col items-start justify-between rounded-xl border border-border bg-white p-6 shadow-sm md:flex-row md:items-center dark:bg-card"
            >
                <div>
                    <h2 class="text-2xl font-bold text-primary">
                        Selamat datang, {{ user?.name }}!
                    </h2>
                    <p class="mt-1 text-muted-foreground">
                        Anda login sebagai
                        <span class="font-semibold text-accent">{{
                            roleName
                        }}</span>
                    </p>
                </div>
            </div>

            <!-- Role-Specific Views -->

            <!-- View Manager (Manajemen) -->
            <div v-if="roleName === 'Manajemen'" class="grid gap-6">
                <div class="grid gap-6 md:grid-cols-3">
                    <div
                        class="rounded-xl border border-l-4 border-border border-l-primary bg-white p-6 shadow-sm dark:bg-card"
                    >
                        <h3 class="font-semibold text-muted-foreground">
                            Total Atlet
                        </h3>
                        <p class="mt-2 text-3xl font-bold">124</p>
                    </div>
                    <div
                        class="rounded-xl border border-l-4 border-border border-l-accent bg-white p-6 shadow-sm dark:bg-card"
                    >
                        <h3 class="font-semibold text-muted-foreground">
                            Total Pelatih
                        </h3>
                        <p class="mt-2 text-3xl font-bold">18</p>
                    </div>
                    <div
                        class="rounded-xl border border-l-4 border-border border-l-emerald-500 bg-white p-6 shadow-sm dark:bg-card"
                    >
                        <h3 class="font-semibold text-muted-foreground">
                            Sesi Latihan Bulan Ini
                        </h3>
                        <p class="mt-2 text-3xl font-bold">45</p>
                    </div>
                </div>
                <div
                    class="flex min-h-[300px] items-center justify-center rounded-xl border border-border bg-white p-6 shadow-sm dark:bg-card"
                >
                    <p class="text-muted-foreground">
                        Grafik Ringkasan Klub akan ditampilkan di sini
                    </p>
                </div>
            </div>

            <!-- View Coach (Pelatih) -->
            <div v-else-if="roleName === 'Pelatih'" class="grid gap-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div
                        class="rounded-xl border border-l-4 border-border border-l-primary bg-white p-6 shadow-sm dark:bg-card"
                    >
                        <h3 class="font-semibold text-muted-foreground">
                            Atlet Asuhan
                        </h3>
                        <p class="mt-2 text-3xl font-bold">12</p>
                    </div>
                    <div
                        class="rounded-xl border border-l-4 border-border border-l-accent bg-white p-6 shadow-sm dark:bg-card"
                    >
                        <h3 class="font-semibold text-muted-foreground">
                            Jadwal Latihan Hari Ini
                        </h3>
                        <p class="mt-2 text-3xl font-bold">
                            2 <span class="text-sm font-normal">sesi</span>
                        </p>
                    </div>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div
                        class="flex min-h-[300px] items-center justify-center rounded-xl border border-border bg-white p-6 shadow-sm dark:bg-card"
                    >
                        <p class="text-muted-foreground">
                            Performa Atlet Asuhan
                        </p>
                    </div>
                    <div
                        class="flex min-h-[300px] flex-col items-center justify-center rounded-xl border border-border bg-white p-6 text-center shadow-sm dark:bg-card"
                    >
                        <p class="mb-4 text-muted-foreground">
                            Belum ada feedback yang diberikan hari ini
                        </p>
                        <button
                            class="rounded-md bg-primary px-4 py-2 font-medium text-primary-foreground transition-opacity hover:opacity-90"
                        >
                            Berikan Rekomendasi
                        </button>
                    </div>
                </div>
            </div>

            <!-- View Athlete (Atlet) -->
            <div v-else-if="roleName === 'Atlet'" class="grid gap-6">
                <div class="grid gap-4 md:grid-cols-4">
                    <div
                        class="rounded-xl bg-primary p-5 text-primary-foreground shadow-sm"
                    >
                        <h3 class="text-sm font-medium opacity-80">
                            Jarak Bulan Ini
                        </h3>
                        <p class="mt-1 text-2xl font-bold">
                            345
                            <span class="text-lg font-normal opacity-80"
                                >km</span
                            >
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-border bg-white p-5 shadow-sm dark:bg-card"
                    >
                        <h3 class="text-sm font-semibold text-muted-foreground">
                            Avg Speed
                        </h3>
                        <p class="mt-1 text-2xl font-bold text-primary">
                            28.4
                            <span
                                class="text-lg font-normal text-muted-foreground"
                                >km/h</span
                            >
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-border bg-white p-5 shadow-sm dark:bg-card"
                    >
                        <h3 class="text-sm font-semibold text-muted-foreground">
                            Kalori Terbakar
                        </h3>
                        <p class="mt-1 text-2xl font-bold text-accent">
                            8,450
                            <span
                                class="text-lg font-normal text-muted-foreground"
                                >kcal</span
                            >
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-border bg-white p-5 shadow-sm dark:bg-card"
                    >
                        <h3 class="text-sm font-semibold text-muted-foreground">
                            Status Fisik
                        </h3>
                        <p class="mt-1 text-2xl font-bold text-emerald-500">
                            Fit
                        </p>
                    </div>
                </div>
                <div
                    class="flex min-h-[400px] flex-col rounded-xl border border-border bg-white p-6 shadow-sm dark:bg-card"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-bold">
                            Grafik Performa Latihan
                        </h3>
                        <button
                            class="rounded-md bg-accent px-4 py-2 text-sm font-medium whitespace-nowrap text-accent-foreground transition-opacity hover:opacity-90"
                        >
                            + Input Latihan
                        </button>
                    </div>
                    <div
                        class="flex flex-1 items-center justify-center rounded-lg border-2 border-dashed border-border bg-slate-50 dark:bg-background"
                    >
                        <p class="text-muted-foreground">
                            Grafik (ApexCharts) akan ditampilkan di sini
                        </p>
                    </div>
                </div>
            </div>

            <!-- Fallback View -->
            <div
                v-else
                class="flex min-h-[400px] items-center justify-center rounded-xl border border-border bg-white p-6 shadow-sm dark:bg-card"
            >
                <p class="text-muted-foreground">
                    Role tidak dikenali. Silakan hubungi administrator.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
