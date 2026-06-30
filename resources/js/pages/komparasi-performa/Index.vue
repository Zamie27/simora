<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { BarChart3, Loader2, Users, Activity, Info } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

import { useAppearance } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';

interface Athlete {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    athlete_profile?: {
        profile_photo_path: string;
    } | null;
}

interface ComparisonItem {
    athlete_id: number;
    total_distance_km: number;
    avg_speed: number;
    avg_rpm: number;
    total_duration_minutes: number;
    total_sessions: number;
}

interface TrendPoint {
    date: string;
    distance_km: number;
    avg_speed: number;
    rpm: number;
}

defineProps<{ athletes: Athlete[] }>();

const page = usePage();
const { resolvedAppearance } = useAppearance();
const userRole = computed(() => (page.props.auth as any).user.role.name);

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    {
        title: 'Perbandingan Performa',
        href:
            userRole.value === 'Manajemen'
                ? '/manajemen/komparasi-performa'
                : '/pelatih/komparasi-performa',
    },
]);

const dataEndpoint = computed(() =>
    userRole.value === 'Manajemen'
        ? '/manajemen/komparasi-performa/data'
        : '/pelatih/komparasi-performa/data',
);

const selectedAthletes = ref<number[]>([]);
const loading = ref(false);
const comparisonData = ref<ComparisonItem[]>([]);
const trendData = ref<Record<number, TrendPoint[]>>({});
const athleteMap = ref<Record<number, Athlete>>({});

const toggleAthlete = (id: number) => {
    const idx = selectedAthletes.value.indexOf(id);

    if (idx > -1) {
        selectedAthletes.value.splice(idx, 1);
    } else {
        selectedAthletes.value.push(id);
    }
};

const fetchComparison = async () => {
    if (selectedAthletes.value.length < 2) {
        return;
    }

    loading.value = true;

    try {
        const res = await axios.get(dataEndpoint.value, {
            params: { athlete_ids: selectedAthletes.value },
        });
        comparisonData.value = res.data.comparison;
        trendData.value = res.data.trends;

        const athletes: Record<number, Athlete> = {};
        Object.entries(res.data.athletes).forEach(([id, a]: [string, any]) => {
            athletes[Number(id)] = a;
        });
        athleteMap.value = athletes;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

// Chart Configurations
const barChartOptions = computed(() => ({
    chart: {
        type: 'bar',
        toolbar: { show: false },
        zoom: { enabled: false },
        background: 'transparent',
        foreColor: resolvedAppearance.value === 'dark' ? '#adb5bd' : '#495057',
    },
    theme: {
        mode: resolvedAppearance.value as 'light' | 'dark',
    },
    plotOptions: {
        bar: {
            borderRadius: 8,
            columnWidth: '50%',
            distributed: false,
        },
    },
    colors: ['#696cff', '#03c3ec', '#71dd37', '#ff3e1d'],
    dataLabels: { enabled: false },
    xaxis: {
        categories: comparisonData.value.map(
            (item) => athleteMap.value[item.athlete_id]?.name || 'Unknown',
        ),
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            formatter: (val: number) => val.toFixed(1),
            style: {
                colors:
                    resolvedAppearance.value === 'dark' ? '#adb5bd' : '#495057',
            },
        },
    },
    grid: {
        borderColor: resolvedAppearance.value === 'dark' ? '#444' : '#e0e0e0',
        strokeDashArray: 4,
        padding: { top: 0, bottom: 0 },
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        fontSize: '12px',
        fontWeight: 600,
        labels: {
            colors: resolvedAppearance.value === 'dark' ? '#adb5bd' : '#495057',
        },
    },
    tooltip: {
        theme: resolvedAppearance.value as 'light' | 'dark',
    },
}));

const barSeries = computed(() => [
    {
        name: 'Jarak (KM)',
        data: comparisonData.value.map((item) => item.total_distance_km),
    },
    {
        name: 'Kecepatan Rata-rata (KM/H)',
        data: comparisonData.value.map((item) => item.avg_speed),
    },
    {
        name: 'Durasi (Menit/10)',
        data: comparisonData.value.map(
            (item) => item.total_duration_minutes / 10,
        ),
    },
]);

const trendChartOptions = computed(() => ({
    chart: {
        type: 'line',
        toolbar: { show: false },
        background: 'transparent',
        foreColor: resolvedAppearance.value === 'dark' ? '#adb5bd' : '#495057',
    },
    theme: {
        mode: resolvedAppearance.value as 'light' | 'dark',
    },
    stroke: {
        curve: 'smooth',
        width: 3,
    },
    markers: {
        size: 4,
    },
    colors: ['#696cff', '#03c3ec', '#71dd37', '#ff3e1d', '#ffab00', '#8592a3'],
    xaxis: {
        type: 'datetime',
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            formatter: (val: number) => val.toFixed(1),
            style: {
                colors:
                    resolvedAppearance.value === 'dark' ? '#adb5bd' : '#495057',
            },
        },
    },
    grid: {
        borderColor: resolvedAppearance.value === 'dark' ? '#444' : '#e0e0e0',
        strokeDashArray: 4,
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        labels: {
            colors: resolvedAppearance.value === 'dark' ? '#adb5bd' : '#495057',
        },
    },
    tooltip: {
        theme: resolvedAppearance.value as 'light' | 'dark',
        x: { format: 'dd MMM yyyy' },
    },
}));

