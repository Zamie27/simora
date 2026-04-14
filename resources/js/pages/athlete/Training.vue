<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import {
    Activity,
    Bike,
    Calendar,
    ChevronRight,
    Clock,
    Download,
    FileText,
    Gauge,
    History,
    Milestone,
    Plus,
    RotateCcw,
    Search,
    Star,
    TrendingUp,
    Upload,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import VueApexCharts from 'vue3-apexcharts';

import CustomSelect from '@/components/ui/CustomSelect.vue';
import DatePicker from '@/components/ui/DatePicker.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface Attachment {
    id: number;
    file_name: string;
    file_path: string;
    file_type: string;
}
interface Log {
    id: number;
    training_session_id?: number | null;
    title: string | null;
    date: string;
    distance_km: number | null;
    duration_minutes: number | null;
    avg_speed: number | null;
    rpm: number | null;
    avg_heart_rate: number | null;
    calories: number | null;
    elevation_m: number | null;
    temperature_c: number | null;
    pace_per_km: string | null;
    hr_zone: string | null;
    trimp: number | null;
    vo2_max: number | null;
    avg_watt_power: number | null;
    intensity: string | null;
    type: string | null;
    athlete_notes: string | null;
    attendance_status: string;
    completion_status: string;
    coach_rating: number | null;
    coach_evaluation: string | null;
    coach_comments: string | null;
    attachments: Attachment[];
    is_editable?: boolean;
    session?: {
        title: string;
        exercise_type: { name: string };
    };
}

interface Statistics {
    total_distance_km: number;
    total_duration_minutes: number;
    avg_speed: number;
    avg_rpm: number;
    total_sessions: number;
    completed_sessions: number;
}

interface ExerciseType {
    id: number;
    name: string;
}

const props = defineProps<{
    logs: Log[];
    statistics: Statistics;
    performanceTrend: any[];
    upcomingSessions: any[];
    exerciseTypes: ExerciseType[];
    filters: {
        start_date: string | null;
        end_date: string | null;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Latihan Saya', href: '/athlete/training' },
];

const showLogModal = ref(false);
const showDetailModal = ref(false);
const selectedSession = ref<any>(null);
const selectedLog = ref<Log | null>(null);

const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const form = useForm({
    id: null as number | null,
    training_session_id: null as number | null,
    title: '',
    date: new Date().toISOString().split('T')[0],
    distance_km: '' as string | number,
    duration_minutes: '' as string | number,
    avg_speed: '' as string | number,
    rpm: '' as string | number,
    avg_heart_rate: '' as string | number,
    calories: '' as string | number,
    elevation_m: '' as string | number,
    temperature_c: '' as string | number,
    intensity: 'medium',
    type: '',
    athlete_notes: '',
    completion_status: 'completed',
    attachments: [] as File[],
});

const isToday = (dateString: string) => {
    const today = new Date().toISOString().split('T')[0];

    return dateString === today;
};

const openLogModal = (session: any = null, log: Log | null = null) => {
    selectedSession.value = session;
    selectedLog.value = log;
    form.reset();

    if (log) {
        form.id = log.id;
        form.training_session_id = log.training_session_id || null;
        form.title = log.title || '';
        form.date = log.date;
        form.distance_km = log.distance_km || '';
        form.duration_minutes = log.duration_minutes || '';
        form.avg_speed = log.avg_speed || '';
        form.rpm = log.rpm || '';
        form.avg_heart_rate = log.avg_heart_rate || '';
        form.calories = log.calories || '';
        form.elevation_m = log.elevation_m || '';
        form.temperature_c = log.temperature_c || '';
        form.intensity = log.intensity || 'medium';
        form.type =
            log.type || (log.session ? log.session.exercise_type.name : '');
        form.athlete_notes = log.athlete_notes || '';
        form.completion_status = log.completion_status;
    } else if (session) {
        form.training_session_id = session.id;
        form.type = session.exercise_type.name;

        // Use current_log from instance if available
        const existingLog = session.current_log;

        if (existingLog) {
            form.id = existingLog.id;
            form.title = existingLog.title || '';
            form.date = existingLog.date;
            form.distance_km = existingLog.distance_km || '';
            form.duration_minutes = existingLog.duration_minutes || '';
            form.avg_speed = existingLog.avg_speed || '';
            form.rpm = existingLog.rpm || '';
            form.avg_heart_rate = existingLog.avg_heart_rate || '';
            form.calories = existingLog.calories || '';
            form.elevation_m = existingLog.elevation_m || '';
            form.temperature_c = existingLog.temperature_c || '';
            form.intensity = existingLog.intensity || 'medium';
            form.athlete_notes = existingLog.athlete_notes || '';
            form.completion_status = existingLog.completion_status;
        } else {
            form.distance_km = session.target_distance_km || '';
            form.duration_minutes = session.target_duration_minutes || '';
            form.avg_speed = session.target_avg_speed || '';
            form.rpm = session.target_rpm || '';
            form.avg_heart_rate = session.target_avg_heart_rate || '';
            form.calories = session.target_calories || '';
            form.elevation_m = session.target_elevation_m || '';
            form.temperature_c = session.target_temperature_c || '';
            // Force date to instance date for session logging
            form.date = session.instance_date;
        }
    }

    showLogModal.value = true;
};

const openDetailModal = (log: Log) => {
    selectedLog.value = log;
    showDetailModal.value = true;
};

const attachmentPreviews = ref<string[]>([]);

const handleFileChange = (e: Event) => {
    const files = (e.target as HTMLInputElement).files;

    if (files) {
        form.attachments = Array.from(files);

        // Generate previews
        attachmentPreviews.value = [];
        Array.from(files).forEach((file) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (e.target?.result) {
                        attachmentPreviews.value.push(
                            e.target.result as string,
                        );
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
};

const submitLog = () => {
    form.post('/athlete/training/log', {
        onSuccess: () => {
            showLogModal.value = false;
            form.reset();
            attachmentPreviews.value = [];
        },
    });
};

const deleteLog = (logId: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus log latihan ini?')) {
        router.delete(`/athlete/training/log/${logId}`);
    }
};

const applyFilters = () => {
    router.get(
        '/athlete/training',
        {
            start_date: startDate.value,
            end_date: endDate.value,
        },
        { preserveState: true },
    );
};

const formatDate = (date: string) => {
    const d = new Date(date);

    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const intensityLabel = (i: string) => {
    const labels: Record<string, string> = {
        low: 'Rendah',
        medium: 'Sedang',
        high: 'Tinggi',
        very_high: 'Sangat Tinggi',
    };

    return labels[i] || i;
};

const intensityColor = (i: string) => {
    const colors: Record<string, string> = {
        low: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
        medium: 'bg-green-500/10 text-green-500 border-green-500/20',
        high: 'bg-orange-500/10 text-orange-500 border-orange-500/20',
        very_high: 'bg-destructive/10 text-destructive border-destructive/20',
    };

    return colors[i] || 'bg-muted text-muted-foreground';
};

const isImage = (type: string) => type.startsWith('image/');

// Chart Options
const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        id: 'performance-trends',
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
            title: { text: 'Speed (kph)', style: { color: '#FF6120' } },
            labels: { style: { colors: '#FF6120' } },
        },
        {
            opposite: true,
            title: { text: 'Cadence (RPM)', style: { color: '#10B981' } },
            labels: { style: { colors: '#10B981' } },
        },
    ],
    grid: { borderColor: '#1e293b' },
    tooltip: { theme: 'dark' },
}));

