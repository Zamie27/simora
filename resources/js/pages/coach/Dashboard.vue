<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
    MessageSquare,
    Send,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

import AppLayout from '@/layouts/AppLayout.vue';
import CustomSelect from '@/components/ui/CustomSelect.vue';
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
    categoryDistribution: any;
    athletesList: { id: number; name: string }[];
    recentMessages: any[];
    athleteRanking: any[];
}

const props = defineProps<Props>();

const messageForm = useForm({
    receiver_id: '',
    content: '',
});

const sendMessage = () => {
    messageForm.post(coach.messages.store().url, {
        preserveScroll: true,
        onSuccess: () => messageForm.reset(),
    });
};

const deleteMessage = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
        router.delete(`/coach/messages/${id}`, {
            preserveScroll: true,
        });
    }
};

const athleteOptions = computed(() =>
    props.athletesList.map((a) => ({
        value: String(a.id),
        label: a.name,
    })),
);

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

            <!-- Squad Leaderboard -->
            <div class="bg-surface rounded-3xl border border-white/5 p-6">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3
                            class="text-lg font-black tracking-tighter text-foreground uppercase"
                        >
                            Leaderboard <span class="text-accent">Squad</span>
                        </h3>
                        <p class="text-xs font-medium text-muted-foreground">
                            Peringkat atlet binaan berdasarkan kecepatan
                            rata-rata (30 hari terakhir).
                        </p>
                    </div>
                    <Trophy class="h-6 w-6 text-accent" />
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        v-for="(athlete, index) in athleteRanking"
                        :key="athlete.id"
                        class="group relative flex items-center gap-4 rounded-2xl bg-white/5 p-4 transition-all hover:bg-white/10"
                    >
                        <!-- Rank Number -->
                        <div
                            class="bg-surface absolute -top-2 -left-2 flex h-7 w-7 items-center justify-center rounded-lg border border-white/10 text-[10px] font-black text-foreground shadow-xl group-hover:bg-accent group-hover:text-white"
                        >
                            #{{ index + 1 }}
                        </div>

                        <!-- Avatar -->
                        <div
                            class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-secondary"
                        >
                            <img
                                v-if="athlete.avatar"
                                :src="athlete.avatar"
                                class="h-full w-full object-cover"
                            />
                            <span
                                v-else
                                class="text-[10px] font-black text-accent"
                            >
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
                            <h4
                                class="truncate text-xs font-black text-foreground"
                            >
                                {{ athlete.name }}
                            </h4>
                            <div class="mt-1 flex items-center gap-2">
                                <span
                                    class="text-[9px] font-bold text-accent uppercase"
                                    >{{ athlete.performance_score }} KPH</span
                                >
                                <span
                                    class="text-[8px] text-muted-foreground/30"
                                    >•</span
                                >
                                <span
                                    class="text-[9px] font-bold text-muted-foreground"
                                    >{{
                                        Math.round(athlete.total_distance)
                                    }}
                                    KM</span
                                >
                            </div>
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

                    <!-- Messages Widget -->
                    <div
                        class="bg-surface rounded-3xl border border-white/5 p-6"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <h3
                                class="text-lg font-black tracking-tighter text-foreground uppercase"
                            >
                                Kirim Pesan
                            </h3>
                            <MessageSquare class="h-5 w-5 text-accent" />
                        </div>

                        <!-- Form -->
                        <form
                            @submit.prevent="sendMessage"
                            class="mb-6 space-y-4"
                        >
                            <div>
                                <CustomSelect
                                    v-model="messageForm.receiver_id"
                                    :options="athleteOptions"
                                    placeholder="Pilih Atlet"
                                />
                                <span
                                    v-if="messageForm.errors.receiver_id"
                                    class="text-xs text-red-500"
                                    >{{ messageForm.errors.receiver_id }}</span
                                >
                            </div>
                            <div>
                                <textarea
                                    v-model="messageForm.content"
                                    rows="3"
                                    placeholder="Tulis pesan atau instruksi..."
                                    class="w-full rounded-xl border border-white/10 bg-white/5 p-3 text-sm text-foreground focus:border-accent focus:ring-accent"
                                    required
                                ></textarea>
                                <span
                                    v-if="messageForm.errors.content"
                                    class="text-xs text-red-500"
                                    >{{ messageForm.errors.content }}</span
                                >
                            </div>
                            <button
                                type="submit"
                                :disabled="messageForm.processing"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-accent p-3 text-sm font-bold text-white transition-all hover:opacity-90 active:scale-95 disabled:opacity-50"
                            >
                                Kirim
                                <Send class="h-4 w-4" />
                            </button>
                        </form>

                        <!-- Recent Messages -->
                        <h4
                            class="mb-3 text-xs font-black tracking-widest text-muted-foreground uppercase"
                        >
                            Pesan Terkirim
                        </h4>
                        <div class="space-y-3">
                            <div
                                v-for="msg in recentMessages"
                                :key="msg.id"
                                class="group relative rounded-2xl border border-white/5 bg-white/5 p-3"
                            >
                                <div
                                    class="mb-1 flex items-center justify-between"
                                >
                                    <span
                                        class="text-xs font-black text-foreground"
                                        >Ke: {{ msg.receiver?.name }}</span
                                    >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-[9px] font-bold text-muted-foreground uppercase"
                                            >{{
                                                formatDate(msg.created_at)
                                            }}</span
                                        >
                                        <button
                                            @click="deleteMessage(msg.id)"
                                            class="text-muted-foreground transition-all hover:text-destructive"
                                            title="Hapus Pesan"
                                        >
                                            <Trash2 class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>
                                <p
                                    class="line-clamp-2 text-xs text-muted-foreground"
                                >
                                    {{ msg.content }}
                                </p>
                                <div class="mt-2 text-right">
                                    <span
                                        v-if="msg.is_read"
                                        class="text-[9px] font-bold text-accent uppercase"
                                        >Dibaca</span
                                    >
                                    <span
                                        v-else
                                        class="text-[9px] font-bold text-muted-foreground uppercase"
                                        >Terkirim</span
                                    >
                                </div>
                            </div>
                            <div
                                v-if="recentMessages.length === 0"
                                class="p-4 text-center"
                            >
                                <p
                                    class="text-xs font-bold text-muted-foreground uppercase"
                                >
                                    Belum ada pesan terkirim
                                </p>
                            </div>
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
