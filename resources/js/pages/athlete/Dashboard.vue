<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import {
    Activity,
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
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
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
                'rpm',
                'calories',
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
                                Misi Mendatang
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
                                class="group flex items-center gap-4 rounded-xl border border-border bg-muted/30 p-4 transition-all hover:border-accent/40"
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
                class="fixed inset-0 z-50 flex items-end justify-center bg-background/80 p-4 backdrop-blur-sm md:items-center"
            >
                <div
                    class="relative max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-t-[2rem] border border-b-0 border-border bg-card p-8 shadow-2xl md:rounded-[2rem] md:border-b"
                >
                    <button
                        @click="showQuickUpdate = false"
                        class="absolute top-6 right-6 text-muted-foreground hover:text-foreground"
                    >
                        <X class="h-6 w-6" />
                    </button>

                    <div class="mb-8 flex items-center gap-3">
                        <div class="rounded-lg bg-accent/10 p-2">
                            <Zap class="h-6 w-6 text-accent" />
                        </div>
                        <h2
                            class="text-2xl font-black tracking-tight text-foreground uppercase"
                        >
                            Update Powerful
                        </h2>
                    </div>

                    <form @submit.prevent="submitQuickUpdate" class="space-y-8">
                        <!-- Physical Info -->
                        <div class="space-y-4">
                            <h3
                                class="flex items-center gap-2 text-xs font-black tracking-widest text-accent uppercase"
                            >
                                <Scale class="h-3 w-3" /> Data Fisik Terkini
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label
                                        class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                        >Berat (KG)</label
                                    >
                                    <div class="relative">
                                        <input
                                            v-model="form.weight"
                                            type="number"
                                            step="0.1"
                                            class="w-full rounded-xl border-border bg-muted/40 px-4 py-3 text-sm font-bold focus:border-accent focus:ring-accent"
                                            placeholder="--.-"
                                        />
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                        >Tinggi (CM)</label
                                    >
                                    <input
                                        v-model="form.height"
                                        type="number"
                                        class="w-full rounded-xl border-border bg-muted/40 px-4 py-3 text-sm font-bold focus:border-accent focus:ring-accent"
                                        placeholder="---"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Training Log -->
                        <div class="space-y-4">
                            <h3
                                class="flex items-center gap-2 text-xs font-black tracking-widest text-accent uppercase"
                            >
                                <Activity class="h-3 w-3" /> Log Latihan
                                Individual
                            </h3>
                            <div class="space-y-4">
                                <div class="space-y-1.5">
                                    <label
                                        class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                        >Judul Latihan</label
                                    >
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        class="w-full rounded-xl border-border bg-muted/40 px-4 py-3 text-sm font-bold italic focus:border-accent focus:ring-accent"
                                        placeholder="Contoh: Endurance Subuh"
                                    />
                                </div>

                                <div class="space-y-1.5">
                                    <label
                                        class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                        >Jenis Latihan</label
                                    >
                                    <select
                                        v-model="form.exercise_type_id"
                                        class="w-full rounded-xl border-border bg-muted/40 px-4 py-3 text-sm font-bold focus:border-accent focus:ring-accent"
                                    >
                                        <option value="" disabled>
                                            Pilih Jenis Latihan
                                        </option>
                                        <option
                                            v-for="type in exerciseTypes"
                                            :key="type.id"
                                            :value="type.id"
                                        >
                                            {{ type.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label
                                            class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                            >Jarak (KM)</label
                                        >
                                        <input
                                            v-model="form.distance_km"
                                            type="number"
                                            step="0.01"
                                            class="w-full rounded-xl border-border bg-muted/40 px-4 py-3 text-sm font-bold focus:border-accent focus:ring-accent"
                                            placeholder="00.00"
                                        />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label
                                            class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                            >Waktu (Menit)</label
                                        >
                                        <input
                                            v-model="form.duration_minutes"
                                            type="number"
                                            class="w-full rounded-xl border-border bg-muted/40 px-4 py-3 text-sm font-bold focus:border-accent focus:ring-accent"
                                            placeholder="00"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-3 text-center">
                                    <div
                                        class="rounded-xl border border-border bg-muted/20 p-3"
                                    >
                                        <label
                                            class="mb-1 block text-[8px] font-black text-muted-foreground uppercase"
                                            >HR</label
                                        >
                                        <input
                                            v-model="form.avg_heart_rate"
                                            type="number"
                                            class="w-full border-none bg-transparent p-0 text-center text-sm font-black text-accent focus:ring-0"
                                            placeholder="0"
                                        />
                                    </div>
                                    <div
                                        class="rounded-xl border border-border bg-muted/20 p-3"
                                    >
                                        <label
                                            class="mb-1 block text-[8px] font-black text-muted-foreground uppercase"
                                            >Cad.</label
                                        >
                                        <input
                                            v-model="form.rpm"
                                            type="number"
                                            class="w-full border-none bg-transparent p-0 text-center text-sm font-black text-accent focus:ring-0"
                                            placeholder="0"
                                        />
                                    </div>
                                    <div
                                        class="rounded-xl border border-border bg-muted/20 p-3"
                                    >
                                        <label
                                            class="mb-1 block text-[8px] font-black text-muted-foreground uppercase"
                                            >Kcal</label
                                        >
                                        <input
                                            v-model="form.calories"
                                            type="number"
                                            class="w-full border-none bg-transparent p-0 text-center text-sm font-black text-accent focus:ring-0"
                                            placeholder="0"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center gap-3 rounded-2xl bg-accent py-5 font-black tracking-[0.2em] text-accent-foreground uppercase shadow-xl shadow-accent/30 transition-all hover:bg-accent/90 active:scale-95 disabled:opacity-50"
                        >
                            <span
                                v-if="form.processing"
                                class="h-5 w-5 animate-spin rounded-full border-2 border-accent-foreground border-t-transparent"
                            ></span>
                            <span v-else>Simpan Performa</span>
                        </button>
                    </form>
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