const chartSeries = computed(() => [
    { name: 'Speed', data: props.performanceTrend.map((t) => t.avg_speed) },
    { name: 'RPM', data: props.performanceTrend.map((t) => t.rpm) },
]);
</script>

<template>
    <Head title="Latihan Saya" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <!-- Hero Header -->
            <div
                class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="rounded-xl bg-orange-500/10 p-3 text-orange-500"
                    >
                        <Bike class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-black tracking-tighter text-foreground uppercase md:text-4xl"
                        >
                            Latihan Saya
                        </h1>
                        <p
                            class="mt-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                        >
                            Performance Tracking & History
                        </p>
                    </div>
                </div>
                <button
                    @click="openLogModal()"
                    class="flex items-center gap-2 rounded-xl bg-accent px-8 py-4 text-xs font-black tracking-widest text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-95"
                >
                    <Plus class="h-4 w-4" /> Log Manual Activity
                </button>
            </div>

            <!-- Dashboard Stats & Trend -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Stats Grid (Left) -->
                <div class="grid grid-cols-1 gap-4 lg:col-span-4">
                    <div
                        class="flex flex-col justify-between rounded-3xl border border-border bg-card p-8 shadow-xl"
                    >
                        <div class="flex items-center gap-4">
                            <div class="rounded-2xl bg-accent p-3 text-white">
                                <Milestone class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-black uppercase"
                                >Total Jarak</span
                            >
                        </div>
                        <div class="mt-6 flex items-baseline gap-2">
                            <span
                                class="text-4xl font-black tracking-tighter text-foreground italic"
                                >{{ statistics.total_distance_km }}</span
                            >
                            <span
                                class="text-[10px] font-black text-accent uppercase italic"
                                >KM</span
                            >
                        </div>
                    </div>

                    <div
                        class="flex flex-col justify-between rounded-3xl border border-border bg-card p-8 shadow-xl"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-secondary p-3 text-accent"
                            >
                                <Gauge class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-black uppercase"
                                >Rata-Rata Kecepatan</span
                            >
                        </div>
                        <div class="mt-6 flex items-baseline gap-2">
                            <span
                                class="text-4xl font-black tracking-tighter text-foreground text-white italic"
                                >{{ statistics.avg_speed }}</span
                            >
                            <span
                                class="text-[10px] font-black text-accent uppercase italic"
                                >KM/JAM</span
                            >
                        </div>
                    </div>

                    <div
                        class="flex flex-col justify-between rounded-3xl border border-border bg-card p-8 shadow-xl"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-emerald-500/10 p-3 text-emerald-500"
                            >
                                <RotateCcw class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-black uppercase"
                                >Rata-Rata Kadens</span
                            >
                        </div>
                        <div class="mt-6 flex items-baseline gap-2">
                            <span
                                class="text-4xl font-black tracking-tighter text-foreground text-white italic"
                                >{{ statistics.avg_rpm }}</span
                            >
                            <span
                                class="text-[10px] font-black text-accent uppercase italic"
                                >RPM</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Trend Chart (Right) -->
                <div
                    class="rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl lg:col-span-8"
                >
                    <div class="mb-8 flex items-center gap-4">
                        <div
                            class="rounded-xl bg-orange-500/10 p-3 text-orange-500"
                        >
                            <TrendingUp class="h-5 w-5" />
                        </div>
                        <h3 class="text-xl font-black tracking-tight uppercase">
                            Tren Performa
                        </h3>
                    </div>
                    <div id="performance-chart">
                        <VueApexCharts
                            width="100%"
                            height="320"
                            type="line"
                            :options="chartOptions"
                            :series="chartSeries"
                        ></VueApexCharts>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
                <!-- Sidebar: Upcoming Missions -->
                <div class="flex flex-col gap-6 lg:col-span-4">
                    <div class="flex items-center gap-4">
                        <div class="rounded-xl bg-secondary p-3 text-accent">
                            <Calendar class="h-5 w-5" />
                        </div>
                        <h2 class="text-xl font-black uppercase">
                            Misi Mendatang
                        </h2>
                    </div>

                    <div
                        v-if="upcomingSessions.length === 0"
                        class="rounded-3xl border border-dashed border-border bg-muted/5 p-8 text-center opacity-40"
                    >
                        <p
                            class="text-[10px] font-bold text-muted-foreground uppercase"
                        >
                            Tidak ada jadwal sesi
                        </p>
                    </div>

                    <div v-else class="flex flex-col gap-4">
                        <div
                            v-for="session in upcomingSessions"
                            :key="session.id"
                            @click="
                                isToday(session.instance_date)
                                    ? openLogModal(session)
                                    : null
                            "
                            :class="[
                                isToday(session.instance_date)
                                    ? 'group cursor-pointer hover:border-accent hover:shadow-lg'
                                    : 'cursor-not-allowed opacity-60 contrast-75 grayscale-[0.5]',
                                'relative overflow-hidden rounded-2xl border border-border bg-card p-6 transition-all',
                            ]"
                        >
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="rounded bg-accent/10 px-2 py-0.5 text-[8px] font-black text-accent uppercase"
                                        >{{ session.exercise_type.name }}</span
                                    >
                                    <span
                                        v-if="session.current_log"
                                        class="rounded bg-emerald-500/10 px-2 py-0.5 text-[8px] font-black text-emerald-500 uppercase"
                                        >Sudah Dicatat</span
                                    >
                                    <span
                                        v-if="session.repeat_weekly"
                                        class="rounded bg-blue-500/10 px-2 py-0.5 text-[8px] font-black text-blue-500 uppercase"
                                        >Mingguan</span
                                    >
                                </div>
                                <span
                                    class="text-[10px] font-bold text-muted-foreground"
                                    >{{
                                        isToday(session.instance_date)
                                            ? 'Hari Ini'
                                            : formatDate(session.instance_date)
                                    }}</span
                                >
                            </div>
                            <h4
                                class="text-xs font-black text-foreground uppercase group-hover:text-accent"
                            >
                                {{ session.title }}
                            </h4>
                            <p
                                v-if="!isToday(session.instance_date)"
                                class="mt-1 text-[8px] font-bold text-muted-foreground uppercase opacity-60"
                            >
                                Sesi Belum Dibuka
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div
                                    class="flex items-center gap-2 text-[9px] font-black text-muted-foreground"
                                >
                                    <Clock class="h-3.5 w-3.5" />
                                    {{ session.scheduled_time || '00:00' }}
                                </div>
                                <ChevronRight
                                    v-if="isToday(session.instance_date)"
                                    class="h-4 w-4 text-muted-foreground group-hover:translate-x-1 group-hover:text-accent"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main: Performance Archive -->
                <div class="flex flex-col gap-8 lg:col-span-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-xl bg-secondary p-3 text-accent"
                            >
                                <History class="h-5 w-5" />
                            </div>
                            <h2 class="text-2xl font-black uppercase">
                                Riwayat Latihan
                            </h2>
                        </div>

                        <!-- History Filter -->
                        <div class="flex gap-2">
                            <DatePicker
                                v-model="startDate"
                                placeholder="Mulai"
                                class="w-32"
                            />
                            <DatePicker
                                v-model="endDate"
                                placeholder="Sampai"
                                class="w-32"
                            />
                            <button
                                @click="applyFilters"
                                class="rounded-lg bg-accent p-2 text-white"
                            >
                                <Search class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="logs.length === 0"
                        class="rounded-3xl border border-border bg-card p-20 text-center shadow-xl"
                    >
                        <Bike
                            class="mx-auto mb-4 h-12 w-12 font-thin text-muted-foreground opacity-10"
                        />
                        <p
                            class="text-xs font-black tracking-widest text-muted-foreground uppercase opacity-40"
                        >
                            Perjalananmu dimulai di sini.
                        </p>
                    </div>

                    <div v-else class="flex flex-col gap-6">
                        <div
                            v-for="log in logs"
                            :key="log.id"
                            class="group relative overflow-hidden rounded-3xl border border-border bg-card shadow-lg transition-all hover:shadow-2xl"
                        >
                            <!-- Action Trigger -->
                            <div
                                @click="openDetailModal(log)"
                                class="cursor-pointer"
                            >
                                <div class="p-8">
                                    <div class="flex flex-col gap-6">
                                        <!-- Header -->
                                        <div
                                            class="flex items-start justify-between border-b border-border pb-6"
                                        >
                                            <div>
                                                <h3
                                                    class="text-xl font-black tracking-tight text-foreground uppercase italic transition-colors group-hover:text-accent"
                                                >
                                                    {{
                                                        log.title ||
                                                        log.session?.title ||
                                                        log.type + ' Session'
                                                    }}
                                                </h3>
                                                <p
                                                    class="mt-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                                >
                                                    {{ formatDate(log.date) }} •
                                                    {{
                                                        log.session
                                                            ? 'Scheduled'
                                                            : 'Manual'
                                                    }}
                                                </p>
                                            </div>
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <!-- Action Buttons -->
                                                <div
                                                    class="mr-2 flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                                >
                                                    <button
                                                        v-if="log.is_editable"
                                                        @click.stop="
                                                            openLogModal(
                                                                null,
                                                                log,
                                                            )
                                                        "
                                                        class="rounded-lg bg-blue-500/10 p-2 text-blue-500 transition-colors hover:bg-blue-500/20"
                                                    >
                                                        <FileText
                                                            class="h-4 w-4"
                                                        />
                                                    </button>
                                                    <button
                                                        @click.stop="
                                                            deleteLog(log.id)
                                                        "
                                                        class="rounded-lg bg-destructive/10 p-2 text-destructive transition-colors hover:bg-destructive/20"
                                                    >
                                                        <X class="h-4 w-4" />
                                                    </button>
                                                </div>
                                                <div
                                                    v-if="log.coach_rating"
                                                    class="flex items-center gap-1 rounded-lg bg-accent/10 px-3 py-1.5 text-[10px] font-black text-accent"
                                                >
                                                    <Star
                                                        class="h-3 w-3 fill-accent"
                                                    />
                                                    {{ log.coach_rating }}
                                                </div>
                                                <div
                                                    :class="
                                                        intensityColor(
                                                            log.intensity ||
                                                                'medium',
                                                        )
                                                    "
                                                    class="rounded-lg border px-3 py-1.5 text-[10px] font-black uppercase"
                                                >
                                                    {{
                                                        intensityLabel(
                                                            log.intensity ||
                                                                'medium',
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Preview Metrics -->
                                        <div
                                            class="grid grid-cols-2 gap-4 md:grid-cols-4"
                                        >
                                            <div
                                                class="flex flex-col gap-1 rounded-2xl bg-muted/20 p-4 text-center"
                                            >
                                                <span
                                                    class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                                    >Jarak</span
                                                >
                                                <span class="text-lg font-black"
                                                    >{{ log.distance_km || 0 }}
                                                    <small
                                                        class="text-[8px] opacity-40"
                                                        >km</small
                                                    ></span
                                                >
                                            </div>
                                            <div
                                                class="flex flex-col gap-1 rounded-2xl bg-muted/20 p-4 text-center"
                                            >
                                                <span
                                                    class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                                    >Durasi</span
                                                >
                                                <span class="text-lg font-black"
                                                    >{{
                                                        log.duration_minutes ||
                                                        0
                                                    }}
                                                    <small
                                                        class="text-[8px] opacity-40"
                                                        >min</small
                                                    ></span
                                                >
                                            </div>
                                            <div
                                                class="flex flex-col gap-1 rounded-2xl bg-muted/20 p-4 text-center"
                                            >
                                                <span
                                                    class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                                    >Kecepatan</span
                                                >
                                                <span
                                                    class="text-lg font-black text-accent italic"
                                                    >{{ log.avg_speed || 0 }}
                                                    <small
                                                        class="text-[8px] opacity-70"
                                                        >kph</small
                                                    ></span
                                                >
                                            </div>
                                            <div
                                                class="flex flex-col gap-1 rounded-2xl bg-muted/20 p-4 text-center"
                                            >
                                                <span
                                                    class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                                    >RPM</span
                                                >
                                                <span class="text-lg font-black"
                                                    >{{ log.rpm || 0 }}
                                                    <small
                                                        class="text-[8px] opacity-40"
                                                        >rpm</small
                                                    ></span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: View Training Detail (Everything combined) -->
            <div
                v-if="showDetailModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/95 p-4 backdrop-blur-2xl"
            >
                <div
                    class="w-full max-w-4xl animate-in overflow-hidden rounded-[3rem] border border-white/10 bg-card shadow-2xl duration-300 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-white/5 bg-accent/5 p-8"
                    >
                        <div>
                            <p
                                class="text-[10px] font-black tracking-widest text-accent uppercase"
                            >
                                Detail Hasil Latihan
                            </p>
                            <h2
                                class="text-2xl font-black tracking-tighter text-foreground uppercase italic"
                            >
                                {{
                                    selectedLog?.title ||
                                    selectedLog?.session?.title ||
                                    selectedLog?.type + ' Session'
                                }}
                            </h2>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                v-if="selectedLog?.is_editable"
                                @click="
                                    showDetailModal = false;
                                    openLogModal(null, selectedLog);
                                "
                                class="flex items-center gap-2 rounded-xl bg-blue-500 px-4 py-2 text-[10px] font-black text-white uppercase transition-all hover:bg-blue-600"
                            >
                                Edit Log
                            </button>
                            <button
                                @click="showDetailModal = false"
                                class="rounded-full bg-white/5 p-3 text-muted-foreground transition-all hover:bg-white/10 hover:text-white"
                            >
                                <X class="h-6 w-6" />
                            </button>
                        </div>
                    </div>

                    <div
                        class="custom-scrollbar max-h-[80vh] overflow-y-auto p-10"
                    >
                        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                            <!-- Left Detail: Core Data -->
                            <div class="flex flex-col gap-8">
                                <div class="grid grid-cols-2 gap-4">
                                    <div
                                        class="flex flex-col gap-2 rounded-2xl border border-border bg-muted/10 p-6"
                                    >
                                        <div
                                            class="flex items-center gap-2 text-[10px] font-black text-muted-foreground uppercase opacity-60"
                                        >
                                            <Calendar class="h-3 w-3" /> Tanggal
                                        </div>
                                        <p class="text-lg font-black">
                                            {{
                                                selectedLog
                                                    ? formatDate(
                                                          selectedLog.date,
                                                      )
                                                    : ''
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex flex-col gap-2 rounded-2xl border border-border bg-muted/10 p-6"
                                    >
                                        <div
                                            class="flex items-center gap-2 text-[10px] font-black text-muted-foreground uppercase opacity-60"
                                        >
                                            <Activity class="h-3 w-3" />
                                            Intensitas
                                        </div>
                                        <p
                                            class="text-lg font-black text-accent uppercase"
                                        >
                                            {{
                                                intensityLabel(
                                                    selectedLog?.intensity ||
                                                        'medium',
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div
                                        class="flex flex-col gap-2 rounded-3xl border border-border bg-secondary p-4 text-center"
                                    >
                                        <span
                                            class="text-[9px] font-black tracking-widest text-accent uppercase"
                                            >Rata-Rata Kecepatan</span
                                        >
                                        <h4
                                            class="text-2xl font-black tracking-tighter text-white italic"
                                        >
                                            {{ selectedLog?.avg_speed || 0 }}
                                            <small class="text-[10px]"
                                                >KM/JAM</small
                                            >
                                        </h4>
                                    </div>
                                    <div
                                        class="flex flex-col gap-2 rounded-3xl border border-border bg-secondary p-4 text-center"
                                    >
                                        <span
                                            class="text-[9px] font-black tracking-widest text-emerald-500 uppercase"
                                            >Rata-Rata Kadens</span
                                        >
                                        <h4
                                            class="text-2xl font-black tracking-tighter text-white italic"
                                        >
                                            {{ selectedLog?.rpm || 0 }}
                                            <small class="text-[10px]"
                                                >RPM</small
                                            >
                                        </h4>
                                    </div>
                                    <div
                                        class="flex flex-col gap-2 rounded-3xl border border-border bg-secondary p-4 text-center"
                                    >
                                        <span
                                            class="text-[9px] font-black tracking-widest text-blue-500 uppercase"
                                            >Tempo / KM</span
                                        >
                                        <h4
                                            class="text-2xl font-black tracking-tighter text-white italic"
                                        >
                                            {{
                                                selectedLog?.pace_per_km ||
                                                '0:00'
                                            }}
                                        </h4>
                                    </div>
                                    <div
                                        class="flex flex-col gap-2 rounded-3xl border border-border bg-secondary p-4 text-center"
                                    >
                                        <span
                                            class="text-[9px] font-black tracking-widest text-orange-500 uppercase"
                                            >Kalori</span
                                        >
                                        <h4
                                            class="text-2xl font-black tracking-tighter text-white italic"
                                        >
                                            {{ selectedLog?.calories || 0 }}
                                            <small class="text-[10px]"
                                                >Kcal</small
                                            >
                                        </h4>
                                    </div>
                                    <div
                                        class="flex flex-col gap-2 rounded-3xl border border-border bg-secondary p-4 text-center"
                                    >
                                        <span
                                            class="text-[9px] font-black tracking-widest text-purple-500 uppercase"
                                            >Zona HR</span
                                        >
                                        <h4
                                            class="mt-1 text-sm font-black tracking-tighter text-white italic"
                                        >
                                            {{
                                                selectedLog?.hr_zone ||
                                                'Unknown'
                                            }}
                                        </h4>
                                    </div>
                                    <div
                                        class="flex flex-col gap-2 rounded-3xl border border-border bg-secondary p-4 text-center"
                                    >
                                        <span
                                            class="text-[9px] font-black tracking-widest text-pink-500 uppercase"
                                            >TRIMP Score</span
                                        >
                                        <h4
                                            class="text-2xl font-black tracking-tighter text-white italic"
                                        >
                                            {{ selectedLog?.trimp || 0 }}
                                        </h4>
                                    </div>
                                    <div
                                        class="flex flex-col gap-2 rounded-3xl border border-border bg-secondary p-4 text-center"
                                    >
                                        <span
                                            class="text-[9px] font-black tracking-widest text-cyan-500 uppercase"
                                            >Est. VO2 Max</span
                                        >
                                        <h4
                                            class="text-2xl font-black tracking-tighter text-white italic"
                                        >
                                            {{ selectedLog?.vo2_max || 0 }}
                                        </h4>
                                    </div>
                                    <div
                                        class="flex flex-col gap-2 rounded-3xl border border-border bg-secondary p-4 text-center"
                                    >
                                        <span
                                            class="text-[9px] font-black tracking-widest text-yellow-500 uppercase"
                                            >Rata-Rata Daya</span
                                        >
                                        <h4
                                            class="text-2xl font-black tracking-tighter text-white italic"
                                        >
                                            {{
                                                selectedLog?.avg_watt_power || 0
                                            }}
                                            <small class="text-[10px]">W</small>
                                        </h4>
                                    </div>
                                </div>

                                <div
                                    class="flex flex-col gap-4 rounded-3xl border border-border bg-card p-8"
                                >
                                    <h5
                                        class="flex items-center gap-2 text-[10px] font-black uppercase italic opacity-60"
                                    >
                                        <FileText class="h-3 w-3" />
                                        Personalisasi Catatan Atlet
                                    </h5>
                                    <p
                                        class="text-sm leading-relaxed font-medium text-muted-foreground italic"
                                    >
                                        "{{
                                            selectedLog?.athlete_notes ||
                                            'Tidak ada catatan personal untuk sesi ini.'
                                        }}"
                                    </p>
                                </div>
                            </div>

                            <!-- Right Detail: Coach Feedback & Attachments -->
                            <div class="flex flex-col gap-8">
                                <div
                                    class="flex flex-col gap-6 rounded-3xl border border-accent/20 bg-accent/5 p-8 shadow-[0_20px_40px_-5px_rgba(255,97,32,0.1)]"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <h5
                                            class="flex items-center gap-2 text-[10px] font-black text-accent uppercase italic"
                                        >
                                            <Star class="h-3 w-3 fill-accent" />
                                            Evaluasi & Rating Pelatih
                                        </h5>
                                        <div
                                            v-if="selectedLog?.coach_rating"
                                            class="flex gap-1"
                                        >
                                            <Star
                                                v-for="i in 5"
                                                :key="i"
                                                class="h-4 w-4"
                                                :class="
                                                    i <=
                                                    selectedLog.coach_rating
                                                        ? 'fill-accent text-accent'
                                                        : 'text-accent/20'
                                                "
                                            />
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="space-y-1">
                                            <p
                                                class="text-[10px] font-black text-muted-foreground uppercase opacity-40"
                                            >
                                                Pelatih Mengevaluasi:
                                            </p>
                                            <p
                                                class="text-sm leading-relaxed font-bold italic"
                                            >
                                                {{
                                                    selectedLog?.coach_evaluation ||
                                                    'Belum ada evaluasi dari pelatih.'
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            v-if="selectedLog?.coach_comments"
                                            class="mt-4 rounded-2xl border border-white/5 bg-white/5 p-4"
                                        >
                                            <p
                                                class="mb-1 text-[9px] font-black text-accent uppercase opacity-60"
                                            >
                                                Komentar Tambahan:
                                            </p>
                                            <p
                                                class="text-xs font-semibold text-muted-foreground italic"
                                            >
                                                {{ selectedLog.coach_comments }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-6">
                                    <h5
                                        class="flex items-center gap-2 text-[10px] font-black uppercase opacity-60"
                                    >
                                        <Search class="h-3 w-3" /> Bukti &
                                        Lampiran Unit
                                    </h5>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div
                                            v-for="att in selectedLog?.attachments"
                                            :key="att.id"
                                            class="group relative aspect-video overflow-hidden rounded-2xl border border-border bg-muted/30"
                                        >
                                            <img
                                                v-if="isImage(att.file_type)"
                                                :src="`/storage/${att.file_path}`"
                                                class="h-full w-full object-cover"
                                            />
                                            <div
                                                v-else
                                                class="flex h-full w-full flex-col items-center justify-center gap-2"
                                            >
                                                <FileText
                                                    class="h-8 w-8 text-muted-foreground opacity-20"
                                                />
                                                <span
                                                    class="text-[8px] font-black text-muted-foreground uppercase opacity-40"
                                                    >{{ att.file_name }}</span
                                                >
                                            </div>
                                            <a
                                                :href="`/storage/${att.file_path}`"
                                                target="_blank"
                                                class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-all group-hover:opacity-100"
                                            >
                                                <Download
                                                    class="h-6 w-6 text-white"
                                                />
                                            </a>
                                        </div>
                                        <div
                                            v-if="
                                                !selectedLog?.attachments.length
                                            "
                                            class="col-span-2 rounded-2xl border border-dashed border-border p-8 text-center opacity-30"
                                        >
                                            <p
                                                class="text-[8px] font-black tracking-widest uppercase"
                                            >
                                                Tidak ada bukti lampiran
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: Log Modal (Create/Update) -->
            <div
                v-if="showLogModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-3xl"
            >
                <div
                    class="w-full max-w-2xl animate-in overflow-hidden rounded-[3rem] border border-border bg-card shadow-2xl duration-500 slide-in-from-bottom-10 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-border bg-muted/20 p-8 md:p-10"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl border border-accent/20 bg-accent/10 text-accent"
                            >
                                <Zap class="h-6 w-6" />
                            </div>
                            <div>
                                <h2
                                    class="text-2xl font-black tracking-tighter text-foreground uppercase italic"
                                >
                                    {{
                                        form.id
                                            ? 'Update Record'
                                            : selectedSession
                                              ? 'Selesaikan Sesi'
                                              : 'Input Manual'
                                    }}
                                </h2>
                                <p
                                    class="mt-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                                >
                                    {{
                                        form.id
                                            ? 'Refine your performance data'
                                            : selectedSession
                                              ? 'Selesaikan misi jadwal Anda'
                                              : 'Dokumentasikan latihan mandiri Anda'
                                    }}
                                </p>
                            </div>
                        </div>
                        <button
                            @click="showLogModal = false"
                            class="rounded-full p-3 text-muted-foreground transition-all hover:bg-muted/50"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <div
                        class="custom-scrollbar max-h-[70vh] overflow-y-auto p-11"
                    >
                        <form
                            @submit.prevent="submitLog"
                            class="flex flex-col gap-10"
                        >
                            <div class="flex flex-col gap-6">
                                <p
                                    class="text-[10px] font-black tracking-[0.3em] text-accent uppercase opacity-80"
                                >
                                    Informasi Umum
                                </p>
                                <div
                                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                                >
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Judul Latihan</Label
                                        >
                                        <Input
                                            v-model="form.title"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                                            placeholder="E.g. Morning Sprint"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Tanggal</Label
                                        >
                                        <DatePicker
                                            v-model="form.date"
                                            :disabled="!!form.id"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-6">
                                <p
                                    class="text-[10px] font-black tracking-[0.3em] text-emerald-500 uppercase opacity-80"
                                >
                                    Data Latihan Utama (Wajib)
                                </p>
                                <div
                                    class="grid grid-cols-1 gap-6 md:grid-cols-3"
                                >
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Jarak (KM)</Label
                                        >
                                        <Input
                                            v-model="form.distance_km"
                                            type="number"
                                            step="0.1"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                                            placeholder="30.5"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Durasi / Waktu (Menit)</Label
                                        >
                                        <Input
                                            v-model="form.duration_minutes"
                                            type="number"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                                            placeholder="60"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Intensitas</Label
                                        >
                                        <CustomSelect
                                            v-model="form.intensity"
                                            :options="[
                                                {
                                                    value: 'low',
                                                    label: 'Rendah',
                                                },
                                                {
                                                    value: 'medium',
                                                    label: 'Sedang',
                                                },
                                                {
                                                    value: 'high',
                                                    label: 'Tinggi',
                                                },
                                                {
                                                    value: 'very_high',
                                                    label: 'Sangat Tinggi',
                                                },
                                            ]"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-6">
                                <p
                                    class="text-[10px] font-black tracking-[0.3em] text-blue-500 uppercase opacity-80"
                                >
                                    Data Pendukung (Opsional)
                                </p>
                                <div
                                    class="grid grid-cols-2 gap-6 md:grid-cols-4"
                                >
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Rata-Rata RPM</Label
                                        >
                                        <Input
                                            v-model="form.rpm"
                                            type="number"
                                            step="0.1"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                                            placeholder="90"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Rata-Rata Detak Jantung</Label
                                        >
                                        <Input
                                            v-model="form.avg_heart_rate"
                                            type="number"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                                            placeholder="150"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Elevasi (M)</Label
                                        >
                                        <Input
                                            v-model="form.elevation_m"
                                            type="number"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                                            placeholder="250"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Suhu (°C)</Label
                                        >
                                        <Input
                                            v-model="form.temperature_c"
                                            type="number"
                                            step="0.1"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                                            placeholder="28"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-6">
                                <p
                                    class="text-[10px] font-black tracking-[0.3em] text-muted-foreground uppercase opacity-80"
                                >
                                    Personal Notes & Attachments
                                </p>
                                <div class="flex flex-col gap-6">
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Athlete Notes</Label
                                        >
                                        <textarea
                                            v-model="form.athlete_notes"
                                            rows="4"
                                            class="w-full rounded-2xl border-none bg-muted/30 p-6 text-sm font-medium outline-none focus:ring-2 focus:ring-accent"
                                            placeholder="Bagaimana perasaan Anda hari ini? Adakah kendala teknis?"
                                        ></textarea>
                                    </div>

                                    <div class="flex flex-col gap-4">
                                        <Label
                                            class="text-[10px] font-black uppercase italic opacity-60"
                                            >Bukti Latihan (Foto
                                            Speedometer/Rute)</Label
                                        >
                                        <div
                                            class="relative flex min-h-[160px] cursor-pointer flex-col items-center justify-center gap-3 rounded-[2rem] border-2 border-dashed border-border bg-muted/5 transition-all hover:bg-muted/10"
                                        >
                                            <input
                                                type="file"
                                                multiple
                                                @change="handleFileChange"
                                                class="absolute inset-0 z-10 cursor-pointer opacity-0"
                                            />
                                            <Upload
                                                class="h-8 w-8 text-muted-foreground opacity-20"
                                            />
                                            <p
                                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                            >
                                                Ketuk untuk mengunggah file
                                            </p>
                                        </div>

                                        <div
                                            v-if="attachmentPreviews.length > 0"
                                            class="mt-4 grid grid-cols-4 gap-4"
                                        >
                                            <div
                                                v-for="(
                                                    preview, idx
                                                ) in attachmentPreviews"
                                                :key="idx"
                                                class="relative aspect-square overflow-hidden rounded-2xl border border-border shadow-md"
                                            >
                                                <img
                                                    :src="preview"
                                                    class="h-full w-full object-cover"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-[2rem] bg-accent py-6 text-xs font-black tracking-[0.3em] text-white uppercase shadow-2xl shadow-accent/40 transition-all hover:scale-[1.02] hover:bg-accent/90 active:scale-[0.98] disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Syncing...'
                                        : form.id
                                          ? 'Perbarui Data Performa'
                                          : 'Simpan Log Latihan'
                                }}
                            </button>
                        </form>
                    </div>
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
