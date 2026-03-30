<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import { 
    Users, 
    Calendar, 
    Activity, 
    Clock, 
    TrendingUp, 
    ArrowUpRight,
    Zap,
    MapPin,
    Trophy
} from 'lucide-vue-next';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

import AppLayout from '@/layouts/AppLayout.vue';
import coach from '@/routes/coach';

interface Props {
    stats: {
        total_athletes: number;
        upcoming_sessions_count: number;
        weekly_distance: number;
        weekly_duration: number;
    };
    upcomingSessions: any[];
    recentLogs: any[];
    performanceTrend: any[];
    categoryDistribution: Record<string, number>;
}

const props = defineProps<Props>();

// Chart Options: Squad Performance Trend
const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'area',
        toolbar: { show: false },
        zoom: { enabled: false },
        background: 'transparent',
    },
    colors: ['#FF6120'], // Safety Orange
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100]
        }
    },
    grid: {
        borderColor: 'rgba(255,255,255,0.05)',
        strokeDashArray: 4,
    },
    xaxis: {
        categories: props.performanceTrend.map(log => {
            const d = new Date(log.date);

            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        }),

        labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 } }
    },
    yaxis: {
        labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 } }
    },
    tooltip: { theme: 'dark' },
}));

const chartSeries = [
    {
        name: 'Total Jarak (km)',
        data: props.performanceTrend.map(log => parseFloat(log.distance_km))
    }
];

// Pie Chart for Category Distribution
const pieOptions = computed<ApexOptions>(() => ({
    chart: { type: 'donut', background: 'transparent' },
    colors: ['#FF6120', '#102844', '#0F1414', '#94a3b8'],
    labels: Object.keys(props.categoryDistribution),
    stroke: { show: false },
    legend: {
        position: 'bottom',
        labels: { colors: '#94a3b8' }
    },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    name: { color: '#94a3b8' },
                    value: { color: '#ffffff', fontSize: '20px', fontWeight: 900 },
                    total: { show: true, label: 'Atlit', color: '#94a3b8' }
                }
            }
        }
    },
    tooltip: { theme: 'dark' }
}));

const pieSeries = Object.values(props.categoryDistribution);

const breadcrumbs = [{ title: 'Dashboard Pelatih', href: '#' }];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

const formatTime = (minutes: number) => {
    const h = Math.floor(minutes / 60);

    const m = minutes % 60;
    return h > 0 ? `${h}j ${m}m` : `${m}m`;
};
</script>

