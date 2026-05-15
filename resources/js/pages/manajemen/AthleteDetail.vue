<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import {
    Activity,
    AlertCircle,
    Calendar,
    Clock,
    Milestone,
    RotateCcw,
    Save,
    TrendingUp,
    X,
} from 'lucide-vue-next';

import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import DatePicker from '@/components/ui/DatePicker.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface Profile {
    id: number;
    profile_photo_path?: string;
    birth_certificate_path?: string;
    family_card_path?: string;
    id_card_path?: string;
    license_path?: string;
    uci_id?: string;
    license_valid_until?: string;
}

interface DataFisik {
    id: number;
    height: number;
    weight: number;
    bmi: number;
    bmi_status: string;
    age: number;
    category: string;
    recorded_at: string;
}

interface Kategori {
    id: number;
    name: string;
}

interface LampiranLatihan {
    id: number;
    file_path: string;
    file_name: string;
    file_type: string;
}

interface LogLatihan {
    id: number;
    title: string | null;
    date: string;
    distance_km: number | string | null;
    duration_minutes: number | string | null;
    avg_speed: number | string | null;
    avg_heart_rate?: number | string | null;
    pace_per_km?: string | null;
    avg_watt_power?: number | string | null;
    elevation_m?: number | string | null;
    temperature_c?: number | string | null;
    calories?: number | string | null;
    rpm: number | string | null;
    intensity: string | null;
    type: string | null;
    athlete_notes: string | null;
    attendance_status: string;
    completion_status: string;
    coach_rating: number | string | null;
    coach_evaluation: string | null;
    coach_comments: string | null;
    session?: {
        id: number;
        exercise_type: { name: string };
    } | null;
    attachments: LampiranLatihan[];
}

interface Statistics {
    total_distance_km: number;
    total_duration_minutes: number;
    avg_speed: number;
    avg_rpm: number;
    total_sessions: number;
    completed_sessions: number;
}

interface User {
    id: number;
    name: string;
    email: string;
    gender: string | null;
    date_of_birth: string | null;
    coach_id?: number | null;
    coach?: { name: string };
    created_at: string;
    athlete_profile?: Profile;
    category_id: number | null;
    category: Kategori | null;
    physical_metrics: DataFisik[];
    avatar?: string | null;
}

interface Coach {
    id: number;
    name: string;
}

const props = defineProps<{
    athlete: User;
    coaches: Coach[];
    trainingLogs: LogLatihan[];
    statistics: Statistics;
    performanceTrend: any[];
    categories: Kategori[];
    filters: {
        start_date: string | null;
        end_date: string | null;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Daftar Atlet Terverifikasi', href: '/manajemen/atlet' },
    {
        title: `Detail: ${props.athlete.name}`,
        href: `/manajemen/atlet/${props.athlete.id}`,
    },
];

const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const coachForm = useForm({
    coach_id: props.athlete.coach_id || null,
});


const isEditingCoach = ref(false);

const toggleEditCoach = () => {
    isEditingCoach.value = !isEditingCoach.value;
};

const updateCoach = () => {
    coachForm.patch(`/manajemen/atlet/${props.athlete.id}/coach`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditingCoach.value = false;
        },
    });
};


const applyFilters = () => {
    router.get(
        `/manajemen/atlet/${props.athlete.id}`,
        {
            start_date: startDate.value,
            end_date: endDate.value,
        },
        { preserveState: true },
    );
};

const formatDate = (date: string) => {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    return `${day}/${month}/${year}`;
};


// Physical Chart Options (Adapted from coach view)
const physicalChartOptions = computed<ApexOptions>(() => ({
    chart: {
        id: 'athlete-metrics',
        theme: 'dark',
        background: 'transparent',
        toolbar: { show: false },
    },
    colors: ['#FF6120', '#102844'],
    stroke: { curve: 'smooth', width: 3 },
    xaxis: {
        categories: [...props.athlete.physical_metrics]
            .reverse()
            .map((m) => formatDate(m.recorded_at)),
        labels: { style: { colors: '#94a3b8' } },
        axisBorder: { show: false },
    },
    yaxis: {
        labels: { style: { colors: '#94a3b8' } },
    },
    grid: { borderColor: '#1e293b', strokeDashArray: 4 },
    tooltip: { theme: 'dark' },
}));

