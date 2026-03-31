<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import {
    Users,
    Calendar,
    Activity,
    TrendingUp,
    Zap,
    Trophy,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

import AppLayout from '@/layouts/AppLayout.vue';
import management from '@/routes/management';

interface Props {
    stats: {
        total_athletes: number;
        total_coaches: number;
        verified_ratio_percent: number;
        total_sessions: number;
        total_logs: number;
    };
    recentLogs: any[];
    performanceTrend: any[];
}

const props = defineProps<Props>();

// Chart Options: Global Performance Trend
const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'area',
        toolbar: { show: false },
        zoom: { enabled: false },
        background: 'transparent',
    },
    colors: ['#0F1414'], // Primary Dark
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100],
        },
    },
    grid: {
        borderColor: 'rgba(255,255,255,0.05)',
        strokeDashArray: 4,
    },
    xaxis: {
        categories: props.performanceTrend.map((log) => {
            const d = new Date(log.date);

            return d.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
            });
        }),
        labels: {
            style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 },
        },
    },
    yaxis: {
        labels: {
            style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 },
        },
    },
    tooltip: { theme: 'dark' },
}));

const chartSeries = [
    {
        name: 'Total Jarak Platform (km)',
        data: props.performanceTrend.map((log) => parseFloat(log.distance_km)),
    },
];

const breadcrumbs = [{ title: 'Dashboard Manajemen', href: '#' }];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen space-y-6 p-4 pb-24 md:p-8">
            <!-- Header Section -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-black tracking-tight text-foreground uppercase md:text-3xl"
                    >
                        System <span class="text-accent">Overview</span>
                    </h1>
                    <p
                        class="text-sm font-bold text-muted-foreground opacity-70"
                    >
                        Monitoring global aktivitas dan performa platform.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="management.users.index().url"
                        class="flex items-center gap-2 rounded-lg bg-accent px-4 py-2 text-sm font-bold text-white shadow-lg shadow-accent/20 transition-all hover:scale-105 active:scale-95"
                    >
                        <ShieldCheck class="h-4 w-4" />
                        Kelola Pengguna
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div
                    class="group bg-surface relative overflow-hidden rounded-2xl border border-white/5 p-4 md:p-6"
                >
                    <div class="relative z-10 space-y-1">
                        <p
                            class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                        >
                            Total Atlet
                        </p>
                        <div class="flex items-end gap-2">
                            <h2 class="text-3xl font-black text-foreground">
                                {{ stats.total_athletes }}
                            </h2>
                        </div>
                    </div>
                    <Users
                        class="absolute -top-4 -right-4 h-24 w-24 text-white/5 transition-transform group-hover:scale-110 group-hover:rotate-12"
                    />
                </div>

                <div
                    class="group bg-surface relative overflow-hidden rounded-2xl border border-white/5 p-4 md:p-6"
                >
                    <div class="relative z-10 space-y-1">
                        <p
                            class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                        >
                            Total Pelatih
                        </p>
                        <h2 class="text-3xl font-black text-foreground">
                            {{ stats.total_coaches }}
                        </h2>
                    </div>
                    <ShieldCheck
                        class="absolute -top-4 -right-4 h-24 w-24 text-white/5 transition-transform group-hover:scale-110 group-hover:rotate-12"
                    />
                </div>

                <div
                    class="group bg-surface relative overflow-hidden rounded-2xl border border-white/5 p-4 md:p-6"
                >
                    <div class="relative z-10 space-y-1">
                        <p
                            class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                        >
                            Sesi Latihan (Global)
                        </p>
                        <div class="flex items-end gap-2">
                            <h2 class="text-3xl font-black text-foreground">
                                {{ stats.total_sessions }}
                            </h2>
                        </div>
                    </div>
                    <Calendar
                        class="absolute -top-4 -right-4 h-24 w-24 text-white/5 transition-transform group-hover:scale-110 group-hover:rotate-12"
                    />
                </div>

                <div
                    class="group bg-surface relative overflow-hidden rounded-2xl border border-white/5 p-4 md:p-6"
                >
                    <div class="relative z-10 space-y-1">
                        <p
                            class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                        >
                            Log Latihan (Global)
                        </p>
                        <h2 class="text-3xl font-black text-foreground">
                            {{ stats.total_logs }}
                        </h2>
                    </div>
                    <Activity
                        class="absolute -top-4 -right-4 h-24 w-24 text-white/5 transition-transform group-hover:scale-110 group-hover:rotate-12"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Main Chart -->
                <div class="space-y-6">
                    <div
                        class="bg-surface rounded-3xl border border-white/5 p-6"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h3
                                    class="text-lg font-black tracking-tighter text-foreground uppercase"
                                >
                                    Aktivitas Platform
                                </h3>
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Volume latihan seluruh atlet dalam 7 hari
                                    terakhir.
                                </p>
                            </div>
                            <TrendingUp class="h-6 w-6 text-accent" />
                        </div>
                        <div class="h-[300px]">
                            <VueApexCharts
                                width="100%"
                                height="300"
                                :options="chartOptions"
                                :series="chartSeries"
                            />
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div
                    class="bg-surface h-fit rounded-3xl border border-white/5 p-6"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="text-lg font-black tracking-tighter text-foreground uppercase"
                        >
                            Log Global Terbaru
                        </h3>
                    </div>
                    <div class="space-y-4">
                        <div
                            v-for="log in recentLogs"
                            :key="log.id"
                            class="group flex items-center gap-4 rounded-2xl bg-white/5 p-4 transition-all hover:bg-white/10"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl border border-accent/20 bg-accent/10"
                            >
                                <Trophy
                                    v-if="log.completion_status === 'completed'"
                                    class="h-6 w-6 text-accent"
                                />
                                <Zap v-else class="h-6 w-6 text-yellow-500" />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p
                                        class="text-sm font-black text-foreground transition-colors group-hover:text-accent"
                                    >
                                        {{ log.athlete?.name }}
                                    </p>
                                    <span
                                        class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase"
                                    >
                                        {{ formatDate(log.date) }}
                                    </span>
                                </div>
                                <p
                                    class="line-clamp-1 text-xs text-muted-foreground"
                                >
                                    {{ log.title || log.exercise_type?.name }} •
                                    {{ log.distance_km }} KM •
                                    {{ log.duration_minutes }} Min
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="recentLogs.length === 0"
                            class="flex flex-col items-center justify-center py-12 opacity-50"
                        >
                            <Activity
                                class="mb-4 h-12 w-12 text-muted-foreground"
                            />
                            <p
                                class="text-xs font-bold tracking-widest uppercase"
                            >
                                Belum ada aktivitas
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
