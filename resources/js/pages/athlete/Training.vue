<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Bike,
    Calendar,
    ChevronRight,
    Clock,
    FileText,
    History,
    Milestone,
    Plus,
    RotateCcw,
    TrendingUp,
    Upload,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

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
    attachments: Attachment[];
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

defineProps<{
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
const selectedSession = ref<any>(null);

const form = useForm({
    training_session_id: null as number | null,
    date: new Date().toISOString().split('T')[0],
    distance_km: '',
    duration_minutes: '',
    avg_speed: '',
    rpm: '',
    intensity: 'medium',
    type: '',
    athlete_notes: '',
    completion_status: 'completed',
    attachments: [] as File[],
});

const openLogModal = (session: any = null) => {
    selectedSession.value = session;

    form.reset();

    if (session) {
        form.training_session_id = session.id;
        form.type = session.exercise_type.name;
        // Pre-fill targets if available
        form.distance_km = session.target_distance_km || '';
        form.duration_minutes = session.target_duration_minutes || '';
        form.avg_speed = session.target_avg_speed || '';
        form.rpm = session.target_rpm || '';
    }

    showLogModal.value = true;
};

const handleFileChange = (e: Event) => {
    const files = (e.target as HTMLInputElement).files;

    if (files) {
        form.attachments = Array.from(files);
    }
};

const submitLog = () => {
    form.post('/athlete/training/log', {
        onSuccess: () => {
            showLogModal.value = false;
            form.reset();
        },
    });
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
</script>

<template>
    <Head title="Latihan Saya" />

    <AppLayout :breadcrumbs="breadcrumbs">
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
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
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
                                </div>
                            </div>
                        </div>

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
                                </div>
                            </div>

                            <div class="p-8 md:p-10">
                                <div class="flex flex-col gap-6">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                                </div>
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
                                        </div>
                                        <input
                                            type="file"
                                            multiple
                                            @change="handleFileChange"
                                            class="hidden"
                                        />
                                    </label>
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
                                        : 'Log Activity Performance'
                                }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
