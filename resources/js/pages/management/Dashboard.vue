<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import {
    Users,
    Calendar,
    Activity,
    TrendingUp,
    ShieldCheck,
    Clock,
    MapPin,
    Trophy,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
    athleteRanking: any[];
    runningSessions: any[];
    upcomingEvents: any[];
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
    colors: ['#FF6120'], // Primary Orange
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

const showDetailModal = ref(false);
const selectedEvent = ref<any>(null);

const openDetail = (event: any) => {
    selectedEvent.value = event;
    showDetailModal.value = true;
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'participated':
            return 'text-emerald-500';
        case 'planned':
            return 'text-accent';
        case 'cancelled':
            return 'text-destructive';
        default:
            return 'text-muted-foreground';
    }
};

const getTypeColor = (id: number | null) => {
    if (!id) {
        return 'bg-muted text-muted-foreground border-border';
    }

    const colors = [
        'bg-orange-500/10 text-orange-500 border-orange-500/20',
        'bg-blue-500/10 text-blue-500 border-blue-500/20',
        'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
        'bg-purple-500/10 text-purple-500 border-purple-500/20',
    ];

    return colors[id % colors.length];
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

            <!-- Athlete Leaderboard -->
            <div class="bg-surface rounded-3xl border border-white/5 p-6">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3
                            class="text-lg font-black tracking-tighter text-foreground uppercase"
                        >
                            Leaderboard
                            <span class="text-accent">Performa</span>
                        </h3>
                        <p class="text-xs font-medium text-muted-foreground">
                            Peringkat atlet berdasarkan kecepatan rata-rata (30
                            hari terakhir).
                        </p>
                    </div>
                    <Trophy class="h-6 w-6 text-accent" />
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="(athlete, index) in athleteRanking.slice(0, 6)"
                        :key="athlete.id"
                        class="group relative flex items-center gap-4 rounded-2xl bg-white/5 p-4 transition-all hover:bg-white/10"
                    >
                        <!-- Rank Number -->
                        <div
                            class="bg-surface absolute -top-2 -left-2 flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 text-sm font-black text-foreground shadow-xl group-hover:bg-accent group-hover:text-white"
                        >
                            #{{ index + 1 }}
                        </div>

                        <!-- Avatar -->
                        <div
                            class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-secondary"
                        >
                            <img
                                v-if="
                                    athlete.athlete_profile?.profile_photo_path
                                "
                                :src="`/documents/${athlete.id}/profile_photo`"
                                class="h-full w-full object-cover"
                            />
                            <img
                                v-else-if="athlete.avatar"
                                :src="athlete.avatar"
                                class="h-full w-full object-cover"
                            />
                            <span v-else class="text-xs font-black text-accent">
                                {{
                                    athlete.name
                                        .split(' ')
                                        .map((n: string) => n[0])
                                        .slice(0, 2)
                                        .join('')
                                }}
                            </span>
                        </div>

                        <div class="flex-1">
                            <h4 class="text-sm font-black text-foreground">
                                {{ athlete.name }}
                            </h4>
                            <p class="text-[10px] font-bold text-accent">
                                {{ athlete.category_name || 'Personal' }}
                            </p>
                            <div class="mt-1 flex items-center gap-3">
                                <span
                                    class="text-[10px] font-bold text-muted-foreground"
                                    >{{ athlete.performance_score }} KPH</span
                                >
                                <span
                                    class="text-[8px] text-muted-foreground/30"
                                    >•</span
                                >
                                <span
                                    class="text-[10px] font-bold text-muted-foreground"
                                    >{{
                                        Math.round(athlete.total_distance)
                                    }}
                                    KM</span
                                >
                            </div>
                        </div>

                        <div class="text-right">
                            <TrendingUp
                                class="h-4 w-4 text-emerald-500 opacity-50"
                            />
                        </div>
                    </div>
                </div>

                <div
                    v-if="athleteRanking.length === 0"
                    class="flex flex-col items-center justify-center py-10 opacity-30"
                >
                    <Trophy class="mb-2 h-10 w-10" />
                    <p class="text-xs font-black uppercase">
                        Belum ada data peringkat
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Running Sessions -->
                <div class="bg-surface rounded-3xl border border-white/5 p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="text-lg font-black tracking-tighter text-foreground uppercase"
                        >
                            Sesi <span class="text-accent">Hari Ini</span>
                        </h3>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10"
                        >
                            <Clock
                                class="h-4 w-4 animate-pulse text-emerald-500"
                            />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="session in runningSessions"
                            :key="session.id"
                            class="group relative overflow-hidden rounded-2xl bg-white/5 p-4 transition-all hover:bg-white/10"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="rounded-lg bg-accent/10 px-2 py-1 text-[10px] font-black text-accent uppercase"
                                    >
                                        {{ session.exercise_type?.name }}
                                    </div>
                                    <span
                                        class="text-xs font-bold text-muted-foreground"
                                        >{{ session.scheduled_time }} WIB</span
                                    >
                                </div>
                                <span
                                    v-if="session.repeat_weekly"
                                    class="text-[9px] font-black text-accent/50 uppercase italic"
                                    >Weekly</span
                                >
                            </div>
                            <h4 class="mt-2 text-sm font-black text-foreground">
                                {{ session.title }}
                            </h4>
                            <div class="mt-2 flex items-center justify-between">
                                <p
                                    class="text-xs font-bold text-muted-foreground opacity-60"
                                >
                                    Coach: {{ session.coach?.name }}
                                </p>
                                <div class="flex -space-x-2">
                                    <div
                                        v-for="athlete in session.athletes.slice(
                                            0,
                                            4,
                                        )"
                                        :key="athlete.id"
                                        class="border-surface flex h-6 w-6 items-center justify-center overflow-hidden rounded-full border-2 bg-secondary text-[8px] font-black"
                                    >
                                        <img
                                            v-if="
                                                athlete.athlete_profile
                                                    ?.profile_photo_path
                                            "
                                            :src="`/documents/${athlete.id}/profile_photo`"
                                            class="h-full w-full object-cover"
                                        />
                                        <img
                                            v-else-if="athlete.avatar"
                                            :src="athlete.avatar"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else>
                                            {{
                                                athlete.name
                                                    .split(' ')
                                                    .map((n: string) => n[0])
                                                    .slice(0, 2)
                                                    .join('')
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        v-if="session.athletes.length > 4"
                                        class="border-surface flex h-6 w-6 items-center justify-center rounded-full border-2 bg-muted text-[8px] font-black text-muted-foreground"
                                    >
                                        +{{ session.athletes.length - 4 }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="runningSessions.length === 0"
                            class="flex flex-col items-center justify-center py-10 opacity-30"
                        >
                            <Calendar class="mb-2 h-8 w-8" />
                            <p class="text-xs font-black uppercase">
                                Tidak ada sesi hari ini
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="bg-surface rounded-3xl border border-white/5 p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="text-lg font-black tracking-tighter text-foreground uppercase"
                        >
                            Event <span class="text-accent">Target</span>
                        </h3>
                        <Trophy class="h-5 w-5 text-accent" />
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="event in upcomingEvents"
                            :key="event.id"
                            @click="openDetail(event)"
                            class="flex cursor-pointer items-center gap-4 rounded-2xl border border-white/5 bg-white/5 p-4 transition-all hover:bg-white/10"
                        >
                            <div
                                class="flex flex-col items-center justify-center rounded-xl bg-accent px-3 py-2 text-white"
                            >
                                <span class="text-xs font-black">{{
                                    new Date(event.event_date).getDate()
                                }}</span>
                                <span class="text-[8px] font-black uppercase">{{
                                    new Date(
                                        event.event_date,
                                    ).toLocaleDateString('id-ID', {
                                        month: 'short',
                                    })
                                }}</span>
                            </div>
                            <div class="flex-1">
                                <h4
                                    class="line-clamp-1 text-sm font-black text-foreground"
                                >
                                    {{ event.title }}
                                </h4>
                                <div
                                    class="mt-1 flex items-center justify-between gap-3 text-[10px] font-bold text-muted-foreground opacity-60"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="flex items-center gap-1">
                                            <MapPin class="h-3 w-3" />
                                            {{ event.location }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ event.type?.name }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <Users class="h-3 w-3" />
                                        <span>{{
                                            event.participants?.length || 0
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="upcomingEvents.length === 0"
                            class="flex flex-col items-center justify-center py-10 opacity-30"
                        >
                            <Trophy class="mb-2 h-8 w-8" />
                            <p class="text-xs font-black uppercase">
                                Belum ada event terjadwal
                            </p>
                        </div>
                    </div>
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
                        <Link
                            v-for="log in recentLogs"
                            :key="log.id"
                            :href="`/management/athletes/${log.athlete_id}`"
                            class="group flex items-center gap-4 rounded-2xl bg-white/5 p-4 transition-all hover:bg-white/10"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-secondary"
                            >
                                <img
                                    v-if="
                                        log.athlete?.athlete_profile
                                            ?.profile_photo_path
                                    "
                                    :src="`/documents/${log.athlete.id}/profile_photo`"
                                    class="h-full w-full object-cover"
                                />
                                <img
                                    v-else-if="log.athlete?.avatar"
                                    :src="log.athlete.avatar"
                                    class="h-full w-full object-cover"
                                />
                                <span
                                    v-else
                                    class="text-xs font-black text-accent"
                                >
                                    {{
                                        log.athlete?.name
                                            .split(' ')
                                            .map((n: string) => n[0])
                                            .slice(0, 2)
                                            .join('')
                                    }}
                                </span>
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
                        </Link>

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

        <!-- Detail Modal (Admin View) -->
        <div
            v-if="showDetailModal && selectedEvent"
            class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-2xl"
        >
            <div
                class="bg-surface w-full max-w-4xl animate-in overflow-hidden rounded-[3rem] border border-white/5 shadow-2xl duration-300 fade-in zoom-in"
            >
                <div
                    class="flex items-center justify-between border-b border-white/5 bg-white/5 p-8 md:p-10"
                >
                    <div class="flex items-center gap-6">
                        <div
                            :class="getTypeColor(selectedEvent.event_type_id)"
                            class="flex h-20 w-20 items-center justify-center rounded-3xl border shadow-xl"
                        >
                            <Trophy class="h-10 w-10" />
                        </div>
                        <div>
                            <h2
                                class="text-3xl leading-none font-black tracking-tighter text-foreground uppercase italic"
                            >
                                {{ selectedEvent.title }}
                            </h2>
                            <p
                                class="mt-2 flex items-center gap-2 text-[10px] font-black tracking-widest text-accent uppercase opacity-80"
                            >
                                <Calendar class="h-3 w-3" />
                                {{ formatDate(selectedEvent.event_date) }}
                                <span class="mx-2 opacity-20">|</span>
                                <MapPin class="h-3 w-3" />
                                {{ selectedEvent.location || 'No Location' }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="showDetailModal = false"
                        class="rounded-full bg-white/5 p-3 text-muted-foreground transition-all hover:bg-white/10"
                    >
                        <X class="h-6 w-6" />
                    </button>
                </div>

                <div
                    class="grid max-h-[70vh] grid-cols-1 gap-10 overflow-y-auto p-10 md:grid-cols-3"
                >
                    <!-- Left: Details -->
                    <div class="space-y-8 md:col-span-1">
                        <div v-if="selectedEvent.description" class="space-y-3">
                            <h4
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                            >
                                Deskripsi Event
                            </h4>
                            <p
                                class="text-sm leading-relaxed font-medium italic"
                            >
                                {{ selectedEvent.description }}
                            </p>
                        </div>
                        <div class="space-y-3">
                            <h4
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                            >
                                Penanggung Jawab
                            </h4>
                            <p class="text-sm font-black text-foreground">
                                Coach: {{ selectedEvent.coach?.name }}
                            </p>
                        </div>
                    </div>

                    <!-- Right: Participant List -->
                    <div class="space-y-6 md:col-span-2">
                        <h4
                            class="flex items-center gap-2 text-xs font-black tracking-widest uppercase"
                        >
                            <Users class="h-4 w-4 text-orange-500" /> Atlet
                            Peserta ({{
                                selectedEvent.participants?.length || 0
                            }})
                        </h4>
                        <div class="grid grid-cols-1 gap-3">
                            <div
                                v-for="participation in selectedEvent.participants"
                                :key="participation.id"
                                class="flex items-center justify-between rounded-2xl border border-white/5 bg-white/5 p-6 transition-all hover:bg-white/10"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="bg-surface flex h-12 w-12 items-center justify-center rounded-xl text-xs font-black shadow-sm"
                                    >
                                        {{
                                            participation.user?.name
                                                .substring(0, 2)
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black uppercase">
                                            {{ participation.user?.name }}
                                        </p>
                                        <p
                                            class="text-[10px] font-bold text-muted-foreground uppercase opacity-60"
                                        >
                                            Kategori:
                                            <span class="text-accent">{{
                                                participation.point?.name ||
                                                'General'
                                            }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <div
                                        :class="
                                            getStatusColor(participation.status)
                                        "
                                        class="text-[10px] font-black tracking-widest uppercase"
                                    >
                                        {{ participation.status }}
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="!selectedEvent.participants?.length"
                                class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-white/5 p-10 opacity-40"
                            >
                                <Users class="mb-2 h-8 w-8" />
                                <p
                                    class="text-[10px] font-black tracking-widest uppercase italic"
                                >
                                    Belum ada atlet ditugaskan
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
