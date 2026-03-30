<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
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
    X 
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
            form.reset('title', 'distance_km', 'duration_minutes', 'avg_speed', 'rpm', 'calories', 'notes');
        },
    });
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
            stops: [20, 100]
        }
    },
    dataLabels: { enabled: false },
    grid: {
        borderColor: 'rgba(255,255,255,0.05)',
        strokeDashArray: 4,
    },
    xaxis: {
        categories: props.performanceTrend.map(log => {
            const d = new Date(log.date);

            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        }),
        labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 } }
    },
    yaxis: {
        labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 } }
    },
    tooltip: { theme: 'dark' },
}));

const chartSeries = [
    {
        name: 'Jarak (km)',
        data: props.performanceTrend.map(log => parseFloat(log.distance_km))
    }
];

const breadcrumbs = [{ title: 'Dashboard', href: athlete.dashboard().url }];
</script>

<template>
    <Head title="Athlete Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen space-y-6 p-4 pb-24 md:p-8">
            <!-- Welcome Header -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground uppercase md:text-3xl">
                        Selamat Datang, <span class="text-accent">{{ user.name }}</span>
                    </h1>
                    <p class="text-sm font-bold text-muted-foreground opacity-70">
                        Level: <span class="text-secondary-foreground">{{ user.category?.name || 'Uncategorized' }}</span> 
                        • Fokus hari ini: Stay Hydrated & Consistent.
                    </p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <!-- Physical Metric -->
                <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-lg group">
                    <div class="absolute -right-4 -top-4 h-16 w-16 text-accent/5 transition-transform group-hover:scale-110">
                        <Scale class="h-full w-full" />
                    </div>
                    <p class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60">Status Fisik</p>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-3xl font-black text-foreground">{{ user.latest_physical_metric?.weight || '--' }}</span>
                        <span class="text-xs font-bold text-muted-foreground italic">KG</span>
                    </div>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-emerald-500 uppercase">{{ user.latest_physical_metric?.bmi_status || 'Data Kosong' }}</span>
                    </div>
                </div>

                <!-- Weekly Distance -->
                <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-lg group">
                    <div class="absolute -right-4 -top-4 h-16 w-16 text-accent/5 transition-transform group-hover:scale-110">
                        <Navigation class="h-full w-full" />
                    </div>
                    <p class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60">Jarak Minggu Ini</p>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-3xl font-black text-foreground">{{ weeklyStats.distance }}</span>
                        <span class="text-xs font-bold text-muted-foreground italic">KM</span>
                    </div>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-accent uppercase">{{ weeklyStats.count }} Sesi</span>
                    </div>
                </div>

                <!-- Weekly Time -->
                <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-lg group">
                    <div class="absolute -right-4 -top-4 h-16 w-16 text-accent/5 transition-transform group-hover:scale-110">
                        <Clock class="h-full w-full" />
                    </div>
                    <p class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60">Waktu Latihan</p>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-3xl font-black text-foreground">{{ formatDuration(weeklyStats.duration) }}</span>
                    </div>
                    <div class="mt-1 flex items-center gap-1.5 text-muted-foreground font-bold text-[10px] uppercase">
                        Siklus 7 Hari
                    </div>
                </div>

                <!-- Weekly Calories -->
                <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-lg group">
                    <div class="absolute -right-4 -top-4 h-16 w-16 text-accent/5 transition-transform group-hover:scale-110">
                        <Flame class="h-full w-full" />
                    </div>
                    <p class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60">Kalori Terbakar</p>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-3xl font-black text-foreground">{{ weeklyStats.calories }}</span>
                        <span class="text-xs font-bold text-muted-foreground italic">KCAL</span>
                    </div>
                    <div class="mt-1 flex items-center gap-1.5 text-accent font-bold text-[10px] uppercase">
                        Active Burn
                    </div>
                </div>
            </div>

            <!-- Content Grid: Charts & Missions -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Performance Chart -->
                <div class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-xl lg:col-span-8">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <TrendingUp class="h-5 w-5 text-accent" />
                            <h2 class="text-lg font-black tracking-tight text-foreground uppercase">Tren Performa</h2>
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

                <!-- Upcoming Missions -->
                <div class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-xl lg:col-span-4">
                    <div class="mb-6 flex items-center gap-2">
                        <Trophy class="h-5 w-5 text-accent" />
                        <h2 class="text-lg font-black tracking-tight text-foreground uppercase">Misi Mendatang</h2>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div v-if="upcomingEvents.length === 0" class="flex flex-col items-center justify-center py-10 opacity-50">
                            <Info class="h-8 w-8 mb-2" />
                            <p class="text-xs font-bold uppercase tracking-widest">Belum ada event</p>
                        </div>
                        <div 
                            v-for="event in upcomingEvents" 
                            :key="event.id"
                            class="group flex items-center gap-4 rounded-xl border border-border bg-muted/30 p-4 transition-all hover:border-accent/40"
                        >
                            <div class="flex h-12 w-12 flex-col items-center justify-center rounded-lg bg-accent text-[10px] font-black text-accent-foreground uppercase leading-tight">
                                <span>{{ new Date(event.event_date).toLocaleDateString('id-ID', { month: 'short' }) }}</span>
                                <span class="text-lg">{{ new Date(event.event_date).getDate() }}</span>
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <h4 class="truncate text-xs font-black text-foreground uppercase group-hover:text-accent transition-colors">
                                    {{ event.title }}
                                </h4>
                                <div class="mt-1 flex items-center gap-2 text-[10px] font-bold text-muted-foreground uppercase opacity-70">
                                    <MapPin class="h-3 w-3" />
                                    <span>{{ event.location || 'Lokasi TBA' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="mt-4 w-full rounded-xl border border-border bg-muted/20 py-3 text-[10px] font-black tracking-widest text-muted-foreground uppercase transition-all hover:text-accent">
                        Lihat Semua Event
                    </button>
                </div>
            </div>

            <!-- Floating Quick Update Button -->
            <button 
                @click="showQuickUpdate = true"
                class="fixed bottom-24 right-6 flex h-14 w-14 items-center justify-center rounded-full bg-accent text-accent-foreground shadow-2xl shadow-accent/40 transition-transform active:scale-90 md:relative md:bottom-0 md:right-0 md:h-auto md:w-full md:rounded-2xl md:py-6 md:px-8 md:text-sm font-black uppercase tracking-widest gap-3"
            >
                <Zap class="h-6 w-6 animate-pulse" />
                <span class="hidden md:inline">Quick Update Fisik & Latihan</span>
            </button>

            <!-- Quick Update Modal -->
            <div 
                v-if="showQuickUpdate" 
                class="fixed inset-0 z-50 flex items-end justify-center bg-background/80 backdrop-blur-sm p-4 md:items-center"
            >
                <div class="w-full max-w-xl rounded-t-[2rem] bg-card p-8 border border-border shadow-2xl md:rounded-[2rem] max-h-[90vh] overflow-y-auto relative border-b-0 md:border-b">
                    <button @click="showQuickUpdate = false" class="absolute top-6 right-6 text-muted-foreground hover:text-foreground">
                        <X class="h-6 w-6" />
                    </button>

                    <div class="flex items-center gap-3 mb-8">
                        <div class="bg-accent/10 p-2 rounded-lg">
                            <Zap class="h-6 w-6 text-accent" />
                        </div>
                        <h2 class="text-2xl font-black tracking-tight text-foreground uppercase">Update Powerful</h2>
                    </div>

                    <form @submit.prevent="submitQuickUpdate" class="space-y-8">
                        <!-- Physical Info -->
                        <div class="space-y-4">
                            <h3 class="text-xs font-black tracking-widest text-accent uppercase flex items-center gap-2">
                                <Scale class="h-3 w-3" /> Data Fisik Terkini
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black uppercase text-muted-foreground tracking-widest">Berat (KG)</label>
                                    <div class="relative">
                                        <input 
                                            v-model="form.weight" 
                                            type="number" 
                                            step="0.1" 
                                            class="w-full bg-muted/40 border-border rounded-xl px-4 py-3 text-sm font-bold focus:ring-accent focus:border-accent"
                                            placeholder="--.-"
                                        />
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black uppercase text-muted-foreground tracking-widest">Tinggi (CM)</label>
                                    <input 
                                        v-model="form.height" 
                                        type="number" 
                                        class="w-full bg-muted/40 border-border rounded-xl px-4 py-3 text-sm font-bold focus:ring-accent focus:border-accent"
                                        placeholder="---"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Training Log -->
                        <div class="space-y-4">
                            <h3 class="text-xs font-black tracking-widest text-accent uppercase flex items-center gap-2">
                                <Activity class="h-3 w-3" /> Log Latihan Individual
                            </h3>
                            <div class="space-y-4">
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black uppercase text-muted-foreground tracking-widest">Judul Latihan</label>
                                    <input 
                                        v-model="form.title" 
                                        type="text" 
                                        class="w-full bg-muted/40 border-border rounded-xl px-4 py-3 text-sm font-bold focus:ring-accent focus:border-accent italic"
                                        placeholder="Contoh: Endurance Subuh"
                                    />
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black uppercase text-muted-foreground tracking-widest">Jenis Latihan</label>
                                    <select 
                                        v-model="form.exercise_type_id" 
                                        class="w-full bg-muted/40 border-border rounded-xl px-4 py-3 text-sm font-bold focus:ring-accent focus:border-accent"
                                    >
                                        <option value="" disabled>Pilih Jenis Latihan</option>
                                        <option v-for="type in exerciseTypes" :key="type.id" :value="type.id">
                                            {{ type.name }}
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="text-[9px] font-black uppercase text-muted-foreground tracking-widest">Jarak (KM)</label>
                                        <input 
                                            v-model="form.distance_km" 
                                            type="number" 
                                            step="0.01" 
                                            class="w-full bg-muted/40 border-border rounded-xl px-4 py-3 text-sm font-bold focus:ring-accent focus:border-accent"
                                            placeholder="00.00"
                                        />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[9px] font-black uppercase text-muted-foreground tracking-widest">Waktu (Menit)</label>
                                        <input 
                                            v-model="form.duration_minutes" 
                                            type="number" 
                                            class="w-full bg-muted/40 border-border rounded-xl px-4 py-3 text-sm font-bold focus:ring-accent focus:border-accent"
                                            placeholder="00"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-3 text-center">
                                    <div class="bg-muted/20 p-3 rounded-xl border border-border">
                                        <label class="block text-[8px] font-black uppercase text-muted-foreground mb-1">HR</label>
                                        <input v-model="form.avg_heart_rate" type="number" class="w-full bg-transparent border-none p-0 text-center text-sm font-black text-accent focus:ring-0" placeholder="0" />
                                    </div>
                                    <div class="bg-muted/20 p-3 rounded-xl border border-border">
                                        <label class="block text-[8px] font-black uppercase text-muted-foreground mb-1">Cad.</label>
                                        <input v-model="form.rpm" type="number" class="w-full bg-transparent border-none p-0 text-center text-sm font-black text-accent focus:ring-0" placeholder="0" />
                                    </div>
                                    <div class="bg-muted/20 p-3 rounded-xl border border-border">
                                        <label class="block text-[8px] font-black uppercase text-muted-foreground mb-1">Kcal</label>
                                        <input v-model="form.calories" type="number" class="w-full bg-transparent border-none p-0 text-center text-sm font-black text-accent focus:ring-0" placeholder="0" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full bg-accent text-accent-foreground py-5 rounded-2xl font-black uppercase tracking-[0.2em] shadow-xl shadow-accent/30 hover:bg-accent/90 transition-all flex items-center justify-center gap-3 active:scale-95 disabled:opacity-50"
                        >
                            <span v-if="form.processing" class="animate-spin h-5 w-5 border-2 border-accent-foreground border-t-transparent rounded-full"></span>
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