const speedTrendSeries = computed(() => {
    return Object.entries(trendData.value).map(([id, points]) => ({
        name: athleteMap.value[Number(id)]?.name || 'Unknown',
        data: points.map((p) => ({
            x: new Date(p.date).getTime(),
            y: p.avg_speed,
        })),
    }));
});

const distanceTrendSeries = computed(() => {
    return Object.entries(trendData.value).map(([id, points]) => ({
        name: athleteMap.value[Number(id)]?.name || 'Unknown',
        data: points.map((p) => ({
            x: new Date(p.date).getTime(),
            y: p.distance_km,
        })),
    }));
});

const initials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
};
</script>

<template>
    <Head title="Perbandingan Performa" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-8 bg-background p-6 text-foreground md:p-10"
        >
            <!-- Header Section -->
            <div
                class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary shadow-sm"
                    >
                        <BarChart3 class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-black tracking-tight text-foreground uppercase"
                        >
                            Komparasi Performa
                        </h1>
                        <p
                            class="text-xs font-bold tracking-widest text-muted-foreground uppercase opacity-70"
                        >
                            Analisis dan Bandingkan Metrik Atlet
                        </p>
                    </div>
                </div>

                <div
                    class="flex items-center gap-2 rounded-xl border border-border/50 bg-muted/30 px-4 py-2 text-[10px] font-bold text-muted-foreground uppercase"
                >
                    <Users class="h-3 w-3" />
                    {{ userRole }} Mode
                </div>
            </div>

            <!-- Athlete Selection Card -->
            <div
                class="group relative overflow-hidden rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl transition-all duration-300 hover:shadow-primary/5"
            >
                <div
                    class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-primary/5 blur-3xl transition-all duration-500 group-hover:bg-primary/10"
                ></div>

                <div class="relative">
                    <div class="mb-6 flex items-center gap-2">
                        <Users class="h-4 w-4 text-primary" />
                        <h2
                            class="text-sm font-black tracking-widest text-foreground uppercase"
                        >
                            Pilih Atlet Untuk Dibandingkan
                        </h2>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="athlete in athletes"
                            :key="athlete.id"
                            @click="toggleAthlete(athlete.id)"
                            class="relative flex items-center gap-3 overflow-hidden rounded-2xl border px-5 py-3 transition-all duration-300 active:scale-95"
                            :class="
                                selectedAthletes.includes(athlete.id)
                                    ? 'border-primary bg-primary/10 text-primary shadow-lg ring-1 shadow-primary/10 ring-primary/20'
                                    : 'border-border bg-muted/20 text-muted-foreground hover:border-primary/40 hover:bg-muted/40'
                            "
                        >
                            <div
                                class="flex h-6 w-6 items-center justify-center overflow-hidden rounded-lg text-[10px] font-black"
                                :class="
                                    selectedAthletes.includes(athlete.id)
                                        ? 'bg-primary text-white'
                                        : 'bg-muted text-muted-foreground'
                                "
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
                                    {{ initials(athlete.name) }}
                                </span>
                            </div>
                            <span class="text-xs font-bold">{{
                                athlete.name
                            }}</span>
                        </button>
                    </div>

                    <div
                        class="mt-8 flex flex-col gap-4 md:flex-row md:items-center"
                    >
                        <button
                            @click="fetchComparison"
                            :disabled="selectedAthletes.length < 2 || loading"
                            class="flex h-12 items-center justify-center gap-3 rounded-2xl bg-primary px-10 text-xs font-black tracking-widest text-white shadow-xl shadow-primary/25 transition-all duration-300 hover:translate-y-[-2px] hover:bg-primary/90 active:translate-y-0 disabled:translate-y-0 disabled:opacity-50"
                        >
                            <Loader2
                                v-if="loading"
                                class="h-4 w-4 animate-spin"
                            />
                            <Activity v-else class="h-4 w-4" />
                            {{
                                loading
                                    ? 'MENGANALISIS...'
                                    : 'BANDINGKAN SEKARANG'
                            }}
                        </button>

                        <p
                            v-if="selectedAthletes.length < 2"
                            class="flex items-center gap-2 text-[10px] font-bold text-muted-foreground uppercase opacity-60"
                        >
                            <Info class="h-3 w-3" />
                            Pilih minimal 2 atlet untuk memulai komparasi
                        </p>
                    </div>
                </div>
            </div>

            <!-- Analysis Results -->
            <div
                v-if="comparisonData.length > 0"
                class="flex animate-in flex-col gap-8 duration-700 fade-in slide-in-from-bottom-4"
            >
                <!-- Summary Metrics Bar Chart -->
                <div
                    class="rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl"
                >
                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h3
                                class="text-xl font-black tracking-tight text-foreground uppercase"
                            >
                                Dashboard Performa
                            </h3>
                            <p
                                class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Perbandingan Metrik Utama
                            </p>
                        </div>
                        <div class="rounded-xl bg-muted/30 p-2">
                            <BarChart3 class="h-5 w-5 text-primary" />
                        </div>
                    </div>
                    <div class="h-[400px]">
                        <VueApexCharts
                            type="bar"
                            height="100%"
                            :options="barChartOptions"
                            :series="barSeries"
                        />
                    </div>
                </div>

                <!-- Trend Charts Grid -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Speed Trend -->
                    <div
                        class="rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl"
                    >
                        <div class="mb-6">
                            <h3
                                class="text-lg font-black tracking-tight text-foreground uppercase"
                            >
                                Tren Kecepatan
                            </h3>
                            <p
                                class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                KM/H Terhadap Waktu
                            </p>
                        </div>
                        <div class="h-[300px]">
                            <VueApexCharts
                                type="line"
                                height="100%"
                                :options="trendChartOptions"
                                :series="speedTrendSeries"
                            />
                        </div>
                    </div>

                    <!-- Distance Trend -->
                    <div
                        class="rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl"
                    >
                        <div class="mb-6">
                            <h3
                                class="text-lg font-black tracking-tight text-foreground uppercase"
                            >
                                Tren Jarak
                            </h3>
                            <p
                                class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                KM Terhadap Waktu
                            </p>
                        </div>
                        <div class="h-[300px]">
                            <VueApexCharts
                                type="line"
                                height="100%"
                                :options="trendChartOptions"
                                :series="distanceTrendSeries"
                            />
                        </div>
                    </div>
                </div>

                <!-- Detailed Stats Table -->
                <div
                    class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-2xl"
                >
                    <div class="border-b border-border/50 p-8">
                        <h3
                            class="text-center text-lg font-black tracking-tight text-foreground uppercase"
                        >
                            Data Komparasi Terperinci
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-muted/30">
                                    <th
                                        class="px-8 py-5 text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                    >
                                        Atlet
                                    </th>
                                    <th
                                        class="px-8 py-5 text-right text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                    >
                                        Total Jarak (KM)
                                    </th>
                                    <th
                                        class="px-8 py-5 text-right text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                    >
                                        Rata-rata Speed
                                    </th>
                                    <th
                                        class="px-8 py-5 text-right text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                    >
                                        Avg RPM
                                    </th>
                                    <th
                                        class="px-8 py-5 text-right text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                    >
                                        Total Sesi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/50">
                                <tr
                                    v-for="item in comparisonData"
                                    :key="item.athlete_id"
                                    class="transition-colors hover:bg-muted/10"
                                >
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-primary/10 text-[10px] font-black text-primary"
                                            >
                                                <img
                                                    v-if="
                                                        athleteMap[
                                                            item.athlete_id
                                                        ]?.athlete_profile
                                                            ?.profile_photo_path
                                                    "
                                                    :src="`/documents/${item.athlete_id}/profile_photo`"
                                                    class="h-full w-full object-cover"
                                                />
                                                <img
                                                    v-else-if="
                                                        athleteMap[
                                                            item.athlete_id
                                                        ]?.avatar
                                                    "
                                                    :src="
                                                        athleteMap[
                                                            item.athlete_id
                                                        ].avatar
                                                    "
                                                    class="h-full w-full object-cover"
                                                />
                                                <span v-else>
                                                    {{
                                                        initials(
                                                            athleteMap[
                                                                item.athlete_id
                                                            ]?.name || '',
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                            <span
                                                class="text-sm font-black text-foreground"
                                                >{{
                                                    athleteMap[item.athlete_id]
                                                        ?.name || ''
                                                }}</span
                                            >
                                        </div>
                                    </td>
                                    <td
                                        class="px-8 py-5 text-right font-mono text-sm font-bold text-foreground"
                                    >
                                        {{ item.total_distance_km }}
                                    </td>
                                    <td
                                        class="px-8 py-5 text-right font-mono text-sm font-bold text-primary"
                                    >
                                        {{ item.avg_speed }}
                                    </td>
                                    <td
                                        class="px-8 py-5 text-right font-mono text-sm font-bold text-accent"
                                    >
                                        {{ item.avg_rpm }}
                                    </td>
                                    <td
                                        class="px-8 py-5 text-right font-mono text-sm font-bold text-foreground"
                                    >
                                        {{ item.total_sessions }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else-if="!loading"
                class="flex flex-col items-center justify-center gap-6 py-20 text-center opacity-40"
            >
                <div
                    class="flex h-24 w-24 items-center justify-center rounded-[2rem] border border-border bg-muted/50"
                >
                    <Activity class="h-10 w-10 text-muted-foreground" />
                </div>
                <div>
                    <h3
                        class="text-xl font-black tracking-tight text-foreground uppercase"
                    >
                        Belum Ada Data Untuk Dibandingkan
                    </h3>
                    <p
                        class="max-w-xs text-[10px] font-bold tracking-widest text-muted-foreground uppercase"
                    >
                        Pilih atlet di atas dan klik tombol bandingkan untuk
                        melihat analisis performa
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.glass-card {
    background: rgba(var(--card), 0.7);
    backdrop-filter: blur(10px);
}

:deep(.apexcharts-canvas) {
    margin: 0 auto;
}

:deep(.apexcharts-tooltip) {
    border-radius: 1rem !important;
    border: 1px solid hsl(var(--border)) !important;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1) !important;
}

:deep(.apexcharts-legend-text) {
    color: hsl(var(--foreground)) !important;
    font-family: inherit !important;
}
</style>