const physicalChartSeries = computed(() => [
    {
        name: 'Weight (kg)',
        data: [...props.athlete.physical_metrics]
            .reverse()
            .map((m) => Number(m.weight)),
    },
    {
        name: 'Height (cm)',
        data: [...props.athlete.physical_metrics]
            .reverse()
            .map((m) => Number(m.height)),
    },
]);

// Training Chart Options (Adapted from coach view)
const trainingChartOptions = computed<ApexOptions>(() => ({
    chart: {
        id: 'training-trends',
        theme: 'dark',
        background: 'transparent',
        toolbar: { show: false },
    },
    colors: ['#FF6120', '#10B981'],
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: props.performanceTrend.map((t) => formatDate(t.date)),
        labels: { style: { colors: '#94a3b8' }, rotate: -45 },
    },
    yaxis: [
        {
            title: { text: 'Avg Speed (kph)', style: { color: '#FF6120' } },
            labels: { style: { colors: '#FF6120' } },
        },
        {
            opposite: true,
            title: {
                text: 'Avg Cadence (RPM)',
                style: { color: '#10B981' },
            },
            labels: { style: { colors: '#10B981' } },
        },
    ],
    grid: { borderColor: '#1e293b' },
    tooltip: { theme: 'dark' },
}));

const trainingChartSeries = computed(() => [
    { name: 'Avg Speed', data: props.performanceTrend.map((t) => t.avg_speed) },
    { name: 'Avg RPM', data: props.performanceTrend.map((t) => t.rpm) },
]);
</script>

