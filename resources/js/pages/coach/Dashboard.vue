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
    Trophy,
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
        name: 'Total Jarak (km)',
        data: props.performanceTrend.map((log) => parseFloat(log.distance_km)),
    },
];

// Pie Chart for Category Distribution
const pieOptions = computed<ApexOptions>(() => ({
    chart: { type: 'donut', background: 'transparent' },
    colors: ['#FF6120', '#102844', '#0F1414', '#94a3b8'],
    labels: Object.keys(props.categoryDistribution),
    stroke: { show: false },
    legend: {
        position: 'bottom',
        labels: { colors: '#94a3b8' },
    },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    name: { color: '#94a3b8' },
                    value: {
                        color: '#ffffff',
                        fontSize: '20px',
                        fontWeight: 900,
                    },
                    total: { show: true, label: 'Atlit', color: '#94a3b8' },
                },
            },
        },
    },
    tooltip: { theme: 'dark' },
}));

const pieSeries = Object.values(props.categoryDistribution);

const breadcrumbs = [{ title: 'Dashboard Pelatih', href: '#' }];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
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
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-black tracking-tight text-foreground uppercase md:text-3xl"
                    >
                        Dashboard <span class="text-accent">Monitoring</span>
                    </h1>
                    <p
                        class="text-sm font-bold text-muted-foreground opacity-70"
                    >
                        Overview performa tim dan aktivitas atlet hari ini.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="coach.trainingSessions.index().url"
                        class="bg-surface hover:bg-surface/80 flex items-center gap-2 rounded-lg border border-white/5 px-4 py-2 text-sm font-bold text-foreground transition-all"
                    >
                        <Calendar class="h-4 w-4 text-accent" />
                        Jadwal Sesi
                    </Link>
                    <Link
                        :href="coach.athletes.index().url"
                        class="flex items-center gap-2 rounded-lg bg-accent px-4 py-2 text-sm font-bold text-white shadow-lg shadow-accent/20 transition-all hover:scale-105 active:scale-95"
                    >
                        <Users class="h-4 w-4" />
                        Daftar Atlet
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
                            Sesi Hari Ini
                        </p>
                        <h2 class="text-3xl font-black text-foreground">
                            {{ stats.upcoming_sessions_count }}
                        </h2>
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
                            Total Jarak (Week)
                        </p>
                        <div class="flex items-end gap-2">
                            <h2 class="text-3xl font-black text-foreground">
                                {{ stats.weekly_distance }}
                            </h2>
                            <span
                                class="mb-1 text-xs font-bold text-muted-foreground"
                                >KM</span
                            >
                        </div>
                    </div>
                    <Activity
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
                            Waktu Latihan (Week)
                        </p>
                        <h2 class="text-3xl font-black text-foreground">
                            {{ formatTime(stats.weekly_duration) }}
                        </h2>
                    </div>
                    <Clock
                        class="absolute -top-4 -right-4 h-24 w-24 text-white/5 transition-transform group-hover:scale-110 group-hover:rotate-12"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Chart -->
                <div class="space-y-6 lg:col-span-2">
                    <div
                        class="bg-surface rounded-3xl border border-white/5 p-6"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h3
                                    class="text-lg font-black tracking-tighter text-foreground uppercase"
                                >
                                    Tren Performa Tim
                                </h3>
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Akumulasi jarak tempuh seluruh atlet dalam 7
                                    hari terakhir.
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

                    <!-- Recent Activity Feed -->
                    <div
                        class="bg-surface rounded-3xl border border-white/5 p-6"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <h3
                                class="text-lg font-black tracking-tighter text-foreground uppercase"
                            >
                                Aktivitas Terbaru
                            </h3>
                            <Link
                                :href="coach.athletes.index().url"
                                class="text-xs font-black text-accent uppercase hover:underline"
                            >
                                Lihat Semua
                            </Link>
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
                                        v-if="
                                            log.completion_status ===
                                            'completed'
                                        "
                                        class="h-6 w-6 text-accent"
                                    />
                                    <Zap
                                        v-else
                                        class="h-6 w-6 text-yellow-500"
                                    />
                                </div>
                                <div class="flex-1">
                                    <div
                                        class="flex items-center justify-between"
                                    >
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
                                        {{
                                            log.title || log.exercise_type?.name
                                        }}
                                        • {{ log.distance_km }} KM •
                                        {{ log.duration_minutes }} Min
                                    </p>
                                </div>
                                <ArrowUpRight
                                    class="h-4 w-4 transform text-white/20 transition-all group-hover:translate-x-1 group-hover:-translate-y-1 group-hover:text-accent"
                                />
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

                <!-- Sidebar Widgets -->
                <div class="space-y-6">
                    <!-- Upcoming Training -->
                    <div
                        class="bg-surface rounded-3xl border border-white/5 p-6"
                    >
                        <h3
                            class="mb-6 text-lg font-black tracking-tighter text-foreground uppercase"
                        >
                            Jadwal Mendatang
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="session in upcomingSessions"
                                :key="session.id"
                                class="rounded-2xl border border-white/5 bg-white/5 p-4"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <span
                                        class="rounded-md bg-accent px-2 py-0.5 text-[10px] font-black text-white uppercase"
                                    >
                                        {{
                                            session.exercise_type?.name ||
                                            'Sesi'
                                        }}
                                    </span>
                                    <span
                                        class="text-[10px] font-bold text-muted-foreground uppercase"
                                        >{{ session.scheduled_time }} WIB</span
                                    >
                                </div>
                                <h4
                                    class="line-clamp-1 text-sm font-black text-foreground"
                                >
                                    {{ session.title }}
                                </h4>
                                <div
                                    class="mt-2 flex items-center gap-4 text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    <span class="flex items-center gap-1"
                                        ><MapPin class="h-3 w-3" />
                                        {{ session.location || 'N/A' }}</span
                                    >
                                    <span>{{
                                        formatDate(session.scheduled_date)
                                    }}</span>
                                </div>
                            </div>

                            <div
                                v-if="upcomingSessions.length === 0"
                                class="rounded-2xl border border-dashed border-white/10 p-8 text-center"
                            >
                                <p
                                    class="text-xs font-bold text-muted-foreground uppercase"
                                >
                                    Tidak ada jadwal
                                </p>
                            </div>

                            <Link
                                :href="coach.trainingSessions.index().url"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-white/5 bg-white/5 py-3 text-xs font-black text-foreground uppercase transition-all hover:bg-white/10 active:scale-95"
                            >
                                Atur Jadwal Baru
                                <Plus class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- Category Dist -->
                    <div
                        class="bg-surface rounded-3xl border border-white/5 p-6"
                    >
                        <h3
                            class="mb-6 text-lg font-black tracking-tighter text-foreground uppercase"
                        >
                            Distribusi Atlet
                        </h3>
                        <div class="h-[250px]">
                            <VueApexCharts
                                width="100%"
                                height="250"
                                :options="pieOptions"
                                :series="pieSeries"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