<template>
    <Head title="Coach Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen space-y-6 p-4 pb-24 md:p-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground uppercase md:text-3xl">
                        Dashboard <span class="text-accent">Monitoring</span>
                    </h1>
                    <p class="text-sm font-bold text-muted-foreground opacity-70">
                        Overview performa tim dan aktivitas atlet hari ini.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="coach.trainingSessions.index().url" 
                        class="flex items-center gap-2 rounded-lg bg-surface px-4 py-2 text-sm font-bold text-foreground transition-all hover:bg-surface/80 border border-white/5">
                        <Calendar class="h-4 w-4 text-accent" />
                        Jadwal Sesi
                    </Link>
                    <Link :href="coach.athletes.index().url" 
                        class="flex items-center gap-2 rounded-lg bg-accent px-4 py-2 text-sm font-bold text-white transition-all hover:scale-105 active:scale-95 shadow-lg shadow-accent/20">
                        <Users class="h-4 w-4" />
                        Daftar Atlet
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="group relative overflow-hidden rounded-2xl bg-surface p-4 border border-white/5 md:p-6">
                    <div class="relative z-10 space-y-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Total Atlet</p>
                        <div class="flex items-end gap-2">
                            <h2 class="text-3xl font-black text-foreground">{{ stats.total_athletes }}</h2>
                        </div>
                    </div>
                    <Users class="absolute -right-4 -top-4 h-24 w-24 text-white/5 transition-transform group-hover:scale-110 group-hover:rotate-12" />
                </div>

                <div class="group relative overflow-hidden rounded-2xl bg-surface p-4 border border-white/5 md:p-6">
                    <div class="relative z-10 space-y-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Sesi Hari Ini</p>
                        <h2 class="text-3xl font-black text-foreground">{{ stats.upcoming_sessions_count }}</h2>
                    </div>
                    <Calendar class="absolute -right-4 -top-4 h-24 w-24 text-white/5 transition-transform group-hover:scale-110 group-hover:rotate-12" />
                </div>

                <div class="group relative overflow-hidden rounded-2xl bg-surface p-4 border border-white/5 md:p-6">
                    <div class="relative z-10 space-y-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Total Jarak (Week)</p>
                        <div class="flex items-end gap-2">
                            <h2 class="text-3xl font-black text-foreground">{{ stats.weekly_distance }}</h2>
                            <span class="mb-1 text-xs font-bold text-muted-foreground">KM</span>
                        </div>
                    </div>
                    <Activity class="absolute -right-4 -top-4 h-24 w-24 text-white/5 transition-transform group-hover:scale-110 group-hover:rotate-12" />
                </div>

                <div class="group relative overflow-hidden rounded-2xl bg-surface p-4 border border-white/5 md:p-6">
                    <div class="relative z-10 space-y-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Waktu Latihan (Week)</p>
                        <h2 class="text-3xl font-black text-foreground">{{ formatTime(stats.weekly_duration) }}</h2>
                    </div>
                    <Clock class="absolute -right-4 -top-4 h-24 w-24 text-white/5 transition-transform group-hover:scale-110 group-hover:rotate-12" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Chart -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-3xl bg-surface p-6 border border-white/5">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-black uppercase tracking-tighter text-foreground">Tren Performa Tim</h3>
                                <p class="text-xs font-medium text-muted-foreground">Akumulasi jarak tempuh seluruh atlet dalam 7 hari terakhir.</p>
                            </div>
                            <TrendingUp class="h-6 w-6 text-accent" />
                        </div>
                        <div class="h-[300px]">
                            <VueApexCharts width="100%" height="300" :options="chartOptions" :series="chartSeries" />
                        </div>
                    </div>

                    <!-- Recent Activity Feed -->
                    <div class="rounded-3xl bg-surface p-6 border border-white/5">
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-lg font-black uppercase tracking-tighter text-foreground">Aktivitas Terbaru</h3>
                            <Link :href="coach.athletes.index().url" class="text-xs font-black uppercase text-accent hover:underline">
                                Lihat Semua
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="log in recentLogs" :key="log.id" 
                                class="flex items-center gap-4 rounded-2xl bg-white/5 p-4 transition-all hover:bg-white/10 group">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-accent/10 border border-accent/20">
                                    <Trophy v-if="log.completion_status === 'completed'" class="h-6 w-6 text-accent" />
                                    <Zap v-else class="h-6 w-6 text-yellow-500" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-black text-foreground group-hover:text-accent transition-colors">
                                            {{ log.athlete?.name }}
                                        </p>
                                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                                            {{ formatDate(log.date) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground line-clamp-1">
                                        {{ log.title || log.exercise_type?.name }} • {{ log.distance_km }} KM • {{ log.duration_minutes }} Min
                                    </p>
                                </div>
                                <ArrowUpRight class="h-4 w-4 text-white/20 group-hover:text-accent transition-all transform group-hover:translate-x-1 group-hover:-translate-y-1" />
                            </div>
                            
                            <div v-if="recentLogs.length === 0" class="flex flex-col items-center justify-center py-12 opacity-50">
                                <Activity class="h-12 w-12 text-muted-foreground mb-4" />
                                <p class="text-xs font-bold uppercase tracking-widest">Belum ada aktivitas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Widgets -->
                <div class="space-y-6">
                    <!-- Upcoming Training -->
                    <div class="rounded-3xl bg-surface p-6 border border-white/5">
                        <h3 class="mb-6 text-lg font-black uppercase tracking-tighter text-foreground">Jadwal Mendatang</h3>
                        <div class="space-y-4">
                            <div v-for="session in upcomingSessions" :key="session.id" class="rounded-2xl border border-white/5 bg-white/5 p-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="rounded-md bg-accent px-2 py-0.5 text-[10px] font-black uppercase text-white">
                                        {{ session.exercise_type?.name || 'Sesi' }}
                                    </span>
                                    <span class="text-[10px] font-bold text-muted-foreground uppercase">{{ session.scheduled_time }} WIB</span>
                                </div>
                                <h4 class="text-sm font-black text-foreground line-clamp-1">{{ session.title }}</h4>
                                <div class="mt-2 flex items-center gap-4 text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
                                    <span class="flex items-center gap-1"><MapPin class="h-3 w-3" /> {{ session.location || 'N/A' }}</span>
                                    <span>{{ formatDate(session.scheduled_date) }}</span>
                                </div>
                            </div>
                            
                            <div v-if="upcomingSessions.length === 0" class="rounded-2xl border border-dashed border-white/10 p-8 text-center">
                                <p class="text-xs font-bold text-muted-foreground uppercase">Tidak ada jadwal</p>
                            </div>

                            <Link :href="coach.trainingSessions.index().url" 
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-white/5 bg-white/5 py-3 text-xs font-black uppercase text-foreground transition-all hover:bg-white/10 active:scale-95">
                                Atur Jadwal Baru
                                <Plus class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- Category Dist -->
                    <div class="rounded-3xl bg-surface p-6 border border-white/5">
                        <h3 class="mb-6 text-lg font-black uppercase tracking-tighter text-foreground">Distribusi Atlet</h3>
                        <div class="h-[250px]">
                            <VueApexCharts width="100%" height="250" :options="pieOptions" :series="pieSeries" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
