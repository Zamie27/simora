<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import {
    Activity,
    AlertCircle,
    Calendar,
    Clock,
    Edit3,
    Gauge,
    Milestone,
    Plus,
    RotateCcw,
    Save,
    TrendingUp,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

import CustomSelect from '@/components/ui/CustomSelect.vue';
import DatePicker from '@/components/ui/DatePicker.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface PhysicalMetric {
    id: number;
    height: number;
    weight: number;
    bmi: number;
    bmi_status: string;
    age: number;
    category: string;
    recorded_at: string;
}

interface Category {
    id: number;
    name: string;
}

interface TrainingAttachment {
    id: number;
    file_path: string;
    file_name: string;
    file_type: string;
}

interface TrainingSession {
    id: number;
    exercise_type: { name: string };
    scheduled_date: string;
}

interface TrainingLog {
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
    hr_zone?: string | null;
    trimp?: number | string | null;
    vo2_max?: number | string | null;
    rpm: number | string | null;
    intensity: string | null;
    type: string | null;
    athlete_notes: string | null;
    attendance_status: string;
    completion_status: string;
    coach_rating: number | string | null;
    coach_evaluation: string | null;
    coach_comments: string | null;
    session: TrainingSession | null;
    attachments: TrainingAttachment[];
    is_editable?: boolean;
}

interface Statistics {
    total_distance_km: number;
    total_duration_minutes: number;
    avg_speed: number;
    avg_rpm: number;
    total_sessions: number;
    completed_sessions: number;
}

interface Athlete {
    id: number;
    name: string;
    email: string;
    gender: string | null;
    date_of_birth: string | null;
    category_id: number | null;
    category: Category | null;
    physical_metrics: PhysicalMetric[];
}

const props = defineProps<{
    athlete: Athlete;
    categories: Category[];
    trainingLogs: TrainingLog[];
    statistics: Statistics;
    performanceTrend: any[];
    filters: {
        start_date: string | null;
        end_date: string | null;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Atlet Saya', href: '/coach/athletes' },
    { title: props.athlete.name, href: `/coach/athletes/${props.athlete.id}` },
];

const showAddModal = ref(false);
const showEditLogModal = ref(false);
const selectedLog = ref<TrainingLog | null>(null);

const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const form = useForm({
    height: props.athlete.physical_metrics[0]?.height || '',
    weight: props.athlete.physical_metrics[0]?.weight || '',
    recorded_at: new Date().toISOString().split('T')[0],
});

const categoryForm = useForm({
    category_id: props.athlete.category_id?.toString() || '',
});

const logForm = useForm({
    title: '',
    distance_km: 0 as number | string,
    duration_minutes: 0 as number | string,
    avg_speed: 0 as number | string,
    rpm: 0 as number | string,
    avg_heart_rate: 0 as number | string,
    calories: 0 as number | string,
    elevation_m: 0 as number | string,
    temperature_c: 0 as number | string,
    intensity: 'low' as string,
    attendance_status: 'present' as string,
    completion_status: 'completed' as string,
    athlete_notes: '',
    coach_rating: 3 as number | string,
    coach_evaluation: '',
    coach_comments: '',
});

const submit = () => {
    form.post(`/coach/athletes/${props.athlete.id}/metrics`, {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset('recorded_at');
        },
    });
};

const updateCategory = () => {
    categoryForm.patch(`/coach/athletes/${props.athlete.id}/category`, {
        preserveScroll: true,
    });
};

const openEditLog = (log: TrainingLog) => {
    selectedLog.value = log;
    logForm.title = log.title || '';
    logForm.distance_km = log.distance_km ?? 0;
    logForm.duration_minutes = log.duration_minutes ?? 0;
    logForm.avg_speed = log.avg_speed ?? 0;
    logForm.rpm = log.rpm ?? 0;
    logForm.avg_heart_rate = log.avg_heart_rate ?? 0;
    logForm.calories = log.calories ?? 0;
    logForm.elevation_m = log.elevation_m ?? 0;
    logForm.temperature_c = log.temperature_c ?? 0;
    logForm.intensity = log.intensity || 'low';
    logForm.attendance_status = log.attendance_status;
    logForm.completion_status = log.completion_status;
    logForm.athlete_notes = log.athlete_notes || '';
    logForm.coach_rating = log.coach_rating ?? 3;
    logForm.coach_evaluation = log.coach_evaluation || '';
    logForm.coach_comments = log.coach_comments || '';
    showEditLogModal.value = true;
};

