<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Calendar,
    ChevronRight,
    Flag,
    MapPin,
    Plus,
    Trash2,
    Users,
    X,
    Trophy,
    Settings,
    Pencil,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

import CustomSelect from '@/components/ui/CustomSelect.vue';
import DatePicker from '@/components/ui/DatePicker.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface Athlete {
    id: number;
    name: string;
    has_valid_license?: boolean;
}

interface EventType {
    id: number;
    name: string;
    coach_id: number | null;
}

interface EventPoint {
    id: number;
    name: string;
    coach_id: number | null;
}

interface Event {
    id: number;
    title: string;
    description: string | null;
    location: string | null;
    event_date: string;
    requires_license: boolean;
    event_type_id: number | null;
    type: EventType | null;
    athletes_count: number;
    athletes: (Athlete & {
        pivot: {
            status: string;
            result: string | null;
            notes: string | null;
            event_point_id: number | null;
        };
    })[];
}

const props = defineProps<{
    events: Event[];
    athletes: Athlete[];
    eventTypes: EventType[];
    eventPoints: EventPoint[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manajemen Event', href: '/coach/events' },
];

const showModal = ref(false);
const showSettingsModal = ref(false);
const showDetailModal = ref(false);
const editingEvent = ref<Event | null>(null);
const selectedEvent = ref<Event | null>(null);

const form = useForm({
    title: '',
    description: '',
    location: '',
    event_date: new Date().toISOString().split('T')[0],
    requires_license: false,
    event_type_id: null as number | null,
    athletes: [] as { id: number; event_point_id: number | null }[],
});

const typeForm = useForm({
    name: '',
});

const pointForm = useForm({
    name: '',
});

const openCreateModal = () => {
    editingEvent.value = null;
    form.reset();
    form.athletes = [];
    showModal.value = true;
};

const openEditModal = (event: Event) => {
    editingEvent.value = event;
    form.title = event.title;
    form.description = event.description || '';
    form.location = event.location || '';
    form.event_date = event.event_date;
    form.requires_license = event.requires_license;
    form.event_type_id = event.event_type_id;
    form.athletes = event.athletes.map((a) => ({
        id: a.id,
        event_point_id: a.pivot.event_point_id,
    }));
    showModal.value = true;
};

const openDetail = (event: Event) => {
    selectedEvent.value = event;
    showDetailModal.value = true;
};

const submit = () => {
    if (editingEvent.value) {
        form.patch(`/coach/events/${editingEvent.value.id}`, {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/coach/events', {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteEvent = (event: Event) => {
    if (confirm(`Apakah Anda yakin ingin menghapus event "${event.title}"?`)) {
        router.delete(`/coach/events/${event.id}`);
    }
};

const formatDate = (date: string) => {
    const d = new Date(date);

    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const toggleAthlete = (athlete: Athlete) => {
    // If requires license and this athlete doesn't have it, ignore click
    if (form.requires_license && !athlete.has_valid_license) {
        return;
    }

    const index = form.athletes.findIndex((a) => a.id === athlete.id);

    if (index === -1) {
        form.athletes.push({ id: athlete.id, event_point_id: null });
    } else {
        form.athletes.splice(index, 1);
    }
};

const isAthleteSelected = (athleteId: number) => {
    return form.athletes.some((a) => a.id === athleteId);
};

const getSelectedAthletePoint = (athleteId: number) => {
    const athlete = form.athletes.find((a) => a.id === athleteId);

    return athlete?.event_point_id ? String(athlete.event_point_id) : '';
};

const updateAthletePoint = (athleteId: number, pointId: any) => {
    const athlete = form.athletes.find((a) => a.id === athleteId);

    if (athlete) {
        athlete.event_point_id = pointId === 'null' ? null : Number(pointId);
    }
};

const addType = () => {
    typeForm.post('/coach/event-types', {
        onSuccess: () => typeForm.reset(),
    });
};

const deleteType = (id: number) => {
    if (confirm('Hapus jenis event ini?')) {
        router.delete(`/coach/event-types/${id}`);
    }
};

const addPoint = () => {
    pointForm.post('/coach/event-points', {
        onSuccess: () => pointForm.reset(),
    });
};

const deletePoint = (id: number) => {
    if (confirm('Hapus poin kejuaraan ini?')) {
        router.delete(`/coach/event-points/${id}`);
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

const getPointName = (id: number | null) => {
    if (!id) {
        return 'General';
    }

    return props.eventPoints.find((p) => p.id === id)?.name || 'General';
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

const typeOptions = computed(() => [
    { value: '', label: 'Pilih Jenis Event' },
    ...props.eventTypes.map((t) => ({ value: String(t.id), label: t.name })),
]);

watch(
    () => form.requires_license,
    (newVal) => {
        if (newVal) {
            // Find athletes in form.athletes who don't have a valid license
            const invalidAthleteIds = props.athletes
                .filter((a) => !a.has_valid_license)
                .map((a) => a.id);

            form.athletes = form.athletes.filter(
                (a) => !invalidAthleteIds.includes(a.id),
            );
        }
    },
);
</script>

<template>
    <Head title="Manajemen Event" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <!-- Header -->
            <div
                class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="rounded-xl bg-orange-500/10 p-3 text-orange-500"
                    >
                        <Trophy class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-black tracking-tighter text-foreground uppercase md:text-4xl"
                        >
                            Target & Agenda Event
                        </h1>
                        <p
                            class="mt-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                        >
                            Manage competitions & athlete participation
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button
                        @click="showSettingsModal = true"
                        class="flex items-center gap-2 rounded-xl border border-border bg-card px-6 py-4 text-xs font-black tracking-widest text-foreground uppercase transition-all hover:bg-muted active:scale-95"
                    >
                        <Settings class="h-4 w-4" /> Pengaturan Event
                    </button>
                    <button
                        @click="openCreateModal"
                        class="flex items-center gap-2 rounded-xl bg-accent px-8 py-4 text-xs font-black tracking-widest text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-95"
                    >
                        <Plus class="h-4 w-4" /> Tambah Event
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="events.length === 0"
                class="flex flex-col items-center justify-center gap-6 rounded-[3rem] border border-dashed border-border bg-card p-20 text-center shadow-xl"
            >
                <div
                    class="flex h-24 w-24 items-center justify-center rounded-3xl bg-orange-500/5 text-orange-500/20"
                >
                    <Trophy class="h-12 w-12" />
                </div>
                <div class="max-w-sm">
                    <h3
                        class="text-xl font-black tracking-tight text-foreground uppercase italic"
                    >
                        Belum Ada Agenda Event
                    </h3>
                    <p
                        class="mt-2 text-sm font-medium text-muted-foreground italic opacity-60"
                    >
                        Mulai rencanakan kompetisi atau training camp untuk
                        memotivasi atlet Anda mencapai target performa.
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    class="rounded-2xl border border-accent/20 bg-accent/5 px-8 py-4 text-[10px] font-black tracking-[0.2em] text-accent uppercase transition-all hover:bg-accent hover:text-white"
                >
                    Buat Agenda Event Sekarang
                </button>
            </div>

            <!-- Events Grid -->
            <div
                v-else
                class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="event in events"
                    :key="event.id"
                    class="group relative flex flex-col justify-between overflow-hidden rounded-[2.5rem] border border-border bg-card p-8 shadow-xl transition-all hover:border-accent/30 hover:shadow-2xl"
                >
                    <div class="space-y-6">
                        <!-- Head: Type & Actions -->
                        <div class="flex items-center justify-between">
                            <span
                                v-if="event.type"
                                :class="getTypeColor(event.event_type_id)"
                                class="rounded-lg border px-3 py-1 text-[8px] font-black tracking-widest uppercase"
                            >
                                {{ event.type.name }}
                            </span>
                            <span
                                v-else
                                class="rounded-lg border border-border bg-muted px-3 py-1 text-[8px] font-black tracking-widest text-muted-foreground uppercase"
                            >
                                Uncategorized
                            </span>
                            <div
                                class="flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <button
                                    @click="openEditModal(event)"
                                    class="rounded-lg bg-blue-500/10 p-2 text-blue-500 transition-all hover:bg-blue-500 hover:text-white"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    @click="deleteEvent(event)"
                                    class="rounded-lg bg-destructive/10 p-2 text-destructive transition-all hover:bg-destructive hover:text-white"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- Date & Title -->
                        <div class="space-y-2">
                            <div
                                class="flex items-center gap-2 text-[10px] font-black tracking-widest text-accent uppercase"
                            >
                                <Calendar class="h-3 w-3" />
                                {{ formatDate(event.event_date) }}
                            </div>
                            <h3
                                class="text-xl leading-tight font-black tracking-tighter text-foreground uppercase italic transition-colors group-hover:text-accent"
                            >
                                {{ event.title }}
                            </h3>
                            <div
                                v-if="event.location"
                                class="flex items-center gap-2 text-[10px] font-bold text-muted-foreground italic opacity-60"
                            >
                                <MapPin class="h-3 w-3" /> {{ event.location }}
                            </div>
                        </div>

                        <!-- Description -->
                        <p
                            v-if="event.description"
                            class="line-clamp-2 text-xs leading-relaxed font-medium text-muted-foreground opacity-80"
                        >
                            {{ event.description }}
                        </p>

                        <!-- Participation Info -->
                        <div
                            @click="openDetail(event)"
                            class="flex cursor-pointer items-center justify-between rounded-2xl border border-border/50 bg-muted/30 p-4 transition-all hover:bg-muted/50"
                        >
                            <div class="flex flex-col gap-0.5">
                                <span
                                    class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                    >Participating Athletes</span
                                >
                                <div class="flex items-center gap-2">
                                    <Users class="h-3.5 w-3.5 text-accent" />
                                    <span class="text-sm font-black"
                                        >{{ event.athletes_count }}
                                        <small class="text-[9px] opacity-40"
                                            >Athletes</small
                                        ></span
                                    >
                                </div>
                            </div>
                            <ChevronRight
                                class="h-5 w-5 text-muted-foreground transition-all group-hover:translate-x-1 group-hover:text-accent"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-2xl"
            >
                <div
                    class="w-full max-w-3xl animate-in overflow-hidden rounded-[3rem] border border-border bg-card shadow-2xl duration-300 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-border bg-muted/20 p-8 md:p-10"
                    >
                        <div>
                            <h2
                                class="text-2xl font-black tracking-tighter text-foreground uppercase italic"
                            >
                                {{
                                    editingEvent
                                        ? 'Edit Agenda Event'
                                        : 'Buat Agenda Baru'
                                }}
                            </h2>
                            <p
                                class="mt-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                            >
                                {{
                                    editingEvent
                                        ? 'Perbarui detail kompetisi atau kegiatan'
                                        : 'Jadwalkan kompetisi mendatang untuk atlet'
                                }}
                            </p>
                        </div>
                        <button
                            @click="showModal = false"
                            class="rounded-full bg-white/5 p-3 text-muted-foreground transition-all hover:bg-white/10"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <div
                        class="custom-scrollbar max-h-[75vh] overflow-y-auto p-10"
                    >
                        <form
                            @submit.prevent="submit"
                            class="flex flex-col gap-8"
                        >
                            <div class="flex flex-col gap-6">
                                <p
                                    class="flex items-center gap-2 text-[10px] font-black tracking-[0.3em] text-accent uppercase italic opacity-80"
                                >
                                    <Flag class="h-3 w-3" /> Event Details
                                </p>
                                <div
                                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                                >
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Nama Event / Kompetisi</Label
                                        >
                                        <Input
                                            v-model="form.title"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                                            placeholder="Contoh: Kejurnas Balap Sepeda 2026"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Jenis Kegiatan</Label
                                        >
                                        <CustomSelect
                                            :model-value="
                                                form.event_type_id
                                                    ? String(form.event_type_id)
                                                    : ''
                                            "
                                            @update:model-value="
                                                (val) =>
                                                    (form.event_type_id = val
                                                        ? Number(val)
                                                        : null)
                                            "
                                            :options="typeOptions"
                                        />
                                    </div>
                                </div>
                                <div
                                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                                >
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Tanggal Pelaksanaan</Label
                                        >
                                        <DatePicker v-model="form.event_date" />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Lokasi</Label
                                        >
                                        <Input
                                            v-model="form.location"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black"
                                            placeholder="Kota / Nama Venue"
                                        />
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <div
                                        class="flex items-center gap-3 rounded-2xl border border-border bg-muted/30 p-4"
                                    >
                                        <input
                                            type="checkbox"
                                            id="requires_license"
                                            v-model="form.requires_license"
                                            class="h-5 w-5 rounded-md border-border text-accent accent-accent focus:ring-accent"
                                        />
                                        <div class="flex flex-col">
                                            <Label
                                                for="requires_license"
                                                class="cursor-pointer text-xs font-black tracking-widest uppercase"
                                                >Wajib Lisensi Atlet</Label
                                            >
                                            <span
                                                class="text-[10px] font-medium text-muted-foreground"
                                                >Jika dicentang, hanya atlet
                                                dengan lisensi aktif yang dapat
                                                berpartisipasi di event
                                                ini.</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <Label
                                        class="text-[10px] font-black uppercase opacity-60"
                                        >Deskripsi / Keterangan</Label
                                    >
                                    <textarea
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full rounded-2xl border-none bg-muted/30 p-6 text-sm font-medium outline-none focus:ring-2 focus:ring-accent"
                                        placeholder="Tuliskan target atau informasi tambahan..."
                                    ></textarea>
                                </div>
                            </div>

                            <div class="flex flex-col gap-6">
                                <p
                                    class="flex items-center gap-2 text-[10px] font-black tracking-[0.3em] text-orange-500 uppercase italic opacity-80"
                                >
                                    <Users class="h-3.5 w-3.5" /> Daftar Atlet &
                                    Poin Kejuaraan
                                </p>
                                <div
                                    class="grid max-h-64 grid-cols-1 gap-3 overflow-y-auto rounded-3xl border border-border bg-muted/10 p-6"
                                >
                                    <div
                                        v-for="athlete in athletes"
                                        :key="athlete.id"
                                        :class="[
                                            isAthleteSelected(athlete.id)
                                                ? 'border-2 border-accent bg-accent/10'
                                                : 'border-border bg-card',
                                            form.requires_license &&
                                            !athlete.has_valid_license
                                                ? 'cursor-not-allowed opacity-50 grayscale'
                                                : '',
                                        ]"
                                        class="flex flex-col gap-4 rounded-2xl border p-4 transition-all"
                                    >
                                        <div
                                            class="flex cursor-pointer items-center justify-between"
                                            @click="toggleAthlete(athlete)"
                                        >
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    :class="
                                                        isAthleteSelected(
                                                            athlete.id,
                                                        )
                                                            ? 'bg-accent text-white'
                                                            : 'bg-secondary text-muted-foreground'
                                                    "
                                                    class="flex h-8 w-8 items-center justify-center rounded-full text-[10px] font-black"
                                                >
                                                    {{
                                                        athlete.name
                                                            .substring(0, 2)
                                                            .toUpperCase()
                                                    }}
                                                </div>
                                                <span
                                                    class="truncate text-xs font-black uppercase"
                                                    >{{ athlete.name }}</span
                                                >
                                                <span
                                                    v-if="
                                                        !athlete.has_valid_license
                                                    "
                                                    class="rounded bg-destructive/10 px-2 py-0.5 text-[8px] font-black tracking-widest text-destructive uppercase"
                                                    >Non Lisensi</span
                                                >
                                            </div>
                                            <div
                                                v-if="
                                                    isAthleteSelected(
                                                        athlete.id,
                                                    )
                                                "
                                                class="rounded-full bg-accent p-1 text-white"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3 w-3"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="4"
                                                        d="M5 13l4 4L19 7"
                                                    />
                                                </svg>
                                            </div>
                                        </div>

                                        <div
                                            v-if="isAthleteSelected(athlete.id)"
                                            class="animate-in duration-200 slide-in-from-top-2"
                                        >
                                            <Label
                                                class="mb-2 block text-[8px] font-black uppercase opacity-40"
                                                >Pilih Poin Kejuaraan /
                                                Kategori</Label
                                            >
                                            <select
                                                :value="
                                                    getSelectedAthletePoint(
                                                        athlete.id,
                                                    )
                                                "
                                                @change="
                                                    (e: any) =>
                                                        updateAthletePoint(
                                                            athlete.id,
                                                            e.target.value,
                                                        )
                                                "
                                                class="h-10 w-full rounded-xl border border-border bg-card px-4 text-[10px] font-bold uppercase focus:ring-accent focus:outline-none"
                                            >
                                                <option value="null">
                                                    General (Tanpa Poin Khusus)
                                                </option>
                                                <option
                                                    v-for="point in eventPoints"
                                                    :key="point.id"
                                                    :value="point.id"
                                                >
                                                    {{ point.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="mt-4 rounded-[2rem] bg-accent py-6 text-xs font-black tracking-[0.3em] text-white uppercase shadow-2xl shadow-accent/40 transition-all hover:scale-[1.02] hover:bg-accent/90 active:scale-[0.98] disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Syncing...'
                                        : editingEvent
                                          ? 'Perbarui Agenda Event'
                                          : 'Simpan Agenda Event'
                                }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Settings Modal -->
            <div
                v-if="showSettingsModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-2xl"
            >
                <div
                    class="w-full max-w-4xl animate-in overflow-hidden rounded-[3rem] border border-border bg-card shadow-2xl duration-300 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-border bg-muted/20 p-8 md:p-10"
                    >
                        <div>
                            <h2
                                class="text-2xl font-black tracking-tighter text-foreground uppercase italic"
                            >
                                Pengaturan Event
                            </h2>
                            <p
                                class="mt-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                            >
                                Kelola Jenis Kegiatan dan Poin Kejuaraan
                            </p>
                        </div>
                        <button
                            @click="showSettingsModal = false"
                            class="rounded-full bg-white/5 p-3 text-muted-foreground transition-all hover:bg-white/10"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <div
                        class="custom-scrollbar grid max-h-[75vh] grid-cols-1 gap-10 overflow-y-auto p-10 md:grid-cols-2"
                    >
                        <!-- Event Types -->
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="flex items-center gap-2 text-xs font-black tracking-widest uppercase"
                                >
                                    <Flag class="h-4 w-4 text-orange-500" />
                                    Jenis Event
                                </h3>
                            </div>
                            <form @submit.prevent="addType" class="flex gap-2">
                                <Input
                                    v-model="typeForm.name"
                                    class="h-12 rounded-xl border-none bg-muted/30 px-4 text-xs font-bold"
                                    placeholder="Tambah Jenis Event..."
                                />
                                <button
                                    type="submit"
                                    :disabled="typeForm.processing"
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-accent text-white transition-all hover:bg-accent/80"
                                >
                                    <Plus class="h-5 w-5" />
                                </button>
                            </form>
                            <div class="space-y-2">
                                <div
                                    v-for="type in eventTypes"
                                    :key="type.id"
                                    :class="[
                                        !type.coach_id
                                            ? 'border-accent/20 bg-accent/5'
                                            : 'border-border bg-card',
                                    ]"
                                    class="flex items-center justify-between rounded-xl border p-4"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-bold uppercase"
                                            >{{ type.name }}</span
                                        >
                                        <span
                                            v-if="!type.coach_id"
                                            class="rounded bg-accent/20 px-1.5 py-0.5 text-[8px] font-black tracking-widest text-accent uppercase"
                                            >System</span
                                        >
                                    </div>
                                    <button
                                        v-if="type.coach_id"
                                        @click="deleteType(type.id)"
                                        class="text-destructive opacity-40 transition-opacity hover:opacity-100"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Event Points -->
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="flex items-center gap-2 text-xs font-black tracking-widest uppercase"
                                >
                                    <Trophy class="h-4 w-4 text-accent" /> Poin
                                    Kejuaraan
                                </h3>
                            </div>
                            <form @submit.prevent="addPoint" class="flex gap-2">
                                <Input
                                    v-model="pointForm.name"
                                    class="h-12 rounded-xl border-none bg-muted/30 px-4 text-xs font-bold"
                                    placeholder="Tambah Poin/Kategori..."
                                />
                                <button
                                    type="submit"
                                    :disabled="pointForm.processing"
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-accent text-white transition-all hover:bg-accent/80"
                                >
                                    <Plus class="h-5 w-5" />
                                </button>
                            </form>
                            <div class="space-y-2">
                                <div
                                    v-for="point in eventPoints"
                                    :key="point.id"
                                    :class="[
                                        !point.coach_id
                                            ? 'border-accent/20 bg-accent/5'
                                            : 'border-border bg-card',
                                    ]"
                                    class="flex items-center justify-between rounded-xl border p-4"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-bold uppercase"
                                            >{{ point.name }}</span
                                        >
                                        <span
                                            v-if="!point.coach_id"
                                            class="rounded bg-accent/20 px-1.5 py-0.5 text-[8px] font-black tracking-widest text-accent uppercase"
                                            >System</span
                                        >
                                    </div>
                                    <button
                                        v-if="point.coach_id"
                                        @click="deletePoint(point.id)"
                                        class="text-destructive opacity-40 transition-opacity hover:opacity-100"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Modal (Coach View) -->
            <div
                v-if="showDetailModal && selectedEvent"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-2xl"
            >
                <div
                    class="w-full max-w-4xl animate-in overflow-hidden rounded-[3rem] border border-border bg-card shadow-2xl duration-300 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-border bg-muted/20 p-8 md:p-10"
                    >
                        <div class="flex items-center gap-6">
                            <div
                                :class="
                                    getTypeColor(selectedEvent.event_type_id)
                                "
                                class="flex h-20 w-20 items-center justify-center rounded-3xl border shadow-xl"
                            >
                                <Trophy
                                    v-if="
                                        selectedEvent.type?.name
                                            .toLowerCase()
                                            .includes('race')
                                    "
                                    class="h-10 w-10"
                                />
                                <Users v-else class="h-10 w-10" />
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
                                    {{
                                        selectedEvent.location || 'No Location'
                                    }}
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
                        class="custom-scrollbar grid max-h-[70vh] grid-cols-1 gap-10 overflow-y-auto p-10 md:grid-cols-3"
                    >
                        <!-- Left: Details -->
                        <div class="space-y-8 md:col-span-1">
                            <div
                                v-if="selectedEvent.description"
                                class="space-y-3"
                            >
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
                                    Status Kegiatan
                                </h4>
                                <div
                                    :class="
                                        getTypeColor(
                                            selectedEvent.event_type_id,
                                        )
                                    "
                                    class="inline-flex rounded-lg border px-3 py-1 text-[10px] font-black tracking-widest uppercase"
                                >
                                    {{
                                        selectedEvent.type?.name ||
                                        'Uncategorized'
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- Right: Athlete List -->
                        <div class="space-y-6 md:col-span-2">
                            <h4
                                class="flex items-center gap-2 text-xs font-black tracking-widest uppercase"
                            >
                                <Users class="h-4 w-4 text-orange-500" /> Daftar
                                Atlet Berpartisipasi ({{
                                    selectedEvent.athletes.length
                                }})
                            </h4>
                            <div class="grid grid-cols-1 gap-3">
                                <div
                                    v-for="athlete in selectedEvent.athletes"
                                    :key="athlete.id"
                                    class="flex items-center justify-between rounded-2xl border border-border/50 bg-muted/20 p-6 transition-all hover:bg-muted/40"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-card text-xs font-black shadow-sm"
                                        >
                                            {{
                                                athlete.name
                                                    .substring(0, 2)
                                                    .toUpperCase()
                                            }}
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-black uppercase"
                                            >
                                                {{ athlete.name }}
                                            </p>
                                            <p
                                                class="text-[10px] font-bold text-muted-foreground uppercase opacity-60"
                                            >
                                                Kategori:
                                                <span class="text-accent">{{
                                                    getPointName(
                                                        athlete.pivot
                                                            .event_point_id,
                                                    )
                                                }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <div
                                            :class="
                                                getStatusColor(
                                                    athlete.pivot.status,
                                                )
                                            "
                                            class="text-[10px] font-black tracking-widest uppercase"
                                        >
                                            {{ athlete.pivot.status }}
                                        </div>
                                        <div
                                            v-if="athlete.pivot.result"
                                            class="text-[9px] font-bold italic opacity-60"
                                        >
                                            Result: {{ athlete.pivot.result }}
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-if="selectedEvent.athletes.length === 0"
                                    class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-border p-10 opacity-40"
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

select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1rem;
}
</style>
