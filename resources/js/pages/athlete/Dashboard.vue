<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import {
    Clock,
    Flame,
    MapPin,
    Scale,
    TrendingUp,
    Trophy,
    Zap,
    Navigation,
    Info,
    X,
    MessageSquare,
    CheckCircle2,
    Calendar,
    Users,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

import CustomSelect from '@/components/ui/CustomSelect.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import athlete from '@/routes/athlete';

interface Props {
    user: any;
    weeklyStats: {
        distance: number;
        duration: number;
        calories: number;
        count: number;
    };
    upcomingEvents: any[];
    performanceTrend: any[];
    exerciseTypes: any[];
    recentMessages: any[];
}

const props = defineProps<Props>();

const showQuickUpdate = ref(false);

const form = useForm({
    weight: props.user.latest_physical_metric?.weight || '',
    height: props.user.latest_physical_metric?.height || '',
    title: '',
    exercise_type_id: '',
    distance_km: '',
    duration_minutes: '',
    avg_speed: '',
    avg_heart_rate: '',
    rpm: '',
    calories: '',
    elevation_m: '',
    temperature_c: '',
    intensity: 'medium',
    notes: '',
});

const submitQuickUpdate = () => {
    form.post(athlete.dashboard.quickUpdate().url, {
        onSuccess: () => {
            showQuickUpdate.value = false;
            form.reset(
                'title',
                'distance_km',
                'duration_minutes',
                'avg_speed',
                'avg_heart_rate',
                'rpm',
                'calories',
                'elevation_m',
                'temperature_c',
                'intensity',
                'notes',
            );
        },
    });
};

const markMessageRead = (id: number) => {
    router.patch(
        athlete.messages.read({ message: id }).url,
        {},
        {
            preserveScroll: true,
        },
    );
};

const formatDuration = (minutes: number) => {
    const hrs = Math.floor(minutes / 60);
    const mins = minutes % 60;

    return hrs > 0 ? `${hrs}j ${mins}m` : `${mins}m`;
};

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'area' as const,
        toolbar: { show: false },
        animations: { enabled: true },
        background: 'transparent',
    },
    colors: ['#FF6120'],
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
    dataLabels: { enabled: false },
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
        name: 'Jarak (km)',
        data: props.performanceTrend.map((log) => parseFloat(log.distance_km)),
    },
];

const breadcrumbs = [{ title: 'Dashboard', href: athlete.dashboard().url }];

const exerciseTypeOptions = computed(() =>
    props.exerciseTypes.map((type) => ({
        value: type.id.toString(),
        label: type.name,
    })),
);

const showDetailModal = ref(false);
const selectedEvent = ref<any>(null);

