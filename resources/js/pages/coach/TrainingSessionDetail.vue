<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    CheckCircle,
    Clock,
    FileText,
    MessageSquare,
    Star,
} from 'lucide-vue-next';
import { ref } from 'vue';

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
    avg_heart_rate?: number | null;
    pace_per_km?: string | null;
    avg_watt_power?: number | null;
    rpm: number | null;
    intensity: string | null;
    type: string | null;
    athlete_notes: string | null;
    attendance_status: string;
    completion_status: string;
    coach_rating: number | null;
    coach_evaluation: string | null;
    coach_comments: string | null;
    athlete: { id: number; name: string; email: string };
    attachments: Attachment[];
}
interface Session {
    id: number;
    title: string;
    description: string | null;
    scheduled_date: string;
    scheduled_time: string | null;
    exercise_type: { id: number; name: string };
    target_distance_km: number | null;
    target_duration_minutes: number | null;
    target_avg_speed: number | null;
    target_rpm: number | null;
    athletes: { id: number; name: string }[];
    logs: Log[];
}

const props = defineProps<{ session: Session }>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Jadwal Latihan', href: '/coach/training-sessions' },
    { title: props.session.title, href: '#' },
];

const editingLogId = ref<number | null>(null);
const evalForm = useForm({
    attendance_status: '',
    completion_status: '',
    coach_rating: null as number | null,
    coach_evaluation: '',
    coach_comments: '',
});

const startEvaluating = (log: Log) => {
    editingLogId.value = log.id;
    evalForm.attendance_status = log.attendance_status;
    evalForm.completion_status = log.completion_status;
    evalForm.coach_rating = log.coach_rating;
    evalForm.coach_evaluation = log.coach_evaluation || '';
    evalForm.coach_comments = log.coach_comments || '';
};

const submitEvaluation = (logId: number) => {
    evalForm.patch(`/coach/training-logs/${logId}/evaluation`, {
        onSuccess: () => {
            editingLogId.value = null;
        },
    });
};

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
const intensityLabel = (i: string) =>
    ({
        low: 'Rendah',
        medium: 'Sedang',
        high: 'Tinggi',
        very_high: 'Sangat Tinggi',
    })[i] || i;
const attendanceLabel = (s: string) =>
    ({ present: 'Hadir', absent: 'Absen', late: 'Terlambat', excused: 'Izin' })[
        s
    ] || s;
const completionLabel = (s: string) =>
    ({
        not_started: 'Belum Mulai',
        in_progress: 'Sedang Berlangsung',
        completed: 'Selesai',
        incomplete: 'Tidak Selesai',
    })[s] || s;
const completionColor = (s: string) =>
    ({
        completed: 'text-green-500',
        in_progress: 'text-blue-500',
        incomplete: 'text-destructive',
        not_started: 'text-muted-foreground',
    })[s] || '';
</script>

