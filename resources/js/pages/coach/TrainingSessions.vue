<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    CalendarDays,
    ChevronRight,
    ClipboardList,
    Clock,
    Milestone,
    Plus,
    RotateCcw,
    Trash2,
    Users,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

import { Checkbox } from '@/components/ui/checkbox';
import CustomSelect from '@/components/ui/CustomSelect.vue';
import DatePicker from '@/components/ui/DatePicker.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface Athlete {
    id: number;
    name: string;
    avatar?: string;
}

interface ExerciseType {
    id: number;
    name: string;
}

interface Session {
    id: number;
    title: string;
    description: string | null;
    scheduled_date: string;
    scheduled_time: string | null;
    repeat_weekly: boolean;
    exercise_type: ExerciseType;
    athletes_count: number;
    athletes: Athlete[];
    target_distance_km: number | null;
    target_duration_minutes: number | null;
    target_avg_speed: number | null;
    target_rpm: number | null;
}

const props = defineProps<{
    sessions: Session[];
    athletes: Athlete[];
    exerciseTypes: ExerciseType[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Jadwal Latihan', href: '/coach/training-sessions' },
];

const showCreateModal = ref(false);

const form = useForm({
    exercise_type_id: '',
    title: '',
    description: '',
    scheduled_date: new Date().toISOString().split('T')[0],
    scheduled_time: '08:00',
    repeat_weekly: false,
    athlete_ids: [] as number[],
    target_distance_km: '',
    target_duration_minutes: '',
    target_avg_speed: '',
    target_rpm: '',
});

const submit = () => {
    form.post('/coach/training-sessions', {
        onSuccess: () => {
            showCreateModal.value = false;
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

const toggleAthlete = (id: number) => {
    const index = form.athlete_ids.indexOf(id);

    if (index === -1) {
        form.athlete_ids.push(id);
    } else {
        form.athlete_ids.splice(index, 1);
    }
};

const deleteSession = (session: Session) => {
    if (confirm(`Apakah Anda yakin ingin menghapus sesi "${session.title}"?`)) {
        router.delete(`/coach/training-sessions/${session.id}`);
    }
};
</script>

<template>
    <Head title="Jadwal Latihan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <!-- Header -->
            <div
                class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row"
            >
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-secondary p-3 text-accent">
                        <ClipboardList class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-black tracking-tighter text-foreground uppercase md:text-4xl"
                        >
                            Jadwal Latihan
                        </h1>
                        <p
                            class="mt-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                        >
                            Kelola sesi latihan individu & grup
                        </p>
                    </div>
                </div>
                <button
                    @click="showCreateModal = true"
                    class="flex items-center gap-2 rounded-xl bg-accent px-8 py-4 text-xs font-black tracking-widest text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-95"
                >
                    <Plus class="h-4 w-4" /> Tambah Sesi
                </button>
            </div>

            <!-- Empty State -->
            <div
                v-if="sessions.length === 0"
                class="flex flex-col items-center justify-center gap-6 rounded-3xl border border-border bg-card p-16 text-center shadow-xl"
            >
                <div
                    class="flex h-20 w-20 items-center justify-center rounded-2xl bg-accent/10 text-accent"
                >
                    <ClipboardList class="h-10 w-10" />
                </div>
                <div>
                    <h3
                        class="text-lg font-black tracking-tight text-foreground uppercase"
                    >
                        Belum Ada Jadwal Latihan
                    </h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Mulai jadwalkan sesi latihan untuk atlet Anda.
                    </p>
                </div>
                <button
                    @click="showCreateModal = true"
                    class="flex items-center gap-2 rounded-xl bg-accent px-6 py-3 text-xs font-black tracking-widest text-white uppercase shadow-lg shadow-accent/20 transition-all hover:bg-accent/90"
                >
                    <Plus class="h-4 w-4" /> Tambah Sesi Latihan
                </button>
            </div>

            <!-- Sessions Grid -->
            <div
                v-else
                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="session in sessions"
                    :key="session.id"
                    class="group relative flex cursor-pointer flex-col gap-6 overflow-hidden rounded-3xl border border-border bg-card p-8 shadow-xl transition-all hover:border-accent/30 hover:shadow-2xl"
                    @click="
                        router.visit(`/coach/training-sessions/${session.id}`)
                    "
                >
                    <!-- Top: Date + Time -->
                    <div class="flex items-center justify-between">
                        <div
                            class="flex items-center gap-2 text-[10px] font-black tracking-widest text-accent uppercase"
                        >
                            <CalendarDays class="h-3 w-3" />
                            {{ formatDate(session.scheduled_date) }}
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                v-if="session.scheduled_time"
                                class="flex items-center gap-1.5 text-[10px] font-black text-muted-foreground uppercase opacity-60"
                            >
                                <Clock class="h-3 w-3" />
                                {{ session.scheduled_time }}
                            </div>
                            <button
                                @click.stop="deleteSession(session)"
                                class="rounded-lg bg-destructive/10 p-2 text-destructive transition-all hover:bg-destructive hover:text-white"
                            >
                                <Trash2 class="h-3 w-3" />
                            </button>
                        </div>
                    </div>

                    <!-- Title & Type -->
                    <div>
                        <div class="mb-1 flex items-center gap-2">
                            <span
                                class="rounded bg-secondary px-2 py-0.5 text-[8px] font-black tracking-wider text-accent uppercase"
                            >
                                {{ session.exercise_type.name }}
                            </span>
                            <span
                                v-if="session.repeat_weekly"
                                class="flex items-center gap-1 rounded bg-green-500/10 px-2 py-0.5 text-[8px] font-black tracking-wider text-green-500 uppercase"
                            >
                                <RotateCcw class="h-2.5 w-2.5" /> Repeat
                            </span>
                        </div>
                        <h3
                            class="text-lg font-black tracking-tight text-foreground uppercase transition-colors group-hover:text-accent"
                        >
                            {{ session.title }}
                        </h3>
                    </div>

                    <!-- Athletes -->
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="flex -space-x-2">
                            <div
                                v-for="athlete in session.athletes.slice(0, 3)"
                                :key="athlete.id"
                                class="flex h-7 w-7 items-center justify-center overflow-hidden rounded-full border-2 border-card bg-secondary text-[8px] font-black text-foreground"
                            >
                                <img
                                    v-if="athlete.avatar"
                                    :src="athlete.avatar"
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
                            <div
                                v-if="session.athletes_count > 3"
                                class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-card bg-accent text-[8px] font-black text-white"
                            >
                                +{{ session.athletes_count - 3 }}
                            </div>
                        </div>
                        <span
                            class="text-[10px] font-bold text-muted-foreground"
                            >{{ session.athletes_count }} Atlet</span
                        >
                    </div>

                    <!-- Targets -->
                    <div class="grid grid-cols-2 gap-2">
                        <div
                            v-if="session.target_distance_km"
                            class="flex items-center gap-2 rounded-xl bg-muted/30 p-2.5"
                        >
                            <Milestone class="h-3 w-3 text-accent opacity-50" />
                            <span class="text-[10px] font-black text-foreground"
                                >{{ session.target_distance_km }} KM</span
                            >
                        </div>
                        <div
                            v-if="session.target_rpm"
                            class="flex items-center gap-2 rounded-xl bg-muted/30 p-2.5"
                        >
                            <RotateCcw class="h-3 w-3 text-accent opacity-50" />
                            <span class="text-[10px] font-black text-foreground"
                                >{{ session.target_rpm }} RPM</span
                            >
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex items-center justify-between border-t border-border pt-4"
                    >
                        <span
                            class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                        >
                            ID: #{{ session.id }}
                        </span>
                        <ChevronRight
                            class="h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-accent"
                        />
                    </div>
                </div>
            </div>

            <!-- Create Modal -->
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-xl"
            >
                <div
                    class="w-full max-w-2xl animate-in overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-2xl duration-300 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-border bg-muted/20 p-8"
                    >
                        <div>
                            <h2
                                class="text-2xl font-black tracking-tight text-foreground uppercase"
                            >
                                Tambah Sesi Latihan
                            </h2>
                            <p
                                class="mt-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Jadwalkan aktivitas baru
                            </p>
                        </div>
                        <button
                            @click="showCreateModal = false"
                            class="rounded-full p-3 text-muted-foreground hover:bg-muted/50"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto p-8">
                        <form
                            @submit.prevent="submit"
                            class="flex flex-col gap-6"
                        >
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Title -->
                                <div class="space-y-2">
                                    <Label
                                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                        >Judul Sesi</Label
                                    >
                                    <Input
                                        v-model="form.title"
                                        class="h-12 rounded-xl border-none bg-muted/30 px-4 font-black focus:ring-2 focus:ring-accent"
                                        placeholder="Contoh: Endurance Ride"
                                    />
                                </div>
                                <!-- Type -->
                                <div class="space-y-2">
                                    <Label
                                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                        >Jenis Latihan</Label
                                    >
                                    <CustomSelect
                                        v-model="form.exercise_type_id"
                                        :options="
                                            props.exerciseTypes.map((t) => ({
                                                value: t.id.toString(),
                                                label: t.name,
                                            }))
                                        "
                                    />
                                </div>
                            </div>

                            <!-- Athlete Selection (Multi) -->
                            <div class="space-y-3">
                                <Label
                                    class="flex items-center gap-2 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                >
                                    <Users class="h-3 w-3" /> Pilih Atlet ({{
                                        form.athlete_ids.length
                                    }}
                                    Terpilih)
                                </Label>
                                <div
                                    class="grid max-h-40 grid-cols-2 gap-2 overflow-y-auto rounded-2xl bg-muted/30 p-4"
                                >
                                    <div
                                        v-for="athlete in athletes"
                                        :key="athlete.id"
                                        @click="toggleAthlete(athlete.id)"
                                        :class="
                                            form.athlete_ids.includes(
                                                athlete.id,
                                            )
                                                ? 'border-accent bg-accent text-white'
                                                : 'border-border bg-background text-foreground'
                                        "
                                        class="flex cursor-pointer items-center gap-2 rounded-xl border p-3 transition-all hover:scale-105 active:scale-95"
                                    >
                                        <div
                                            :class="
                                                form.athlete_ids.includes(
                                                    athlete.id,
                                                )
                                                    ? 'bg-white/20'
                                                    : 'bg-secondary'
                                            "
                                            class="flex h-6 w-6 items-center justify-center overflow-hidden rounded-full text-[8px] font-black"
                                        >
                                            <img
                                                v-if="athlete.avatar"
                                                :src="athlete.avatar"
                                                class="h-full w-full object-cover"
                                            />
                                            <span v-else>
                                                {{
                                                    athlete.name
                                                        .substring(0, 2)
                                                        .toUpperCase()
                                                }}
                                            </span>
                                        </div>
                                        <span
                                            class="truncate text-[11px] font-black uppercase"
                                            >{{ athlete.name }}</span
                                        >
                                    </div>
                                </div>
                                <p
                                    v-if="form.errors.athlete_ids"
                                    class="text-[10px] font-bold text-destructive"
                                >
                                    {{ form.errors.athlete_ids }}
                                </p>
                            </div>

                            <!-- Date & Time -->
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <div class="space-y-2">
                                    <Label
                                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                        >Tanggal</Label
                                    >
                                    <DatePicker v-model="form.scheduled_date" />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                        >Waktu</Label
                                    >
                                    <Input
                                        v-model="form.scheduled_time"
                                        type="time"
                                        class="h-12 rounded-xl border-none bg-muted/30 px-4 font-black"
                                    />
                                </div>
                                <div class="flex items-end pb-2">
                                    <div
                                        class="flex w-full items-center justify-between gap-2 rounded-xl bg-muted/30 p-3"
                                    >
                                        <div class="flex items-center gap-2">
                                            <RotateCcw
                                                class="h-3 w-3 text-muted-foreground"
                                            />
                                            <span
                                                class="text-[9px] leading-none font-black uppercase"
                                                >Ulang Mingguan</span
                                            >
                                        </div>
                                        <Checkbox
                                            :checked="form.repeat_weekly"
                                            @update:checked="
                                                (v: boolean) =>
                                                    (form.repeat_weekly = v)
                                            "
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Targets -->
                            <div class="space-y-3">
                                <Label
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                    >Target Performa</Label
                                >
                                <div
                                    class="grid grid-cols-2 gap-4 md:grid-cols-4"
                                >
                                    <div class="space-y-1">
                                        <span
                                            class="text-[8px] font-black text-muted-foreground uppercase opacity-60"
                                            >Jarak (KM)</span
                                        >
                                        <Input
                                            v-model="form.target_distance_km"
                                            type="number"
                                            step="0.1"
                                            class="h-11 rounded-xl border-none bg-muted/30 px-3 font-black"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <span
                                            class="text-[8px] font-black text-muted-foreground uppercase opacity-60"
                                            >Durasi (Mnt)</span
                                        >
                                        <Input
                                            v-model="
                                                form.target_duration_minutes
                                            "
                                            type="number"
                                            class="h-11 rounded-xl border-none bg-muted/30 px-3 font-black"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <span
                                            class="text-[8px] font-black text-muted-foreground uppercase opacity-60"
                                            >Speed (KM/H)</span
                                        >
                                        <Input
                                            v-model="form.target_avg_speed"
                                            type="number"
                                            step="0.1"
                                            class="h-11 rounded-xl border-none bg-muted/30 px-3 font-black"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <span
                                            class="text-[8px] font-black text-muted-foreground uppercase opacity-60"
                                            >Cadence (RPM)</span
                                        >
                                        <Input
                                            v-model="form.target_rpm"
                                            type="number"
                                            class="h-11 rounded-xl border-none bg-muted/30 px-3 font-black"
                                        />
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="mt-4 rounded-2xl bg-accent py-5 text-[10px] font-black tracking-[0.2em] text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-95 disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Sesi Latihan'
                                }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