const openDetail = (event: any) => {
    selectedEvent.value = event;
    showDetailModal.value = true;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
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
</script>

<template>
    <Head title="Athlete Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen space-y-6 p-4 pb-24 md:p-8">
            <!-- Welcome Header -->
            <div
                class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-black tracking-tight text-foreground uppercase md:text-3xl"
                    >
                        Selamat Datang,
                        <span class="text-accent">{{ user.name }}</span>
                    </h1>
                    <p
                        class="text-sm font-bold text-muted-foreground opacity-70"
                    >
                        Level:
                        <span class="text-secondary-foreground">{{
                            user.category?.name || 'Uncategorized'
                        }}</span>
                        • Fokus hari ini: Stay Hydrated & Consistent.
                    </p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <!-- Physical Metric -->
                <div
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-lg"
                >
                    <div
                        class="absolute -top-4 -right-4 h-16 w-16 text-accent/5 transition-transform group-hover:scale-110"
                    >
                        <Scale class="h-full w-full" />
                    </div>
                    <p
                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >
                        Status Fisik
                    </p>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-3xl font-black text-foreground">{{
                            user.latest_physical_metric?.weight || '--'
                        }}</span>
                        <span
                            class="text-xs font-bold text-muted-foreground italic"
                            >KG</span
                        >
                    </div>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span
                            class="text-[10px] font-bold text-emerald-500 uppercase"
                            >{{
                                user.latest_physical_metric?.bmi_status ||
                                'Data Kosong'
                            }}</span
                        >
                    </div>
                </div>

                <!-- Weekly Distance -->
                <div
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-lg"
                >
                    <div
                        class="absolute -top-4 -right-4 h-16 w-16 text-accent/5 transition-transform group-hover:scale-110"
                    >
                        <Navigation class="h-full w-full" />
                    </div>
                    <p
                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >
                        Jarak Minggu Ini
                    </p>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-3xl font-black text-foreground">{{
                            weeklyStats.distance
                        }}</span>
                        <span
                            class="text-xs font-bold text-muted-foreground italic"
                            >KM</span
                        >
                    </div>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span
                            class="text-[10px] font-bold text-accent uppercase"
                            >{{ weeklyStats.count }} Sesi</span
                        >
                    </div>
                </div>

                <!-- Weekly Time -->
                <div
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-lg"
                >
                    <div
                        class="absolute -top-4 -right-4 h-16 w-16 text-accent/5 transition-transform group-hover:scale-110"
                    >
                        <Clock class="h-full w-full" />
                    </div>
                    <p
                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >
                        Waktu Latihan
                    </p>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-3xl font-black text-foreground">{{
                            formatDuration(weeklyStats.duration)
                        }}</span>
                    </div>
                    <div
                        class="mt-1 flex items-center gap-1.5 text-[10px] font-bold text-muted-foreground uppercase"
                    >
                        Siklus 7 Hari
                    </div>
                </div>

                <!-- Weekly Calories -->
                <div
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-lg"
                >
                    <div
                        class="absolute -top-4 -right-4 h-16 w-16 text-accent/5 transition-transform group-hover:scale-110"
                    >
                        <Flame class="h-full w-full" />
                    </div>
                    <p
                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >
                        Kalori Terbakar
                    </p>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-3xl font-black text-foreground">{{
                            weeklyStats.calories
                        }}</span>
                        <span
                            class="text-xs font-bold text-muted-foreground italic"
                            >KCAL</span
                        >
                    </div>
                    <div
                        class="mt-1 flex items-center gap-1.5 text-[10px] font-bold text-accent uppercase"
                    >
                        Active Burn
                    </div>
                </div>
            </div>

            <!-- Content Grid: Charts & Missions -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Performance Chart -->
                <div
                    class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-xl lg:col-span-8"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <TrendingUp class="h-5 w-5 text-accent" />
                            <h2
                                class="text-lg font-black tracking-tight text-foreground uppercase"
                            >
                                Tren Performa
                            </h2>
                        </div>
                    </div>
                    <div class="h-[300px] w-full">
                        <VueApexCharts
                            :options="chartOptions"
                            :series="chartSeries"
                            type="area"
                            height="100%"
                        />
                    </div>
                </div>

                <!-- Right Column: Missions & Messages -->
                <div class="flex flex-col gap-6 lg:col-span-4">
                    <!-- Upcoming Missions -->
                    <div
                        class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-xl"
                    >
                        <div class="mb-6 flex items-center gap-2">
                            <Trophy class="h-5 w-5 text-accent" />
                            <h2
                                class="text-lg font-black tracking-tight text-foreground uppercase"
                            >
                                Event Mendatang
                            </h2>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div
                                v-if="upcomingEvents.length === 0"
                                class="flex flex-col items-center justify-center py-10 opacity-50"
                            >
                                <Info class="mb-2 h-8 w-8" />
                                <p
                                    class="text-xs font-bold tracking-widest uppercase"
                                >
                                    Belum ada event
                                </p>
                            </div>
                            <div
                                v-for="event in upcomingEvents"
                                :key="event.id"
                                @click="openDetail(event)"
                                class="group flex cursor-pointer items-center gap-4 rounded-xl border border-border bg-muted/30 p-4 transition-all hover:border-accent/40 hover:bg-muted/50"
                            >
                                <div
                                    class="flex h-12 w-12 flex-col items-center justify-center rounded-lg bg-accent text-[10px] leading-tight font-black text-accent-foreground uppercase"
                                >
                                    <span>{{
                                        new Date(
                                            event.event_date,
                                        ).toLocaleDateString('id-ID', {
                                            month: 'short',
                                        })
                                    }}</span>
                                    <span class="text-lg">{{
                                        new Date(event.event_date).getDate()
                                    }}</span>
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <h4
                                        class="truncate text-xs font-black text-foreground uppercase transition-colors group-hover:text-accent"
                                    >
                                        {{ event.title }}
                                    </h4>
                                    <div
                                        class="mt-1 flex items-center gap-2 text-[10px] font-bold text-muted-foreground uppercase opacity-70"
                                    >
                                        <MapPin class="h-3 w-3" />
                                        <span>{{
                                            event.location || 'Lokasi TBA'
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coach Messages -->
                    <div
                        class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-xl"
                    >
                        <div class="mb-6 flex items-center gap-2">
                            <MessageSquare class="h-5 w-5 text-accent" />
                            <h2
                                class="text-lg font-black tracking-tight text-foreground uppercase"
                            >
                                Catatan Pelatih
                            </h2>
                        </div>
                        <div class="flex flex-col gap-3">
                            <div
                                v-if="recentMessages.length === 0"
                                class="flex flex-col items-center justify-center py-8 opacity-50"
                            >
                                <MessageSquare
                                    class="mb-2 h-6 w-6 text-muted-foreground"
                                />
                                <p
                                    class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase"
                                >
                                    Belum ada pesan
                                </p>
                            </div>
                            <div
                                v-for="msg in recentMessages"
                                :key="msg.id"
                                :class="[
                                    'relative rounded-xl border p-4 transition-all',
                                    msg.is_read
                                        ? 'border-border bg-muted/20'
                                        : 'border-accent/30 bg-accent/5 shadow-[0_0_15px_rgba(255,97,32,0.1)]',
                                ]"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <span
                                        class="text-xs font-black tracking-widest text-foreground uppercase"
                                        >{{ msg.sender?.name }}</span
                                    >
                                    <span
                                        class="text-[9px] font-bold text-muted-foreground uppercase"
                                        >{{
                                            new Date(
                                                msg.created_at,
                                            ).toLocaleDateString('id-ID')
                                        }}</span
                                    >
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ msg.content }}
                                </p>
                                <div class="mt-3 flex justify-end">
                                    <button
                                        v-if="!msg.is_read"
                                        @click="markMessageRead(msg.id)"
                                        class="flex items-center gap-1 text-[10px] font-black tracking-widest text-accent uppercase transition-opacity hover:opacity-80"
                                    >
                                        <CheckCircle2 class="h-3 w-3" /> Tandai
                                        Dibaca
                                    </button>
                                    <span
                                        v-else
                                        class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-50"
                                        ><CheckCircle2
                                            class="mr-0.5 inline h-3 w-3"
                                        />
                                        Terbaca</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Quick Update Button -->
            <button
                @click="showQuickUpdate = true"
                class="fixed right-6 bottom-24 flex h-14 w-14 items-center justify-center gap-3 rounded-full bg-accent font-black tracking-widest text-accent-foreground uppercase shadow-2xl shadow-accent/40 transition-transform active:scale-90 md:relative md:right-0 md:bottom-0 md:h-auto md:w-full md:rounded-2xl md:px-8 md:py-6 md:text-sm"
            >
                <Zap class="h-6 w-6 animate-pulse" />
                <span class="hidden md:inline"
                    >Quick Update Fisik & Latihan</span
                >
            </button>

            <!-- Quick Update Modal -->
            <div
                v-if="showQuickUpdate"
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
                                    Quick Update
                                </h2>
                                <p
                                    class="mt-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                                >
                                    Update cepat performa latihan harian
                                </p>
                            </div>
                        </div>
                        <button
                            @click="showQuickUpdate = false"
                            class="rounded-full p-3 text-muted-foreground transition-all hover:bg-muted/50"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <div
                        class="custom-scrollbar max-h-[70vh] overflow-y-auto p-11"
                    >
                        <form
                            @submit.prevent="submitQuickUpdate"
                            class="flex flex-col gap-10"
                        >
                            <div class="flex flex-col gap-6">
                                <p
                                    class="text-[10px] font-black tracking-[0.3em] text-accent uppercase opacity-80"
                                >
                                    General Infomation
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
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black text-foreground dark:[color-scheme:dark]"
                                            placeholder="E.g. Morning Sprint"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Tanggal</Label
                                        >
                                        <div
                                            class="flex h-14 cursor-not-allowed items-center justify-between rounded-2xl border-none bg-muted/30 px-6 opacity-80"
                                        >
                                            <span
                                                class="text-sm font-black text-white/50"
                                                >Otomatis (Saat Ini)</span
                                            >
                                            <X class="h-4 w-4 text-white/30" />
                                        </div>
                                    </div>
                                    <div
                                        class="col-span-1 flex flex-col gap-2 md:col-span-2"
                                    >
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Jenis Latihan</Label
                                        >
                                        <CustomSelect
                                            v-model="form.exercise_type_id"
                                            :options="exerciseTypeOptions"
                                            placeholder="Pilih Jenis Latihan"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-6">
                                <p
                                    class="text-[10px] font-black tracking-[0.3em] text-blue-500 uppercase opacity-80"
                                >
                                    Data Fisik Terkini
                                </p>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Berat Badan (KG)</Label
                                        >
                                        <Input
                                            v-model="form.weight"
                                            type="number"
                                            step="0.1"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black text-foreground dark:[color-scheme:dark]"
                                            placeholder="70"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Tinggi Badan (CM)</Label
                                        >
                                        <Input
                                            v-model="form.height"
                                            type="number"
                                            step="1"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black text-foreground dark:[color-scheme:dark]"
                                            placeholder="175"
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
                                            >Distance / Jarak (KM)</Label
                                        >
                                        <Input
                                            v-model="form.distance_km"
                                            type="number"
                                            step="0.1"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black text-foreground dark:[color-scheme:dark]"
                                            placeholder="30.5"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Duration / Waktu (Min)</Label
                                        >
                                        <Input
                                            v-model="form.duration_minutes"
                                            type="number"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black text-foreground dark:[color-scheme:dark]"
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
                                            >Avg RPM</Label
                                        >
                                        <Input
                                            v-model="form.rpm"
                                            type="number"
                                            step="0.1"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black text-foreground dark:[color-scheme:dark]"
                                            placeholder="90"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Avg Heart Rate</Label
                                        >
                                        <Input
                                            v-model="form.avg_heart_rate"
                                            type="number"
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black text-foreground dark:[color-scheme:dark]"
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
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black text-foreground dark:[color-scheme:dark]"
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
                                            class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black text-foreground dark:[color-scheme:dark]"
                                            placeholder="28"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-6">
                                <p
                                    class="text-[10px] font-black tracking-[0.3em] text-muted-foreground uppercase opacity-80"
                                >
                                    Personal Notes
                                </p>
                                <div class="flex flex-col gap-6">
                                    <div class="flex flex-col gap-2">
                                        <Label
                                            class="text-[10px] font-black uppercase opacity-60"
                                            >Athlete Notes</Label
                                        >
                                        <textarea
                                            v-model="form.notes"
                                            rows="4"
                                            class="w-full rounded-2xl border-none bg-muted/30 p-6 text-sm font-medium outline-none focus:ring-2 focus:ring-accent"
                                            placeholder="How did you feel today? Any mechanical issues?"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex items-center justify-center gap-2 rounded-[2rem] bg-accent py-6 text-xs font-black tracking-[0.3em] text-white uppercase shadow-2xl shadow-accent/40 transition-all hover:scale-[1.02] hover:bg-accent/90 active:scale-[0.98] disabled:opacity-50"
                            >
                                <span
                                    v-if="form.processing"
                                    class="h-5 w-5 animate-spin rounded-full border-2 border-accent-foreground border-t-transparent"
                                ></span>
                                <span>{{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Quick Update Data'
                                }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Event Detail Modal -->
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
                                <Trophy class="h-10 w-10 text-accent" />
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
                                    {{ selectedEvent.location || 'Lokasi TBA' }}
                                </p>
                            </div>
                        </div>
                        <button
                            @click="showDetailModal = false"
                            class="rounded-full bg-muted/30 p-3 text-muted-foreground transition-all hover:bg-muted/50"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <div
                        class="grid max-h-[70vh] grid-cols-1 gap-10 overflow-y-auto p-10 md:grid-cols-3"
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
                                    Penanggung Jawab
                                </h4>
                                <p class="text-sm font-black text-foreground">
                                    Coach:
                                    {{ selectedEvent.coach?.name || 'TBA' }}
                                </p>
                            </div>

                            <div class="space-y-3">
                                <h4
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                >
                                    Status Keikutsertaan
                                </h4>
                                <div class="flex flex-col gap-2">
                                    <p
                                        v-for="participation in selectedEvent.participants.filter(
                                            (p: any) => p.user_id === user.id,
                                        )"
                                        :key="participation.id"
                                        class="text-sm font-black uppercase"
                                        :class="
                                            getStatusColor(participation.status)
                                        "
                                    >
                                        {{ participation.status }}
                                    </p>
                                    <p
                                        v-for="participation in selectedEvent.participants.filter(
                                            (p: any) => p.user_id === user.id,
                                        )"
                                        :key="'point-' + participation.id"
                                        class="text-xs font-bold text-muted-foreground uppercase opacity-60"
                                    >
                                        Kategori Poin:
                                        <span class="text-accent">{{
                                            participation.point?.name || '-'
                                        }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Participant List -->
                        <div class="space-y-6 md:col-span-2">
                            <h4
                                class="flex items-center gap-2 text-xs font-black tracking-widest uppercase"
                            >
                                <Users class="h-4 w-4 text-orange-500" />
                                Peserta Lain ({{
                                    selectedEvent.participants?.length || 0
                                }})
                            </h4>
                            <div class="grid grid-cols-1 gap-3">
                                <div
                                    v-for="participation in selectedEvent.participants"
                                    :key="participation.id"
                                    class="flex items-center justify-between rounded-2xl border border-border bg-muted/10 p-4 transition-all hover:bg-muted/20"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl border border-border bg-secondary"
                                        >
                                            <img
                                                v-if="
                                                    participation.user?.avatar
                                                "
                                                :src="participation.user.avatar"
                                                class="h-full w-full object-cover"
                                            />
                                            <span
                                                v-else
                                                class="text-[10px] font-black uppercase"
                                            >
                                                {{
                                                    participation.user?.name.substring(
                                                        0,
                                                        2,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-black uppercase"
                                            >
                                                {{ participation.user?.name }}
                                                <span
                                                    v-if="
                                                        participation.user_id ===
                                                        user.id
                                                    "
                                                    class="ml-2 text-[8px] text-accent"
                                                    >(Anda)</span
                                                >
                                            </p>
                                            <p
                                                class="text-[8px] font-bold text-muted-foreground uppercase opacity-60"
                                            >
                                                Kategori:
                                                {{
                                                    participation.point?.name ||
                                                    'General'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        :class="
                                            getStatusColor(participation.status)
                                        "
                                        class="text-[8px] font-black tracking-widest uppercase"
                                    >
                                        {{ participation.status }}
                                    </div>
                                </div>
                                <div
                                    v-if="!selectedEvent.participants?.length"
                                    class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-border p-10 opacity-40"
                                >
                                    <Users class="mb-2 h-8 w-8" />
                                    <p
                                        class="text-[10px] font-black tracking-widest uppercase italic"
                                    >
                                        Belum ada peserta terdaftar
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
/* Kinetic Archive specific styles */
.rounded-2xl {
    clip-path: polygon(
        0% 0%,
        100% 0%,
        100% 100%,
        10px 100%,
        0% calc(100% - 10px)
    );
}

.text-accent {
    text-shadow: 0 0 10px rgba(255, 97, 32, 0.3);
}

/* Custom scrollbar for quick update modal */
div::-webkit-scrollbar {
    width: 4px;
}
div::-webkit-scrollbar-track {
    background: transparent;
}
div::-webkit-scrollbar-thumb {
    background: rgba(255, 97, 32, 0.2);
    border-radius: 10px;
}
</style>
