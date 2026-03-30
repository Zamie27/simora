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
    intensity: 'medium',
    type: '',
    athlete_notes: '',
    completion_status: 'completed',
    attachments: [] as File[],
});

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
        form.intensity = log.intensity || 'medium';
        form.type = log.type || (log.session ? log.session.exercise_type.name : '');
        form.athlete_notes = log.athlete_notes || '';
        form.completion_status = log.completion_status;
    } else if (session) {
        form.training_session_id = session.id;
        form.type = session.exercise_type.name;
        
        const existingLog = session.logs && session.logs.length > 0 ? session.logs[0] : null;

        if (existingLog) {
            form.id = existingLog.id;
            form.title = existingLog.title || '';
            form.date = existingLog.date;
            form.distance_km = existingLog.distance_km || '';
            form.duration_minutes = existingLog.duration_minutes || '';
            form.avg_speed = existingLog.avg_speed || '';
            form.rpm = existingLog.rpm || '';
            form.intensity = existingLog.intensity || 'medium';
            form.athlete_notes = existingLog.athlete_notes || '';
            form.completion_status = existingLog.completion_status;
        } else {
            form.distance_km = session.target_distance_km || '';
            form.duration_minutes = session.target_duration_minutes || '';
            form.avg_speed = session.target_avg_speed || '';
            form.rpm = session.target_rpm || '';
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
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (e.target?.result) {
                        attachmentPreviews.value.push(e.target.result as string);
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
    router.get('/athlete/training', {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
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
        categories: props.performanceTrend.map(t => formatDate(t.date)),
        labels: { style: { colors: '#94a3b8' }, rotate: -45 },
    },
    yaxis: [
        { 
            title: { text: "Speed (kph)", style: { color: "#FF6120" } },
            labels: { style: { colors: '#FF6120' } } 
        },
        { 
            opposite: true, 
            title: { text: "Cadence (RPM)", style: { color: "#10B981" } },
            labels: { style: { colors: '#10B981' } } 
        }
    ],
    grid: { borderColor: '#1e293b' },
    tooltip: { theme: 'dark' },
}));

const chartSeries = computed(() => [
    { name: 'Speed', data: props.performanceTrend.map(t => t.avg_speed) },
    { name: 'RPM', data: props.performanceTrend.map(t => t.rpm) },
]);
</script>

<template>
    <Head title="Latihan Saya" />

    <AppLayout :breadcrumbs="breadcrumbs">
<<<<<<< HEAD
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <!-- Hero Stats -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
                <!-- Total KM -->
                <div
                    class="col-span-2 flex flex-col justify-between rounded-[2rem] border border-border bg-card p-8 shadow-xl shadow-black/5 md:col-span-2"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-accent text-white shadow-lg shadow-accent/20"
                        >
                            <Milestone class="h-6 w-6" />
                        </div>
                        <span
                            class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                            >Total Distance</span
                        >
                    </div>
                    <div class="mt-8 flex items-baseline gap-2">
                        <span
                            class="text-5xl font-black tracking-tighter text-foreground italic"
                            >{{ statistics.total_distance_km }}</span
                        >
                        <span
                            class="text-sm font-black text-accent uppercase italic"
                            >KM</span
                        >
                    </div>
                </div>

                <!-- Avg Speed -->
                <div
                    class="col-span-2 flex flex-col justify-between rounded-[2rem] border border-border bg-card p-8 shadow-xl shadow-black/5 md:col-span-2 lg:col-span-2"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-secondary text-accent"
                        >
                            <TrendingUp class="h-6 w-6" />
                        </div>
                        <span
                            class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                            >Avg Speed</span
                        >
                    </div>
                    <div class="mt-8 flex items-baseline gap-2">
                        <span
                            class="text-5xl font-black tracking-tighter text-foreground italic"
                            >{{ statistics.avg_speed }}</span
                        >
                        <span
                            class="text-sm font-black text-accent uppercase italic"
                            >KM/H</span
                        >
                    </div>
                </div>

                <!-- RPM -->
                <div
                    class="col-span-1 flex flex-col justify-between rounded-[2rem] border border-border bg-card p-8 shadow-xl shadow-black/5 lg:col-span-1"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-500"
                        >
                            <RotateCcw class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-2xl font-black text-foreground">
                            {{ statistics.avg_rpm }}
                        </p>
                        <p
                            class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                        >
                            Avg RPM
                        </p>
                    </div>
                </div>

                <!-- Completion -->
                <div
                    class="col-span-1 flex flex-col justify-between rounded-[2rem] border border-border bg-card p-8 shadow-xl shadow-black/5 lg:col-span-1"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-500/10 text-green-500"
                        >
                            <Star class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-2xl font-black text-foreground">
                            {{ statistics.completed_sessions }}/{{
                                statistics.total_sessions
                            }}
                        </p>
                        <p
                            class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                        >
                            Sessions
                        </p>
=======
        <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10">
            
            <!-- Hero Header -->
            <div class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-orange-500/10 p-3 text-orange-500"><Bike class="h-6 w-6" /></div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tighter text-foreground uppercase md:text-4xl">Latihan Saya</h1>
                        <p class="mt-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Performance Tracking & History</p>
                    </div>
                </div>
                <button @click="openLogModal()" class="flex items-center gap-2 rounded-xl bg-accent px-8 py-4 text-xs font-black tracking-widest text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-95">
                    <Plus class="h-4 w-4" /> Log Manual Activity
                </button>
            </div>

            <!-- Dashboard Stats & Trend -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Stats Grid (Left) -->
                <div class="lg:col-span-4 grid grid-cols-1 gap-4">
                    <div class="flex flex-col justify-between rounded-3xl border border-border bg-card p-8 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div class="rounded-2xl bg-accent p-3 text-white"><Milestone class="h-5 w-5" /></div>
                            <span class="text-[8px] font-black uppercase opacity-40">Total Distance</span>
                        </div>
                        <div class="mt-6 flex items-baseline gap-2">
                            <span class="text-4xl font-black italic text-foreground tracking-tighter">{{ statistics.total_distance_km }}</span>
                            <span class="text-[10px] font-black italic text-accent uppercase">KM</span>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between rounded-3xl border border-border bg-card p-8 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div class="rounded-2xl bg-secondary p-3 text-accent"><Gauge class="h-5 w-5" /></div>
                            <span class="text-[8px] font-black uppercase opacity-40">Avg Speed</span>
                        </div>
                        <div class="mt-6 flex items-baseline gap-2">
                            <span class="text-4xl font-black italic text-foreground tracking-tighter">{{ statistics.avg_speed }}</span>
                            <span class="text-[10px] font-black italic text-accent uppercase">KM/H</span>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between rounded-3xl border border-border bg-card p-8 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div class="rounded-2xl bg-emerald-500/10 p-3 text-emerald-500"><RotateCcw class="h-5 w-5" /></div>
                            <span class="text-[8px] font-black uppercase opacity-40">Avg Cadence</span>
                        </div>
                        <div class="mt-6 flex items-baseline gap-2">
                            <span class="text-4xl font-black italic text-foreground tracking-tighter">{{ statistics.avg_rpm }}</span>
                            <span class="text-[10px] font-black italic text-accent uppercase">RPM</span>
                        </div>
                    </div>
                </div>

                <!-- Trend Chart (Right) -->
                <div class="lg:col-span-8 rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl">
                    <div class="mb-8 flex items-center gap-4">
                        <div class="rounded-xl bg-orange-500/10 p-3 text-orange-500"><TrendingUp class="h-5 w-5" /></div>
                        <h3 class="text-xl font-black tracking-tight uppercase">Tren Performa</h3>
                    </div>
                    <div id="performance-chart">
                        <VueApexCharts width="100%" height="320" type="line" :options="chartOptions" :series="chartSeries"></VueApexCharts>
>>>>>>> 023a5e4 (feat: implement training log management, performance trend visualization, and date filtering in athlete detail view (Update besar SRS-F dari tujuan utama sistem))
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
<<<<<<< HEAD
                <!-- Left: Upcoming & Quick Action -->
                <div class="flex flex-col gap-8 lg:col-span-4">
                    <div class="flex flex-col gap-4">
                        <h2
                            class="flex items-center gap-2 text-xs font-black tracking-[0.3em] text-muted-foreground uppercase opacity-60"
                        >
                            <Calendar class="h-3 w-3" /> Upcoming Missions
                        </h2>

                        <div
                            v-if="upcomingSessions.length === 0"
                            class="rounded-3xl border border-dashed border-border bg-muted/5 p-8 text-center"
                        >
                            <p
                                class="text-[10px] font-bold text-muted-foreground uppercase opacity-50"
                            >
                                No scheduled sessions
                            </p>
                        </div>

                        <div v-else class="flex flex-col gap-3">
                            <div
                                v-for="session in upcomingSessions"
                                :key="session.id"
                                @click="openLogModal(session)"
                                class="group cursor-pointer rounded-2xl border border-border bg-card p-5 transition-all hover:border-accent hover:shadow-lg"
                            >
                                <div
                                    class="mb-3 flex items-center justify-between"
                                >
                                    <span
                                        class="rounded bg-accent/10 px-2 py-0.5 text-[8px] font-black text-accent uppercase"
                                        >{{ session.exercise_type.name }}</span
                                    >
                                    <span
                                        class="text-[10px] font-bold text-muted-foreground"
                                        >{{
                                            formatDate(session.scheduled_date)
                                        }}</span
                                    >
                                </div>
                                <h4
                                    class="text-xs font-black text-foreground uppercase group-hover:text-accent"
                                >
                                    {{ session.title }}
                                </h4>
                                <div
                                    class="mt-3 flex items-center justify-between"
                                >
                                    <div
                                        class="flex items-center gap-2 text-[9px] font-bold text-muted-foreground"
                                    >
                                        <Clock class="h-3 w-3" />
                                        {{ session.scheduled_time || '00:00' }}
                                    </div>
                                    <ChevronRight
                                        class="h-3 w-3 text-muted-foreground group-hover:translate-x-1 group-hover:text-accent"
                                    />
=======
                <!-- Sidebar: Upcoming Missions -->
                <div class="lg:col-span-4 flex flex-col gap-6">
                    <div class="flex items-center gap-4">
                         <div class="rounded-xl bg-secondary p-3 text-accent"><Calendar class="h-5 w-5" /></div>
                        <h2 class="text-xl font-black uppercase">Upcoming Missions</h2>
                    </div>
                    
                    <div v-if="upcomingSessions.length === 0" class="rounded-3xl border border-dashed border-border p-8 text-center bg-muted/5 opacity-40">
                        <p class="text-[10px] font-bold text-muted-foreground uppercase">No scheduled sessions</p>
                    </div>

                    <div v-else class="flex flex-col gap-4">
                        <div v-for="session in upcomingSessions" :key="session.id" 
                            @click="openLogModal(session)"
                            class="group cursor-pointer rounded-2xl border border-border bg-card p-6 transition-all hover:border-accent hover:shadow-lg">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-accent/10 px-2 py-0.5 text-[8px] font-black text-accent uppercase">{{ session.exercise_type.name }}</span>
                                    <span v-if="session.logs && session.logs.length > 0" class="rounded bg-emerald-500/10 px-2 py-0.5 text-[8px] font-black text-emerald-500 uppercase">Sudah Dicatat</span>
                                </div>
                                <span class="text-[10px] font-bold text-muted-foreground">{{ formatDate(session.scheduled_date) }}</span>
                            </div>
                            <h4 class="text-xs font-black uppercase text-foreground group-hover:text-accent">{{ session.title }}</h4>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-[9px] font-black text-muted-foreground">
                                    <Clock class="h-3.5 w-3.5" /> {{ session.scheduled_time || '00:00' }}
>>>>>>> 023a5e4 (feat: implement training log management, performance trend visualization, and date filtering in athlete detail view (Update besar SRS-F dari tujuan utama sistem))
                                </div>
                                <ChevronRight class="h-4 w-4 text-muted-foreground group-hover:translate-x-1 group-hover:text-accent" />
                            </div>
                        </div>
<<<<<<< HEAD

                        <button
                            @click="openLogModal()"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-secondary py-5 text-[10px] font-black tracking-widest text-accent uppercase shadow-lg shadow-secondary/10 hover:bg-secondary/80"
                        >
                            <Plus class="h-4 w-4" /> Log Manual Activity
                        </button>
                    </div>
                </div>

                <!-- Right: High Fidelity History -->
                <div class="flex flex-col gap-8 lg:col-span-8">
                    <div class="flex items-center justify-between">
                        <h2
                            class="flex items-center gap-2 text-xs font-black tracking-[0.3em] text-muted-foreground uppercase opacity-60"
                        >
                            <History class="h-3 w-3" /> Performance Archive
                        </h2>
                    </div>

                    <div
                        v-if="logs.length === 0"
                        class="rounded-3xl border border-border bg-card p-20 text-center shadow-xl"
                    >
                        <Bike
                            class="mx-auto mb-4 h-12 w-12 text-muted-foreground opacity-10"
                        />
                        <p
                            class="text-xs font-black tracking-[0.2em] text-muted-foreground uppercase italic opacity-40"
                        >
                            Your journey begins here.
                        </p>
                    </div>

                    <div v-else class="flex flex-col gap-10">
                        <div
                            v-for="log in logs"
                            :key="log.id"
                            class="group relative overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-2xl shadow-black/5 transition-all hover:shadow-black/10"
                        >
                            <!-- Instagrammable Header (Media) -->
                            <div
                                v-if="
                                    log.attachments.some((a) =>
                                        isImage(a.file_type),
                                    )
                                "
                                class="relative h-64 w-full overflow-hidden"
                            >
                                <img
                                    :src="`/storage/${log.attachments.find((a) => isImage(a.file_type))?.file_path}`"
                                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                />
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-card via-card/20 to-transparent"
                                ></div>
                                <div class="absolute bottom-6 left-8">
                                    <span
                                        class="rounded-lg bg-accent px-3 py-1.5 text-[9px] font-black text-white uppercase shadow-lg"
                                        >{{
                                            log.session?.exercise_type.name ||
                                            log.type
                                        }}</span
                                    >
=======
                    </div>
                </div>

                <!-- Main: Performance Archive -->
                <div class="lg:col-span-8 flex flex-col gap-8">
                     <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="rounded-xl bg-secondary p-3 text-accent"><History class="h-5 w-5" /></div>
                            <h2 class="text-2xl font-black uppercase">Riwayat Latihan</h2>
                        </div>
                        
                        <!-- History Filter -->
                        <div class="flex gap-2">
                            <DatePicker v-model="startDate" placeholder="Mulai" class="w-32" />
                            <DatePicker v-model="endDate" placeholder="Sampai" class="w-32" />
                            <button @click="applyFilters" class="rounded-lg bg-accent p-2 text-white"><Search class="h-4 w-4" /></button>
                        </div>
                    </div>

                    <div v-if="logs.length === 0" class="rounded-3xl border border-border bg-card p-20 text-center shadow-xl">
                        <Bike class="mx-auto mb-4 h-12 w-12 text-muted-foreground opacity-10 font-thin" />
                        <p class="text-xs font-black tracking-widest text-muted-foreground uppercase opacity-40">Your journey begins here.</p>
                    </div>

                    <div v-else class="flex flex-col gap-6">
                        <div v-for="log in logs" :key="log.id" class="group relative overflow-hidden rounded-3xl border border-border bg-card shadow-lg transition-all hover:shadow-2xl">
                            <!-- Action Trigger -->
                             <div @click="openDetailModal(log)" class="cursor-pointer">
                                <div class="p-8">
                                    <div class="flex flex-col gap-6">
                                        <!-- Header -->
                                        <div class="flex items-start justify-between border-b border-border pb-6">
                                            <div>
                                                <h3 class="text-xl font-black italic text-foreground uppercase tracking-tight group-hover:text-accent transition-colors">
                                                    {{ log.title || log.session?.title || log.type + ' Session' }}
                                                </h3>
                                                <p class="mt-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60">
                                                    {{ formatDate(log.date) }} • {{ log.session ? 'Scheduled' : 'Manual' }}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <!-- Action Buttons -->
                                                <div class="flex items-center gap-2 mr-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <button v-if="log.is_editable" @click.stop="openLogModal(null, log)" class="p-2 rounded-lg bg-blue-500/10 text-blue-500 hover:bg-blue-500/20 transition-colors">
                                                        <FileText class="h-4 w-4" />
                                                    </button>
                                                    <button @click.stop="deleteLog(log.id)" class="p-2 rounded-lg bg-destructive/10 text-destructive hover:bg-destructive/20 transition-colors">
                                                        <X class="h-4 w-4" />
                                                    </button>
                                                </div>
                                                <div v-if="log.coach_rating" class="flex items-center gap-1 rounded-lg bg-accent/10 px-3 py-1.5 text-[10px] font-black text-accent">
                                                    <Star class="h-3 w-3 fill-accent" /> {{ log.coach_rating }}
                                                </div>
                                                <div :class="intensityColor(log.intensity || 'medium')" class="rounded-lg border px-3 py-1.5 text-[x-small] font-black uppercase">
                                                    {{ intensityLabel(log.intensity || 'medium') }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Preview Metrics -->
                                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                                            <div class="flex flex-col gap-1 rounded-2xl bg-muted/20 p-4 text-center">
                                                <span class="text-[8px] font-black text-muted-foreground uppercase opacity-40 tracking-widest uppercase">Distance</span>
                                                <span class="text-lg font-black">{{ log.distance_km || 0 }} <small class="text-[8px] opacity-40">km</small></span>
                                            </div>
                                            <div class="flex flex-col gap-1 rounded-2xl bg-muted/20 p-4 text-center">
                                                <span class="text-[8px] font-black text-muted-foreground uppercase opacity-40 tracking-widest uppercase">Duration</span>
                                                <span class="text-lg font-black">{{ log.duration_minutes || 0 }} <small class="text-[8px] opacity-40">min</small></span>
                                            </div>
                                            <div class="flex flex-col gap-1 rounded-2xl bg-muted/20 p-4 text-center">
                                                <span class="text-[8px] font-black text-muted-foreground uppercase opacity-40 tracking-widest uppercase">Speed</span>
                                                <span class="text-lg font-black text-accent italic">{{ log.avg_speed || 0 }} <small class="text-[8px] opacity-70">kph</small></span>
                                            </div>
                                            <div class="flex flex-col gap-1 rounded-2xl bg-muted/20 p-4 text-center">
                                                <span class="text-[8px] font-black text-muted-foreground uppercase opacity-40 tracking-widest uppercase">RPM</span>
                                                <span class="text-lg font-black">{{ log.rpm || 0 }} <small class="text-[8px] opacity-40">rpm</small></span>
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
            <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-background/95 p-4 backdrop-blur-2xl">
                <div class="w-full max-w-4xl animate-in fade-in zoom-in duration-300 rounded-[3rem] border border-white/10 bg-card shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between border-b border-white/5 bg-accent/5 p-8">
                        <div>
                            <p class="text-[10px] font-black tracking-widest text-accent uppercase">Training Results Detail</p>
                            <h2 class="text-2xl font-black tracking-tighter text-foreground uppercase italic">{{ selectedLog?.title || selectedLog?.session?.title || selectedLog?.type + ' Session' }}</h2>
                        </div>
                        <div class="flex items-center gap-3">
                            <button v-if="selectedLog?.is_editable" @click="showDetailModal = false; openLogModal(null, selectedLog)" class="flex items-center gap-2 rounded-xl bg-blue-500 px-4 py-2 text-[10px] font-black text-white uppercase hover:bg-blue-600 transition-all">
                                Edit Log
                            </button>
                            <button @click="showDetailModal = false" class="rounded-full bg-white/5 p-3 text-muted-foreground hover:bg-white/10 hover:text-white transition-all">
                                <X class="h-6 w-6" />
                            </button>
                        </div>
                    </div>
                    
                    <div class="max-h-[80vh] overflow-y-auto p-10">
                        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                            <!-- Left Detail: Core Data -->
                            <div class="flex flex-col gap-8">
                                <div class="grid grid-cols-2 gap-4">
                                     <div class="flex flex-col gap-2 rounded-2xl border border-border bg-muted/10 p-6">
                                        <div class="flex items-center gap-2 text-[10px] font-black text-muted-foreground uppercase opacity-60">
                                            <Calendar class="h-3 w-3" /> Tanggal
                                        </div>
                                        <p class="text-lg font-black">{{ selectedLog ? formatDate(selectedLog.date) : '' }}</p>
                                    </div>
                                    <div class="flex flex-col gap-2 rounded-2xl border border-border bg-muted/10 p-6">
                                        <div class="flex items-center gap-2 text-[10px] font-black text-muted-foreground uppercase opacity-60">
                                            <Activity class="h-3 w-3" /> Intensitas
                                        </div>
                                        <p class="text-lg font-black uppercase text-accent">{{ intensityLabel(selectedLog?.intensity || 'medium') }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-2 rounded-3xl bg-secondary p-8 text-center border border-border">
                                        <span class="text-[9px] font-black text-accent uppercase tracking-widest">Average Speed</span>
                                        <h4 class="text-4xl font-black italic tracking-tighter text-white">{{ selectedLog?.avg_speed || 0 }} <small class="text-xs">KPH</small></h4>
                                    </div>
                                    <div class="flex flex-col gap-2 rounded-3xl bg-secondary p-8 text-center border border-border">
                                        <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Average Cadence</span>
                                        <h4 class="text-4xl font-black italic tracking-tighter text-white">{{ selectedLog?.rpm || 0 }} <small class="text-xs">RPM</small></h4>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-4 rounded-3xl border border-border bg-card p-8">
                                    <h5 class="text-[10px] font-black uppercase opacity-60 flex items-center gap-2 italic">
                                        <FileText class="h-3 w-3" /> Personalisasi Catatan Atlet
                                    </h5>
                                    <p class="text-sm font-medium italic text-muted-foreground leading-relaxed">
                                        "{{ selectedLog?.athlete_notes || 'Tidak ada catatan personal untuk sesi ini.' }}"
                                    </p>
>>>>>>> 023a5e4 (feat: implement training log management, performance trend visualization, and date filtering in athlete detail view (Update besar SRS-F dari tujuan utama sistem))
                                </div>
                            </div>

                            <!-- Right Detail: Coach Feedback & Attachments -->
                            <div class="flex flex-col gap-8">
                                <div class="flex flex-col gap-6 rounded-3xl bg-accent/5 border border-accent/20 p-8 shadow-[0_20px_40px_-5px_rgba(255,97,32,0.1)]">
                                     <div class="flex items-center justify-between">
                                        <h5 class="text-[10px] font-black uppercase text-accent flex items-center gap-2 italic">
                                            <Star class="h-3 w-3 fill-accent" /> Evaluasi & Rating Pelatih
                                        </h5>
                                        <div v-if="selectedLog?.coach_rating" class="flex gap-1">
                                            <Star v-for="i in 5" :key="i" class="h-4 w-4" :class="i <= (selectedLog.coach_rating as number) ? 'fill-accent text-accent' : 'text-accent/20'" />
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-black text-muted-foreground uppercase opacity-40">Pelatih Mengevaluasi:</p>
                                            <p class="text-sm font-bold italic leading-relaxed">{{ selectedLog?.coach_evaluation || 'Belum ada evaluasi dari pelatih.' }}</p>
                                        </div>
                                        <div v-if="selectedLog?.coach_comments" class="rounded-2xl bg-white/5 p-4 border border-white/5 mt-4">
                                            <p class="text-[9px] font-black text-accent uppercase opacity-60 mb-1">Komentar Tambahan:</p>
                                            <p class="text-xs font-semibold text-muted-foreground italic">{{ selectedLog.coach_comments }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-6">
<<<<<<< HEAD
                                    <!-- Title & Date -->
                                    <div
                                        class="flex items-start justify-between border-b border-border pb-6"
                                    >
                                        <div>
                                            <h3
                                                class="text-2xl font-black tracking-tighter text-foreground uppercase italic md:text-3xl"
                                            >
                                                {{
                                                    log.session?.title ||
                                                    log.type + ' Session'
                                                }}
                                            </h3>
                                            <p
                                                class="mt-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                                            >
                                                {{ formatDate(log.date) }} •
                                                {{
                                                    log.athlete_notes
                                                        ? 'Activity Shared'
                                                        : 'Data Logged'
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            :class="
                                                intensityColor(
                                                    log.intensity || 'medium',
                                                )
                                            "
                                            class="rounded-xl border px-4 py-2 text-[9px] font-black uppercase"
                                        >
                                            {{
                                                intensityLabel(
                                                    log.intensity || 'medium',
                                                )
                                            }}
                                        </div>
                                    </div>

                                    <!-- Core Metrics Grid -->
                                    <div
                                        class="grid grid-cols-2 gap-4 md:grid-cols-4"
                                    >
                                        <div
                                            class="flex flex-col gap-1 rounded-2xl bg-muted/30 p-5 text-center"
                                        >
                                            <span
                                                class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                                >Distance</span
                                            >
                                            <span
                                                class="text-xl font-black text-foreground"
                                                >{{ log.distance_km }}
                                                <small
                                                    class="text-[9px] uppercase opacity-50"
                                                    >km</small
                                                ></span
                                            >
                                        </div>
                                        <div
                                            class="flex flex-col gap-1 rounded-2xl bg-muted/30 p-5 text-center"
                                        >
                                            <span
                                                class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                                >Duration</span
                                            >
                                            <span
                                                class="text-xl font-black text-foreground"
                                                >{{ log.duration_minutes }}
                                                <small
                                                    class="text-[9px] uppercase opacity-50"
                                                    >min</small
                                                ></span
                                            >
                                        </div>
                                        <div
                                            class="flex flex-col gap-1 rounded-2xl border border-accent/10 bg-muted/30 p-5 text-center"
                                        >
                                            <span
                                                class="text-[8px] font-black tracking-widest text-accent uppercase opacity-60"
                                                >Speed</span
                                            >
                                            <span
                                                class="text-xl font-black text-accent italic"
                                                >{{ log.avg_speed }}
                                                <small
                                                    class="text-[9px] uppercase opacity-70"
                                                    >kph</small
                                                ></span
                                            >
                                        </div>
                                        <div
                                            class="flex flex-col gap-1 rounded-2xl bg-muted/30 p-5 text-center"
                                        >
                                            <span
                                                class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                                >Cadence</span
                                            >
                                            <span
                                                class="text-xl font-black text-foreground"
                                                >{{ log.rpm }}
                                                <small
                                                    class="text-[9px] uppercase opacity-50"
                                                    >rpm</small
                                                ></span
                                            >
                                        </div>
                                    </div>

                                    <!-- Notes & Evaluation -->
                                    <div
                                        v-if="
                                            log.athlete_notes ||
                                            log.coach_evaluation
                                        "
                                        class="grid grid-cols-1 gap-6 md:grid-cols-2"
                                    >
                                        <div
                                            v-if="log.athlete_notes"
                                            class="flex flex-col gap-2"
                                        >
                                            <span
                                                class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                                >Athlete's Note</span
                                            >
                                            <p
                                                class="text-xs leading-relaxed font-semibold text-muted-foreground italic"
                                            >
                                                "{{ log.athlete_notes }}"
                                            </p>
                                        </div>
                                        <div
                                            v-if="log.coach_evaluation"
                                            class="flex flex-col gap-2 border-l border-border pl-6"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <span
                                                    class="text-[8px] font-black tracking-widest text-accent uppercase opacity-60"
                                                    >Coach Evaluation</span
                                                >
                                                <div
                                                    v-if="log.coach_rating"
                                                    class="flex items-center gap-1 text-[10px] font-black text-accent"
                                                >
                                                    <Star
                                                        class="h-3 w-3 fill-accent"
                                                    />
                                                    {{ log.coach_rating }}
                                                </div>
                                            </div>
                                            <p
                                                class="text-xs leading-relaxed font-bold text-foreground italic"
                                            >
                                                {{ log.coach_evaluation }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Footer (Other attachments) -->
                                    <div
                                        v-if="log.attachments.length > 0"
                                        class="flex flex-wrap gap-2 border-t border-border pt-6"
                                    >
                                        <a
                                            v-for="att in log.attachments"
                                            :key="att.id"
                                            :href="`/storage/${att.file_path}`"
                                            target="_blank"
                                            class="flex items-center gap-2 rounded-lg bg-muted/50 px-3 py-1.5 text-[8px] font-black text-muted-foreground uppercase transition-all hover:bg-accent hover:text-white"
                                        >
                                            <FileText class="h-3 w-3" />
                                            {{ att.file_name }}
                                        </a>
=======
                                    <h5 class="text-[10px] font-black uppercase opacity-60 flex items-center gap-2">
                                        <Search class="h-3 w-3" /> Bukti & Lampiran Unit
                                    </h5>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div v-for="att in selectedLog?.attachments" :key="att.id" class="group relative aspect-video rounded-2xl bg-muted/30 overflow-hidden border border-border">
                                            <img v-if="isImage(att.file_type)" :src="`/storage/${att.file_path}`" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center flex-col gap-2">
                                                <FileText class="h-8 w-8 text-muted-foreground opacity-20" />
                                                <span class="text-[8px] text-muted-foreground font-black uppercase opacity-40">{{ att.file_name }}</span>
                                            </div>
                                            <a :href="`/storage/${att.file_path}`" target="_blank" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all">
                                                <Download class="h-6 w-6 text-white" />
                                            </a>
                                        </div>
                                        <div v-if="!selectedLog?.attachments.length" class="col-span-2 rounded-2xl border border-dashed border-border p-8 text-center opacity-30">
                                            <p class="text-[8px] font-black uppercase tracking-widest">No Attachments Provided</p>
                                        </div>
>>>>>>> 023a5e4 (feat: implement training log management, performance trend visualization, and date filtering in athlete detail view (Update besar SRS-F dari tujuan utama sistem))
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<<<<<<< HEAD
            <!-- Log Modal -->
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
=======
            <!-- Modal: Log Modal (Create/Update) -->
            <div v-if="showLogModal" class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-3xl">
                <div class="w-full max-w-2xl animate-in overflow-hidden rounded-[3rem] border border-border bg-card shadow-2xl duration-500 fade-in zoom-in slide-in-from-bottom-10">
                    <div class="flex items-center justify-between border-b border-border bg-muted/20 p-8 md:p-10">
>>>>>>> 023a5e4 (feat: implement training log management, performance trend visualization, and date filtering in athlete detail view (Update besar SRS-F dari tujuan utama sistem))
                        <div>
                            <h2
                                class="text-2xl font-black tracking-tighter text-foreground uppercase italic"
                            >
                                {{
                                    selectedSession
                                        ? 'Finish Session'
                                        : 'Manual Entry'
                                }}
                            </h2>
                            <p
                                class="mt-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                            >
                                {{
                                    selectedSession
                                        ? 'Complete your scheduled mission'
                                        : 'Document your independent ride'
                                }}
                            </p>
                        </div>
                        <button
                            @click="showLogModal = false"
                            class="rounded-full p-3 text-muted-foreground transition-all hover:bg-muted/50"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>

<<<<<<< HEAD
                    <div class="max-h-[70vh] overflow-y-auto p-8 md:p-11">
                        <form
                            @submit.prevent="submitLog"
                            class="flex flex-col gap-10"
                        >
                            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label
                                        class="text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase italic opacity-50"
                                        >Date of Activity</Label
                                    >
                                    <DatePicker v-model="form.date" />
=======
                    <div class="max-h-[70vh] overflow-y-auto p-11 custom-scrollbar">
                        <form @submit.prevent="submitLog" class="flex flex-col gap-10">
                            
                             <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                                <div class="space-y-3 md:col-span-1">
                                    <Label class="text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50 italic">Date of Activity</Label>
                                    <DatePicker v-model="form.date" :disabled="!!form.id" />
>>>>>>> 023a5e4 (feat: implement training log management, performance trend visualization, and date filtering in athlete detail view (Update besar SRS-F dari tujuan utama sistem))
                                </div>
                                <div class="space-y-3 md:col-span-2">
                                    <Label class="text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50 italic">Title / Name of Activity</Label>
                                    <Input v-model="form.title" class="h-12 rounded-xl border-none bg-muted/40 px-6 font-bold" placeholder="e.g. Morning Ride to Cibubur" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                <div class="space-y-3">
                                    <Label
                                        class="text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase italic opacity-50"
                                        >Type of Activity</Label
                                    >
                                    <CustomSelect
                                        v-model="form.type"
                                        :options="
                                            exerciseTypes.map((t) => ({
                                                value: t.name,
                                                label: t.name,
                                            }))
                                        "
                                    />
                                </div>
                                <div class="space-y-3 opacity-50 pointer-events-none">
                                     <Label class="text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50 italic">Input Status</Label>
                                     <div class="h-12 flex items-center px-4 rounded-xl bg-muted/20 text-[10px] font-bold uppercase">{{ form.id ? 'Updating Record' : 'New Performance Log' }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                                <div class="space-y-2">
                                    <Label
                                        class="text-[9px] font-black text-muted-foreground uppercase"
                                        >Distance (KM)</Label
                                    >
                                    <Input
                                        v-model="form.distance_km"
                                        type="number"
                                        step="0.01"
                                        class="h-14 rounded-2xl border-none bg-muted/40 px-6 text-lg font-black focus:ring-accent"
                                        placeholder="0.0"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="text-[9px] font-black text-muted-foreground uppercase"
                                        >Duration (Min)</Label
                                    >
                                    <Input
                                        v-model="form.duration_minutes"
                                        type="number"
                                        class="h-14 rounded-2xl border-none bg-muted/40 px-6 text-lg font-black focus:ring-accent"
                                        placeholder="0"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="text-[9px] font-black text-muted-foreground uppercase"
                                        >Avg Speed</Label
                                    >
                                    <Input
                                        v-model="form.avg_speed"
                                        type="number"
                                        step="0.1"
                                        class="h-14 rounded-2xl border-none bg-muted/40 px-6 text-lg font-black focus:ring-accent"
                                        placeholder="0.0"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="text-[9px] font-black text-muted-foreground uppercase"
                                        >Avg RPM</Label
                                    >
                                    <Input
                                        v-model="form.rpm"
                                        type="number"
                                        step="1"
                                        class="h-14 rounded-2xl border-none bg-muted/40 px-6 text-lg font-black focus:ring-accent"
                                        placeholder="0"
                                    />
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Label
                                    class="text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase italic opacity-50"
                                    >Personal Notes</Label
                                >
                                <textarea
                                    v-model="form.athlete_notes"
                                    rows="3"
                                    class="w-full rounded-[2rem] border-none bg-muted/40 p-8 text-sm font-semibold text-foreground focus:ring-2 focus:ring-accent focus:outline-none"
                                    placeholder="How did it feel? Any issues?"
                                ></textarea>
                            </div>

                            <div class="space-y-4">
<<<<<<< HEAD
                                <Label
                                    class="text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase italic opacity-50"
                                    >Documentation (Photos/Files)</Label
                                >
                                <div
                                    class="flex w-full items-center justify-center"
                                >
                                    <label
                                        class="flex h-32 w-full cursor-pointer flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-border bg-muted/20 transition-all hover:bg-muted/30"
                                    >
                                        <div
                                            class="flex flex-col items-center justify-center pt-5 pb-6 text-muted-foreground"
                                        >
                                            <Upload class="mb-2 h-6 w-6" />
                                            <p
                                                class="text-[10px] font-black tracking-widest uppercase"
                                            >
                                                {{
                                                    form.attachments.length > 0
                                                        ? `${form.attachments.length} Files Ready`
                                                        : 'Drop proof or Click to Upload'
                                                }}
                                            </p>
=======
                                <Label class="text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50 italic">Documentation (Photos/Files)</Label>
                                
                                <div v-if="attachmentPreviews.length > 0" class="grid grid-cols-4 gap-4 mb-4">
                                    <div v-for="(preview, index) in attachmentPreviews" :key="index" class="aspect-square rounded-xl bg-muted/20 border border-border p-1">
                                        <img :src="preview" class="w-full h-full object-cover rounded-lg" />
                                    </div>
                                </div>

                                <div class="flex items-center justify-center w-full">
                                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-border rounded-[2rem] cursor-pointer bg-muted/20 hover:bg-muted/30 transition-all">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6 text-muted-foreground">
                                            <Upload class="w-6 h-6 mb-2" />
                                            <p class="text-[10px] font-black uppercase tracking-widest">{{ form.attachments.length > 0 ? `${form.attachments.length} Files Ready` : 'Drop proof or Click to Upload' }}</p>
>>>>>>> 023a5e4 (feat: implement training log management, performance trend visualization, and date filtering in athlete detail view (Update besar SRS-F dari tujuan utama sistem))
                                        </div>
                                        <input
                                            type="file"
                                            multiple
                                            @change="handleFileChange"
                                            class="hidden"
                                        />
                                    </label>
                                </div>
                                <p v-if="form.id && !form.attachments.length" class="text-[9px] font-bold text-accent/60 uppercase text-center italic mt-2">* Uploading new files will replace existing ones for this session</p>
                            </div>

<<<<<<< HEAD
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-[2rem] bg-accent py-6 text-xs font-black tracking-[0.3em] text-white uppercase shadow-2xl shadow-accent/40 transition-all hover:scale-[1.02] hover:bg-accent/90 active:scale-[0.98] disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Syncing...'
                                        : 'Log Activity Performance'
                                }}
=======
                            <button type="submit" :disabled="form.processing" class="rounded-[2rem] bg-accent py-6 text-xs font-black tracking-[0.3em] text-white uppercase shadow-2xl shadow-accent/40 hover:bg-accent/90 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50">
                                {{ form.processing ? 'Syncing...' : (form.id ? 'Update Performance Metrics' : 'Log Activity Performance') }}
>>>>>>> 023a5e4 (feat: implement training log management, performance trend visualization, and date filtering in athlete detail view (Update besar SRS-F dari tujuan utama sistem))
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