<template>
    <Head :title="`Sesi | ${session.title}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <!-- Header -->
            <div class="border-b border-border pb-6">
                <div class="mb-2 flex items-center gap-2">
                    <span
                        class="rounded-md bg-accent/10 px-2 py-0.5 text-[9px] font-black text-accent uppercase"
                        >{{ session.exercise_type.name }}</span
                    >
                    <span class="text-xs text-muted-foreground"
                        >• {{ session.athletes.length }} Atlet</span
                    >
                </div>
                <h1
                    class="text-3xl font-black tracking-tighter text-foreground uppercase md:text-4xl"
                >
                    {{ session.title }}
                </h1>
                <div
                    class="mt-2 flex flex-wrap items-center gap-3 text-xs text-muted-foreground"
                >
                    <span class="flex items-center gap-1"
                        ><Clock class="h-3 w-3" />
                        {{ formatDate(session.scheduled_date) }}</span
                    >
                    <span
                        v-if="session.scheduled_time"
                        class="flex items-center gap-1"
                        ><Clock class="h-3 w-3" />
                        {{ session.scheduled_time }}</span
                    >
                </div>
                <p
                    v-if="session.description"
                    class="mt-3 max-w-2xl text-sm text-muted-foreground"
                >
                    {{ session.description }}
                </p>
            </div>

            <!-- Session Targets -->
            <div
                v-if="
                    session.target_distance_km ||
                    session.target_duration_minutes ||
                    session.target_avg_speed ||
                    session.target_rpm
                "
                class="grid grid-cols-2 gap-4 md:grid-cols-4"
            >
                <div
                    v-if="session.target_distance_km"
                    class="rounded-2xl border border-border bg-card p-6 text-center shadow-lg"
                >
                    <p class="text-3xl font-black text-foreground">
                        {{ session.target_distance_km }}
                    </p>
                    <p
                        class="mt-1 text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                    >
                        Target KM
                    </p>
                </div>
                <div
                    v-if="session.target_duration_minutes"
                    class="rounded-2xl border border-border bg-card p-6 text-center shadow-lg"
                >
                    <p class="text-3xl font-black text-foreground">
                        {{ session.target_duration_minutes }}
                    </p>
                    <p
                        class="mt-1 text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                    >
                        Target Menit
                    </p>
                </div>
                <div
                    v-if="session.target_avg_speed"
                    class="rounded-2xl border border-border bg-card p-6 text-center shadow-lg"
                >
                    <p class="text-3xl font-black text-foreground">
                        {{ session.target_avg_speed }}
                    </p>
                    <p
                        class="mt-1 text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                    >
                        Target KM/H
                    </p>
                </div>
                <div
                    v-if="session.target_rpm"
                    class="rounded-2xl border border-border bg-card p-6 text-center shadow-lg"
                >
                    <p class="text-3xl font-black text-foreground">
                        {{ session.target_rpm }}
                    </p>
                    <p
                        class="mt-1 text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                    >
                        Target RPM
                    </p>
                </div>
            </div>

            <!-- Training Logs -->
            <div class="flex flex-col gap-6">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-secondary p-3 text-accent">
                        <FileText class="h-5 w-5" />
                    </div>
                    <h2
                        class="text-2xl font-black tracking-tight text-foreground uppercase"
                    >
                        Log Latihan Atlet
                    </h2>
                </div>

                <div
                    v-if="session.logs.length === 0"
                    class="rounded-3xl border border-border bg-card p-12 text-center shadow-xl"
                >
                    <p class="text-muted-foreground">
                        Belum ada log latihan untuk sesi ini. Atlet belum
                        menginput data latihan.
                    </p>
                </div>

                <div
                    v-for="log in session.logs"
                    :key="log.id"
                    class="rounded-3xl border border-border bg-card p-8 shadow-xl"
                >
                    <!-- Log Header -->
                    <div
                        class="mb-6 flex flex-col items-start justify-between gap-4 border-b border-border pb-4 md:flex-row md:items-center"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-accent/10 text-xs font-black text-accent"
                            >
                                {{
                                    log.athlete.name
                                        .split(' ')
                                        .map((n) => n[0])
                                        .slice(0, 2)
                                        .join('')
                                }}
                            </div>
                            <div>
                                <h4 class="font-bold text-foreground">
                                    {{ log.athlete.name }}
                                </h4>
                                <p class="text-[10px] text-muted-foreground">
                                    {{ formatDate(log.date) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                :class="completionColor(log.completion_status)"
                                class="text-xs font-black uppercase"
                                ><CheckCircle class="mr-1 inline h-3 w-3" />{{
                                    completionLabel(log.completion_status)
                                }}</span
                            >
                            <span
                                class="rounded-md bg-muted px-2 py-0.5 text-[9px] font-black text-muted-foreground uppercase"
                                >{{
                                    attendanceLabel(log.attendance_status)
                                }}</span
                            >
                        </div>
                    </div>

                    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-7">
                        <div class="rounded-xl bg-muted/30 p-4 text-center">
                            <p class="text-xl font-black text-foreground">{{ log.distance_km || '-' }}</p>
                            <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50">KM</p>
                        </div>
                        <div class="rounded-xl bg-muted/30 p-4 text-center">
                            <p class="text-xl font-black text-foreground">{{ log.duration_minutes || '-' }}</p>
                            <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50">MENIT</p>
                        </div>
                        <div class="rounded-xl bg-muted/30 p-4 text-center">
                            <p class="text-xl font-black text-foreground">{{ log.avg_speed || '-' }}</p>
                            <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50">KM/H</p>
                        </div>
                        <div class="rounded-xl bg-muted/30 p-4 text-center">
                            <p class="text-xl font-black text-foreground text-orange-500">{{ log.avg_watt_power || '-' }}</p>
                            <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50">WATT</p>
                        </div>
                        <div class="rounded-xl bg-muted/30 p-4 text-center">
                            <p class="text-xl font-black text-foreground">{{ log.avg_heart_rate || '-' }}</p>
                            <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50">BPM</p>
                        </div>
                        <div class="rounded-xl bg-muted/30 p-4 text-center">
                            <p class="text-xl font-black text-foreground">{{ log.pace_per_km || '-' }}</p>
                            <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50">PACE</p>
                        </div>
                        <div class="rounded-xl bg-muted/30 p-4 text-center">
                            <p class="text-xl font-black text-foreground">{{ log.intensity ? intensityLabel(log.intensity) : '-' }}</p>
                            <p class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50">INTENSITAS</p>
                        </div>
                    </div>

                    <!-- Athlete Notes -->
                    <div
                        v-if="log.athlete_notes"
                        class="mb-6 rounded-xl bg-muted/20 p-4"
                    >
                        <p
                            class="mb-1 text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                        >
                            Catatan Atlet
                        </p>
                        <p class="text-sm text-foreground">
                            {{ log.athlete_notes }}
                        </p>
                    </div>

                    <!-- Attachments -->
                    <div v-if="log.attachments.length > 0" class="mb-6">
                        <p
                            class="mb-2 text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                        >
                            Bukti Latihan
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <a
                                v-for="att in log.attachments"
                                :key="att.id"
                                :href="`/storage/${att.file_path}`"
                                target="_blank"
                                class="flex items-center gap-2 rounded-lg border border-border bg-muted/30 px-3 py-2 text-[10px] font-bold text-foreground transition-colors hover:border-accent/30 hover:text-accent"
                            >
                                <FileText class="h-3 w-3" /> {{ att.file_name }}
                            </a>
                        </div>
                    </div>

                    <!-- Coach Evaluation Display / Edit -->
                    <div class="border-t border-border pt-4">
                        <div v-if="editingLogId !== log.id">
                            <!-- Display mode -->
                            <div class="flex items-start justify-between">
                                <div v-if="log.coach_rating">
                                    <div class="flex items-center gap-2">
                                        <Star class="h-4 w-4 text-accent" />
                                        <span
                                            class="text-lg font-black text-accent"
                                            >{{ log.coach_rating }}/10</span
                                        >
                                    </div>
                                    <p
                                        v-if="log.coach_evaluation"
                                        class="mt-2 text-sm text-foreground"
                                    >
                                        {{ log.coach_evaluation }}
                                    </p>
                                    <p
                                        v-if="log.coach_comments"
                                        class="mt-1 text-xs text-muted-foreground italic"
                                    >
                                        {{ log.coach_comments }}
                                    </p>
                                </div>
                                <div
                                    v-else
                                    class="text-xs text-muted-foreground italic"
                                >
                                    Belum ada evaluasi
                                </div>
                                <button
                                    @click="startEvaluating(log)"
                                    class="rounded-lg bg-accent/10 px-4 py-2 text-[10px] font-black text-accent uppercase transition-colors hover:bg-accent/20"
                                >
                                    <MessageSquare
                                        class="mr-1 inline h-3 w-3"
                                    />
                                    {{ log.coach_rating ? 'Edit' : 'Evaluasi' }}
                                </button>
                            </div>
                        </div>
                        <div v-else>
                            <!-- Edit mode -->
                            <form
                                @submit.prevent="submitEvaluation(log.id)"
                                class="flex flex-col gap-4"
                            >
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label
                                            class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                            >Kehadiran</label
                                        >
                                        <select
                                            v-model="evalForm.attendance_status"
                                            class="w-full rounded-xl border-none bg-muted/30 px-4 py-3 text-sm font-bold text-foreground focus:ring-2 focus:ring-accent focus:outline-none"
                                        >
                                            <option value="present">
                                                Hadir
                                            </option>
                                            <option value="absent">
                                                Absen
                                            </option>
                                            <option value="late">
                                                Terlambat
                                            </option>
                                            <option value="excused">
                                                Izin
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label
                                            class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                            >Status Penyelesaian</label
                                        >
                                        <select
                                            v-model="evalForm.completion_status"
                                            class="w-full rounded-xl border-none bg-muted/30 px-4 py-3 text-sm font-bold text-foreground focus:ring-2 focus:ring-accent focus:outline-none"
                                        >
                                            <option value="not_started">
                                                Belum Mulai
                                            </option>
                                            <option value="in_progress">
                                                Sedang Berlangsung
                                            </option>
                                            <option value="completed">
                                                Selesai
                                            </option>
                                            <option value="incomplete">
                                                Tidak Selesai
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                        >Rating (1-10)</label
                                    >
                                    <input
                                        v-model.number="evalForm.coach_rating"
                                        type="number"
                                        min="1"
                                        max="10"
                                        class="w-full rounded-xl border-none bg-muted/30 px-4 py-3 text-sm font-bold text-foreground focus:ring-2 focus:ring-accent focus:outline-none"
                                        placeholder="8"
                                    />
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                        >Evaluasi</label
                                    >
                                    <textarea
                                        v-model="evalForm.coach_evaluation"
                                        rows="2"
                                        class="w-full rounded-xl border-none bg-muted/30 px-4 py-3 text-sm font-semibold text-foreground focus:ring-2 focus:ring-accent focus:outline-none"
                                        placeholder="Evaluasi performa atlet..."
                                    ></textarea>
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                        >Komentar</label
                                    >
                                    <textarea
                                        v-model="evalForm.coach_comments"
                                        rows="2"
                                        class="w-full rounded-xl border-none bg-muted/30 px-4 py-3 text-sm font-semibold text-foreground focus:ring-2 focus:ring-accent focus:outline-none"
                                        placeholder="Komentar tambahan..."
                                    ></textarea>
                                </div>
                                <div class="flex gap-3">
                                    <button
                                        type="submit"
                                        :disabled="evalForm.processing"
                                        class="flex-1 rounded-xl bg-accent py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-lg shadow-accent/20 transition-all hover:bg-accent/90 disabled:opacity-50"
                                    >
                                        {{
                                            evalForm.processing
                                                ? 'Menyimpan...'
                                                : 'Simpan Evaluasi'
                                        }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="editingLogId = null"
                                        class="rounded-xl border border-border px-6 py-3 text-[10px] font-black tracking-widest text-muted-foreground uppercase transition-colors hover:bg-muted/30"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