const deleteLog = (logId: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus log latihan ini?')) {
        router.delete(`/coach/training-logs/${logId}`);
    }
};

const submitLogUpdate = () => {
    if (!selectedLog.value) {
        return;
    }

    logForm.patch(`/coach/training-logs/${selectedLog.value.id}`, {
        onSuccess: () => {
            showEditLogModal.value = false;
        },
    });
};

const applyFilters = () => {
    router.get(
        `/coach/athletes/${props.athlete.id}`,
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

// Physical Chart Options
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

// Training Chart Options
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
            title: { text: 'Average Speed (kph)', style: { color: '#FF6120' } },
            labels: { style: { colors: '#FF6120' } },
        },
        {
            opposite: true,
            title: {
                text: 'Average Cadence (RPM)',
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

const intensityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'very_high', label: 'Very High' },
];

const attendanceOptions = [
    { value: 'present', label: 'Hadir' },
    { value: 'absent', label: 'Alpa' },
    { value: 'late', label: 'Terlambat' },
    { value: 'excused', label: 'Izin' },
];

const completionOptions = [
    { value: 'not_started', label: 'Belum Mulai' },
    { value: 'in_progress', label: 'Sedang Berjalan' },
    { value: 'completed', label: 'Selesai' },
    { value: 'incomplete', label: 'Tidak Selesai' },
];
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
                        class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-accent/20 bg-accent/20 text-2xl font-black text-accent shadow-2xl shadow-accent/10"
                    >
                        {{
                            athlete.name
                                .split(' ')
                                .map((n) => n[0])
                                .slice(0, 2)
                                .join('')
                        }}
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
                    <button
                        @click="showAddModal = true"
                        class="flex items-center gap-2 rounded-xl border border-border bg-secondary px-8 py-4 text-xs font-black tracking-widest text-accent uppercase shadow-xl transition-all hover:bg-secondary/90 active:scale-95"
                    >
                        <Plus class="h-4 w-4" /> Update Data Fisik
                    </button>
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
                            class="rounded-2xl bg-accent/10 p-4 text-accent transition-colors duration-500 group-hover:bg-accent group-hover:text-white"
                        >
                            <Gauge class="h-6 w-6" />
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
                                        <th class="px-8 py-5">Tanggal</th>
                                        <th class="px-6 py-5">Jenis</th>
                                        <th class="px-6 py-5">Km / Speed</th>
                                        <th class="px-6 py-5">Power / Pace</th>
                                        <th class="px-6 py-5">Status</th>
                                        <th class="px-8 py-5 text-right">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/50">
                                    <tr
                                        v-for="log in trainingLogs"
                                        :key="log.id"
                                        class="group transition-colors hover:bg-muted/10"
                                    >
                                        <td class="px-8 py-6">
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
                                            <p class="text-sm font-black text-orange-500 italic">
                                                {{ log.avg_watt_power || '-' }}
                                                <small class="text-[10px] opacity-40">W</small>
                                            </p>
                                            <p class="text-[10px] font-bold text-muted-foreground">
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
                                        <td class="px-8 py-6 text-right">
                                            <div
                                                class="flex items-center justify-end gap-2"
                                            >
                                                <button
                                                    v-if="log.is_editable"
                                                    @click="openEditLog(log)"
                                                    class="rounded-xl border border-border bg-card p-2 text-muted-foreground transition-all hover:border-accent hover:text-accent"
                                                    title="Edit Log"
                                                >
                                                    <Edit3 class="h-4 w-4" />
                                                </button>
                                                <button
                                                    @click="deleteLog(log.id)"
                                                    class="rounded-xl border border-border bg-card p-2 text-muted-foreground transition-all hover:border-destructive hover:text-destructive"
                                                    title="Delete Log"
                                                >
                                                    <X class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="trainingLogs.length === 0">
                                        <td
                                            colspan="6"
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
                                            ?.bmi_status || 'Waiting Data'
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Category Management Tool -->
                    <div
                        class="rounded-[2.5rem] border border-border bg-card p-10 shadow-xl"
                    >
                        <h3
                            class="mb-6 text-xs font-black tracking-[0.2em] text-muted-foreground uppercase opacity-60"
                        >
                            Kategori Atlet
                        </h3>
                        <form
                            @submit.prevent="updateCategory"
                            class="flex flex-col gap-4"
                        >
                            <CustomSelect
                                v-model="categoryForm.category_id"
                                :options="
                                    props.categories.map((c) => ({
                                        value: c.id.toString(),
                                        label: c.name,
                                    }))
                                "
                            />
                            <button
                                type="submit"
                                :disabled="categoryForm.processing"
                                class="flex w-full items-center justify-center gap-3 rounded-2xl bg-accent px-6 py-5 text-[10px] font-black tracking-widest text-white uppercase shadow-xl shadow-accent/10 transition-all hover:bg-accent/90"
                            >
                                <Save class="h-4 w-4" />
                                {{
                                    categoryForm.processing
                                        ? 'Saving...'
                                        : 'Update Kategori'
                                }}
                            </button>
                        </form>
                    </div>

                    <!-- Athlete Profile Info -->
                    <div
                        class="space-y-6 rounded-[2.5rem] border border-border bg-card p-10 shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between border-b border-border pb-4"
                        >
                            <span
                                class="text-[10px] font-black uppercase opacity-40"
                                >Age</span
                            >
                            <span class="text-xs font-black text-foreground"
                                >{{
                                    athlete.physical_metrics[0]?.age || '-'
                                }}
                                Thn</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-border pb-4"
                        >
                            <span
                                class="text-[10px] font-black uppercase opacity-40"
                                >Gender</span
                            >
                            <span
                                class="text-xs font-black text-foreground uppercase"
                                >{{ athlete.gender || '-' }}</span
                            >
                        </div>
                        <div class="flex items-center justify-between">
                            <span
                                class="text-[10px] font-black uppercase opacity-40"
                                >Verified Date</span
                            >
                            <span
                                class="text-xs font-black text-muted-foreground italic"
                                >20/03/2026</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: Update Physical Metric -->
            <div
                v-if="showAddModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-xl"
            >
                <div
                    class="w-full max-w-xl animate-in overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-2xl duration-300 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-border bg-muted/20 p-10"
                    >
                        <h2
                            class="text-2xl font-black tracking-tight uppercase"
                        >
                            Update Data Fisik
                        </h2>
                        <button
                            @click="showAddModal = false"
                            class="rounded-full p-2 hover:bg-muted"
                        >
                            <AlertCircle class="h-6 w-6 rotate-45" />
                        </button>
                    </div>
                    <form
                        @submit.prevent="submit"
                        class="grid grid-cols-2 gap-8 p-10"
                    >
                        <div class="flex flex-col gap-2">
                            <Label
                                class="text-[10px] font-black uppercase opacity-60"
                                >Tinggi (CM)</Label
                            >
                            <Input
                                type="number"
                                v-model="form.height"
                                class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label
                                class="text-[10px] font-black uppercase opacity-60"
                                >Berat (KG)</Label
                            >
                            <Input
                                type="number"
                                v-model="form.weight"
                                class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                            />
                        </div>
                        <div class="col-span-2 flex flex-col gap-2">
                            <Label
                                class="text-[10px] font-black uppercase opacity-60"
                                >Tanggal</Label
                            >
                            <DatePicker v-model="form.recorded_at" />
                        </div>
                        <button
                            type="submit"
                            class="col-span-2 rounded-2xl bg-accent py-5 text-[10px] font-black tracking-widest text-white uppercase shadow-xl hover:bg-accent/90"
                        >
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Modal: Edit Training Log (Coach) -->
            <div
                v-if="showEditLogModal"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-background/95 p-4 backdrop-blur-2xl"
            >
                <div
                    class="w-full max-w-2xl animate-in overflow-hidden rounded-[3rem] border border-white/10 bg-card shadow-[0_100px_150px_-50px_rgba(0,0,0,0.5)] duration-300 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-white/5 bg-muted/20 p-8"
                    >
                        <div>
                            <p
                                class="text-[10px] font-black tracking-widest text-accent uppercase"
                            >
                                Pelatih Monitoring
                            </p>
                            <h2
                                class="text-2xl font-black tracking-tight text-foreground uppercase"
                            >
                                Edit Log Latihan
                            </h2>
                        </div>
                        <button
                            @click="showEditLogModal = false"
                            class="rounded-full bg-white/5 p-3 text-muted-foreground transition-all hover:bg-white/10 hover:text-white"
                        >
                            <Activity class="h-6 w-6 rotate-45" />
                        </button>
                    </div>

                    <form
                        @submit.prevent="submitLogUpdate"
                        class="custom-scrollbar h-[70vh] overflow-y-auto p-10"
                    >
                        <div class="mb-8 space-y-2">
                            <Label
                                class="text-[10px] font-black uppercase opacity-60"
                                >Judul Latihan</Label
                            >
                            <Input
                                v-model="logForm.title"
                                class="h-14 rounded-2xl border-white/10 bg-white/5 px-6 font-black"
                                placeholder="Nama sesi latihan..."
                            />
                        </div>

                        <div class="mb-10 grid grid-cols-2 lg:grid-cols-4 gap-8">
                            <!-- Basic Metrics -->
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase opacity-60">Jarak (KM)</Label>
                                <Input type="number" step="0.01" v-model="logForm.distance_km" class="h-14 rounded-2xl border-white/10 bg-white/5 px-6 font-black" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase opacity-60">Durasi (Menit)</Label>
                                <Input type="number" v-model="logForm.duration_minutes" class="h-14 rounded-2xl border-white/10 bg-white/5 px-6 font-black" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase opacity-60">Avg RPM</Label>
                                <Input type="number" step="0.1" v-model="logForm.rpm" class="h-14 rounded-2xl border-white/10 bg-white/5 px-6 font-black" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase opacity-60">Intensitas</Label>
                                <CustomSelect v-model="logForm.intensity" :options="intensityOptions" />
                            </div>
                            <!-- Optional Metrics Grouping -->
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase opacity-60">Avg HR (BPM)</Label>
                                <Input type="number" v-model="logForm.avg_heart_rate" class="h-14 rounded-2xl border-white/10 bg-white/5 px-6 font-black" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase opacity-60">Calories (Kcal)</Label>
                                <Input type="number" v-model="logForm.calories" class="h-14 rounded-2xl border-white/10 bg-white/5 px-6 font-black" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase opacity-60">Elevasi (M)</Label>
                                <Input type="number" v-model="logForm.elevation_m" class="h-14 rounded-2xl border-white/10 bg-white/5 px-6 font-black" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase opacity-60">Suhu (°C)</Label>
                                <Input type="number" step="0.1" v-model="logForm.temperature_c" class="h-14 rounded-2xl border-white/10 bg-white/5 px-6 font-black" />
                            </div>
                        </div>

                        <!-- Generated Metrics (Read Only) -->
                        <div class="mb-10 space-y-4">
                            <Label class="text-[10px] font-black uppercase italic opacity-60">Hasil Auto-Generate (View Only)</Label>
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="flex flex-col gap-2 rounded-2xl border border-border bg-secondary p-4 text-center">
                                    <span class="text-[9px] font-black tracking-widest text-accent uppercase">Avg Speed</span>
                                    <h4 class="text-xl font-black tracking-tighter text-white italic">{{ selectedLog?.avg_speed || 0 }} <small class="text-[9px]">KPH</small></h4>
                                </div>
                                <div class="flex flex-col gap-2 rounded-2xl border border-border bg-secondary p-4 text-center">
                                    <span class="text-[9px] font-black tracking-widest text-blue-500 uppercase">Pace / KM</span>
                                    <h4 class="text-xl font-black tracking-tighter text-white italic">{{ selectedLog?.pace_per_km || '0:00' }}</h4>
                                </div>
                                <div class="flex flex-col gap-2 rounded-2xl border border-border bg-secondary p-4 text-center">
                                    <span class="text-[9px] font-black tracking-widest text-orange-500 uppercase">Calories</span>
                                    <h4 class="text-xl font-black tracking-tighter text-white italic">{{ selectedLog?.calories || 0 }} <small class="text-[9px]">Kcal</small></h4>
                                </div>
                                <div class="flex flex-col gap-2 rounded-2xl border border-border bg-secondary p-4 text-center">
                                    <span class="text-[9px] font-black tracking-widest text-purple-500 uppercase">HR Zone</span>
                                    <h4 class="text-[11px] mt-1 pr-1 font-black tracking-tighter text-white italic truncate" :title="selectedLog?.hr_zone || ''">{{ selectedLog?.hr_zone || '-' }}</h4>
                                </div>
                                <div class="flex flex-col gap-2 rounded-2xl border border-border bg-secondary p-4 text-center">
                                    <span class="text-[9px] font-black tracking-widest text-pink-500 uppercase">TRIMP Score</span>
                                    <h4 class="text-xl font-black tracking-tighter text-white italic">{{ selectedLog?.trimp || 0 }}</h4>
                                </div>
                                <div class="flex flex-col gap-2 rounded-2xl border border-border bg-secondary p-4 text-center">
                                    <span class="text-[9px] font-black tracking-widest text-cyan-500 uppercase">Est. VO2 Max</span>
                                    <h4 class="text-xl font-black tracking-tighter text-white italic">{{ selectedLog?.vo2_max || 0 }}</h4>
                                </div>
                                <div class="flex flex-col gap-2 rounded-2xl border border-border bg-secondary p-4 text-center">
                                    <span class="text-[9px] font-black tracking-widest text-yellow-500 uppercase">Avg Power</span>
                                    <h4 class="text-xl font-black tracking-tighter text-white italic">{{ selectedLog?.avg_watt_power || 0 }} <small class="text-[9px]">W</small></h4>
                                </div>
                                <div class="flex flex-col gap-2 rounded-2xl border border-border bg-secondary p-4 text-center">
                                    <span class="text-[9px] font-black tracking-widest text-emerald-500 uppercase">Avg Cadence</span>
                                    <h4 class="text-xl font-black tracking-tighter text-white italic">{{ selectedLog?.rpm || 0 }} <small class="text-[9px]">RPM</small></h4>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Rating -->
                        <div class="mb-10 grid grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <Label
                                    class="text-[10px] font-black uppercase opacity-60"
                                    >Kehadiran</Label
                                >
                                <CustomSelect
                                    v-model="logForm.attendance_status"
                                    :options="attendanceOptions"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-[10px] font-black uppercase opacity-60"
                                    >Penyelesaian</Label
                                >
                                <CustomSelect
                                    v-model="logForm.completion_status"
                                    :options="completionOptions"
                                />
                            </div>
                        </div>

                        <!-- Athlete Notes (Read Only) -->
                        <div class="mb-10 space-y-2">
                            <Label
                                class="text-[10px] font-black uppercase italic opacity-60"
                                >Catatan Atlet (View Only)</Label
                            >
                            <div
                                class="rounded-2xl border border-border bg-muted/40 p-4 text-xs font-medium text-muted-foreground italic"
                            >
                                {{
                                    selectedLog?.athlete_notes ||
                                    'Tidak ada catatan dari atlet.'
                                }}
                            </div>
                        </div>

                        <!-- Coach Feedback Section -->
                        <div
                            class="flex flex-col gap-8 rounded-3xl border border-accent/20 bg-accent/5 p-8"
                        >
                            <div class="space-y-2">
                                <Label
                                    class="text-[10px] font-black text-accent uppercase"
                                    >Rating Performa Pelatih (1-5)</Label
                                >
                                <Input
                                    type="number"
                                    min="1"
                                    max="5"
                                    v-model="logForm.coach_rating"
                                    class="h-14 rounded-2xl border-accent/20 bg-white/5 px-6 font-black text-accent"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-[10px] font-black uppercase opacity-60"
                                    >Evaluasi Pelatih</Label
                                >
                                <textarea
                                    v-model="logForm.coach_evaluation"
                                    rows="4"
                                    class="w-full rounded-2xl border-white/10 bg-white/5 p-4 text-sm font-medium outline-none focus:ring-2 focus:ring-accent"
                                    placeholder="Masukkan evaluasi detail performa..."
                                ></textarea>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-[10px] font-black uppercase opacity-60"
                                    >Komentar Tambahan</Label
                                >
                                <textarea
                                    v-model="logForm.coach_comments"
                                    rows="2"
                                    class="w-full rounded-2xl border-white/10 bg-white/5 p-4 text-sm font-medium outline-none focus:ring-2 focus:ring-accent"
                                    placeholder="Instruksi tambahan untuk sesi selanjutnya..."
                                ></textarea>
                            </div>
                        </div>

                        <div class="mt-10 flex gap-4">
                            <button
                                type="button"
                                @click="showEditLogModal = false"
                                class="flex-1 rounded-2xl border border-white/10 bg-white/5 py-5 text-[10px] font-black tracking-widest uppercase hover:bg-white/10"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="flex-[2] rounded-2xl bg-accent py-5 text-[10px] font-black tracking-widest text-white uppercase shadow-2xl shadow-accent/30 hover:bg-accent/90"
                            >
                                Simpan Log Latihan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.1);
}
</style>