<template>
    <Head :title="`Detail Atlet | ${athlete.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <!-- Header -->
            <div
                class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row"
            >
                <div class="flex items-center gap-6">
                    <div
                        class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-2 border-accent/20 bg-accent/20 text-2xl font-black text-accent shadow-2xl shadow-accent/10"
                    >
                        <img
                            v-if="athlete.athlete_profile?.profile_photo_path"
                            :src="`/documents/${athlete.id}/profile_photo`"
                            class="h-full w-full object-cover"
                        />
                        <span v-else>
                            {{
                                athlete.name
                                    .split(' ')
                                    .map((n) => n[0])
                                    .slice(0, 2)
                                    .join('')
                            }}
                        </span>
                    </div>
                    <div>
                        <h1
                            class="text-4xl font-black tracking-tighter text-foreground uppercase"
                        >
                            {{ athlete.name }}
                        </h1>
                        <p
                            class="mt-1 text-xs font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                        >
                            {{ athlete.email }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-4">
                    <div
                        class="flex shrink-0 items-center gap-4 rounded-2xl border border-accent/20 bg-accent/5 p-4 shadow-sm backdrop-blur-sm"
                    >
                        <div class="flex flex-col min-w-[150px]">
                            <span
                                class="text-[9px] font-black tracking-widest text-accent uppercase opacity-80"
                                >Pelatih Pembina</span
                            >
                            <div
                                v-if="isEditingCoach"
                                class="mt-2 flex items-center gap-2"
                            >
                                <select
                                    v-model="coachForm.coach_id"
                                    class="rounded-lg border border-border bg-background px-3 py-1.5 text-xs font-bold text-foreground focus:ring-2 focus:ring-accent focus:outline-none dark:[color-scheme:dark] w-full"
                                >
                                    <option :value="null">
                                        Belum Ada Pelatih
                                    </option>
                                    <option
                                        v-for="c in coaches"
                                        :key="c.id"
                                        :value="c.id"
                                    >
                                        {{ c.name }}
                                    </option>
                                </select>
                                <div class="flex items-center gap-1">
                                    <button
                                        @click="updateCoach"
                                        :disabled="coachForm.processing"
                                        class="rounded-lg bg-emerald-500 p-2 text-white transition-all hover:bg-emerald-600 disabled:opacity-50"
                                        title="Simpan"
                                    >
                                        <Save class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="toggleEditCoach"
                                        class="rounded-lg bg-muted p-2 text-muted-foreground transition-all hover:bg-destructive hover:text-white"
                                        title="Batal"
                                    >
                                        <X class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>
                            <div
                                v-else
                                class="mt-1 flex items-center justify-between gap-6"
                            >
                                <p :class="[
                                    'text-sm font-black uppercase tracking-tight',
                                    athlete.coach?.name ? 'text-foreground' : 'text-muted-foreground/60'
                                ]">
                                    {{
                                        athlete.coach?.name ||
                                        'BELUM ADA PELATIH'
                                    }}
                                </p>
                                <button
                                    @click="toggleEditCoach"
                                    class="text-[10px] font-black text-accent transition-all hover:scale-105 active:scale-95 bg-accent/10 px-2 py-1 rounded-md"
                                >
                                    GANTI
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Completion Warning -->
            <div
                v-if="!athlete.date_of_birth || !athlete.gender"
                class="group flex items-center justify-between gap-6 rounded-3xl border border-destructive/20 bg-destructive/10 p-6"
            >
                <div class="flex items-center gap-6">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive text-white shadow-xl shadow-destructive/20 transition-transform group-hover:scale-110"
                    >
                        <AlertCircle class="h-6 w-6" />
                    </div>
                    <div>
                        <h3
                            class="text-lg font-black tracking-tight text-foreground uppercase"
                        >
                            Data Profil Atlet Tidak Lengkap
                        </h3>
                        <p
                            class="text-xs font-medium text-muted-foreground italic opacity-70"
                        >
                            Atlet belum melengkapi data profil dasar (Tanggal
                            Lahir/Gender). Usia dan BMI tidak dapat dikalkulasi
                            secara akurat.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Training Stats Section -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group relative overflow-hidden rounded-[2rem] border border-border bg-card p-8 shadow-xl transition-all hover:shadow-2xl"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="rounded-2xl bg-orange-500/10 p-4 text-orange-500 transition-colors duration-500 group-hover:bg-orange-500 group-hover:text-white"
                        >
                            <Milestone class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Total Jarak
                            </p>
                            <h4 class="text-3xl font-black italic">
                                {{ statistics.total_distance_km }}
                                <small class="text-xs">KM</small>
                            </h4>
                        </div>
                    </div>
                </div>

                <div
                    class="group relative overflow-hidden rounded-[2rem] border border-border bg-card p-8 shadow-xl transition-all hover:shadow-2xl"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="rounded-2xl bg-blue-500/10 p-4 text-blue-500 transition-colors duration-500 group-hover:bg-blue-500 group-hover:text-white"
                        >
                            <Clock class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Durasi
                            </p>
                            <h4 class="text-3xl font-black italic">
                                {{ statistics.total_duration_minutes }}
                                <small class="text-xs">MIN</small>
                            </h4>
                        </div>
                    </div>
                </div>

                <div
                    class="group relative overflow-hidden rounded-[2rem] border border-border bg-card p-8 shadow-xl transition-all hover:shadow-2xl"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="rounded-2xl bg-orange-500/10 p-4 text-orange-500 transition-colors duration-500 group-hover:bg-orange-500 group-hover:text-white"
                        >
                            <TrendingUp
                                class="h-6 w-6 transition-colors duration-500 group-hover:text-white"
                            />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Avg Speed
                            </p>
                            <h4 class="text-3xl font-black italic">
                                {{ statistics.avg_speed }}
                                <small class="text-xs">KPH</small>
                            </h4>
                        </div>
                    </div>
                </div>

                <div
                    class="group relative overflow-hidden rounded-[2rem] border border-border bg-card p-8 shadow-xl transition-all hover:shadow-2xl"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="rounded-2xl bg-emerald-500/10 p-4 text-emerald-500 transition-colors duration-500 group-hover:bg-emerald-500 group-hover:text-white"
                        >
                            <RotateCcw class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Avg RPM
                            </p>
                            <h4 class="text-3xl font-black italic">
                                {{ statistics.avg_rpm }}
                                <small class="text-xs">RPM</small>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Training Trend Chart -->
                <div
                    class="rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl"
                >
                    <div class="mb-8 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-xl bg-orange-500/10 p-3 text-orange-500"
                            >
                                <TrendingUp class="h-5 w-5" />
                            </div>
                            <h3
                                class="text-xl font-black tracking-tight uppercase"
                            >
                                Tren Performa Latihan
                            </h3>
                        </div>
                    </div>
                    <div id="training-chart">
                        <VueApexCharts
                            width="100%"
                            height="350"
                            type="line"
                            :options="trainingChartOptions"
                            :series="trainingChartSeries"
                        ></VueApexCharts>
                    </div>
                </div>

                <!-- Physical Trend Chart -->
                <div
                    class="rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl"
                >
                    <div class="mb-8 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-xl bg-secondary p-3 text-accent"
                            >
                                <Activity class="h-5 w-5" />
                            </div>
                            <h3
                                class="text-xl font-black tracking-tight uppercase"
                            >
                                Progress Fisik (BB/TB)
                            </h3>
                        </div>
                    </div>
                    <div id="physical-chart">
                        <VueApexCharts
                            width="100%"
                            height="350"
                            type="line"
                            :options="physicalChartOptions"
                            :series="physicalChartSeries"
                        ></VueApexCharts>
                    </div>
                </div>
            </div>

            <!-- Main Content & Sidebar -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Training History (Left) -->
                <div class="flex flex-col gap-6 lg:col-span-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-xl bg-secondary p-3 text-accent"
                            >
                                <Calendar class="h-5 w-5" />
                            </div>
                            <h2
                                class="text-2xl font-black tracking-tight uppercase"
                            >
                                Riwayat Latihan
                            </h2>
                        </div>
                    </div>

                    <!-- Filters for Log -->
                    <div
                        class="flex flex-wrap items-end gap-4 rounded-3xl border border-border bg-card p-6 shadow-xl"
                    >
                        <div class="flex min-w-[200px] flex-col gap-2">
                            <Label
                                class="text-[10px] font-black uppercase opacity-60"
                                >Mulai Dari</Label
                            >
                            <DatePicker v-model="startDate" />
                        </div>
                        <div class="flex min-w-[200px] flex-col gap-2">
                            <Label
                                class="text-[10px] font-black uppercase opacity-60"
                                >Sampai Ke</Label
                            >
                            <DatePicker v-model="endDate" />
                        </div>
                        <button
                            @click="applyFilters"
                            class="rounded-xl bg-accent px-6 py-3.5 text-[10px] font-black tracking-widest text-white uppercase hover:bg-accent/90"
                        >
                            Filter
                        </button>
                    </div>

                    <!-- Logs Table -->
                    <div
                        class="overflow-hidden rounded-3xl border border-border bg-card shadow-2xl"
                    >
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead
                                    class="border-b border-border bg-muted/40"
                                >
                                    <tr
                                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                    >
                                        <th class="px-8 py-5">Atlet</th>
                                        <th class="px-6 py-5">Tanggal</th>
                                        <th class="px-6 py-5">Jenis</th>
                                        <th class="px-6 py-5">Km / Speed</th>
                                        <th class="px-6 py-5">Power / Pace</th>
                                        <th class="px-6 py-5">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/50">
                                    <tr
                                        v-for="log in trainingLogs"
                                        :key="log.id"
                                        class="group transition-colors hover:bg-muted/10"
                                    >
                                        <td class="px-8 py-6">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full border border-border bg-secondary"
                                                >
                                                    <img
                                                        v-if="
                                                            athlete
                                                                .athlete_profile
                                                                ?.profile_photo_path
                                                        "
                                                        :src="`/documents/${athlete.id}/profile_photo`"
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <img
                                                        v-else-if="
                                                            athlete.avatar
                                                        "
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
                                                                .map(
                                                                    (n) => n[0],
                                                                )
                                                                .slice(0, 2)
                                                                .join('')
                                                        }}
                                                    </span>
                                                </div>
                                                <span
                                                    class="text-xs font-bold text-foreground"
                                                    >{{ athlete.name }}</span
                                                >
                                            </div>
                                        </td>
                                        <td class="px-6 py-6">
                                            <p
                                                class="text-xs font-black text-foreground"
                                            >
                                                {{ formatDate(log.date) }}
                                            </p>
                                            <span
                                                v-if="log.session"
                                                class="text-[8px] font-bold tracking-tighter text-accent uppercase"
                                                >Scheduled Sesi</span
                                            >
                                        </td>
                                        <td class="px-6 py-6">
                                            <p
                                                class="mb-1 text-xs font-black text-foreground"
                                            >
                                                {{
                                                    log.title ||
                                                    (log.session
                                                        ? log.session
                                                              .exercise_type
                                                              .name
                                                        : log.type) ||
                                                    'General Session'
                                                }}
                                            </p>
                                            <span
                                                class="rounded-lg border border-border bg-secondary px-2 py-1 text-[8px] font-black text-muted-foreground uppercase"
                                                >{{
                                                    log.type || 'General'
                                                }}</span
                                            >
                                        </td>
                                        <td class="px-6 py-6">
                                            <p class="text-xs font-black">
                                                {{ log.distance_km || 0 }}
                                                <small
                                                    class="uppercase opacity-40"
                                                    >km</small
                                                >
                                            </p>
                                            <p
                                                class="text-[10px] font-bold text-accent italic"
                                            >
                                                {{ log.avg_speed || 0 }} kph
                                            </p>
                                        </td>
                                        <td class="px-6 py-6">
                                            <p
                                                class="text-sm font-black text-orange-500 italic"
                                            >
                                                {{ log.avg_watt_power || '-' }}
                                                <small
                                                    class="text-[10px] opacity-40"
                                                    >W</small
                                                >
                                            </p>
                                            <p
                                                class="text-[10px] font-bold text-muted-foreground"
                                            >
                                                {{ log.pace_per_km || '-' }} /KM
                                            </p>
                                        </td>
                                        <td class="px-6 py-6">
                                            <span
                                                :class="[
                                                    'rounded-lg px-3 py-1.5 text-[9px] font-black tracking-wider uppercase',
                                                    log.completion_status ===
                                                    'completed'
                                                        ? 'bg-emerald-500/10 text-emerald-500'
                                                        : 'bg-orange-500/10 text-orange-500',
                                                ]"
                                            >
                                                {{ log.completion_status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="trainingLogs.length === 0">
                                        <td
                                            colspan="5"
                                            class="px-8 py-20 text-center"
                                        >
                                            <p
                                                class="text-xs font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                            >
                                                Belum ada riwayat latihan.
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (Right) -->
                <div class="flex flex-col gap-8 lg:col-span-4">
                    <!-- Physical Overview Card -->
                    <div
                        class="group relative overflow-hidden rounded-[2.5rem] border border-white/5 bg-secondary p-8 shadow-2xl"
                    >
                        <div
                            class="absolute -top-4 -right-4 h-32 w-32 scale-150 rotate-12 rounded-full bg-white/5 transition-transform group-hover:scale-110"
                        ></div>
                        <h3
                            class="relative z-10 mb-8 text-[10px] font-black tracking-[0.2em] text-[#a4badd] uppercase opacity-60"
                        >
                            Status Fisik Terkini
                        </h3>
                        <div class="relative z-10 space-y-6">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[10px] font-black uppercase opacity-40"
                                    >BB / TB</span
                                >
                                <p class="text-xl font-black italic">
                                    {{
                                        athlete.physical_metrics[0]?.weight ||
                                        '?'
                                    }}kg /
                                    {{
                                        athlete.physical_metrics[0]?.height ||
                                        '?'
                                    }}cm
                                </p>
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[10px] font-black uppercase opacity-40"
                                    >BMI Score</span
                                >
                                <p
                                    class="text-xl font-black text-accent italic"
                                >
                                    {{
                                        athlete.physical_metrics[0]?.bmi || '?'
                                    }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-4 text-center">
                                <span
                                    class="text-[9px] font-bold tracking-[0.2em] text-accent uppercase"
                                    >{{
                                        athlete.physical_metrics[0]
                                            ?.bmi_status || 'Data Fisik Kosong'
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
