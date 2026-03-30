<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { BarChart3, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';

interface Athlete {
    id: number;
    name: string;
    email: string;
}
interface ComparisonItem {
    athlete_id: number;
    total_distance_km: number;
    avg_speed: number;
    total_duration_minutes: number;
    total_sessions: number;
}

defineProps<{ athletes: Athlete[] }>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Perbandingan Performa', href: '/coach/performance-comparison' },
];

const selectedAthletes = ref<number[]>([]);
const loading = ref(false);
const comparisonData = ref<ComparisonItem[]>([]);
const athleteMap = ref<Record<number, string>>({});

const toggleAthlete = (id: number) => {
    const idx = selectedAthletes.value.indexOf(id);

    if (idx > -1) {
        selectedAthletes.value.splice(idx, 1);
    } else {
        selectedAthletes.value.push(id);
    }
};

const fetchComparison = async () => {
    if (selectedAthletes.value.length < 2) {
        return;
    }

    loading.value = true;

    try {
        const res = await axios.get('/coach/performance-comparison/data', {
            params: { athlete_ids: selectedAthletes.value },
        });
        comparisonData.value = res.data.comparison;
        const athletes: Record<number, string> = {};
        Object.entries(res.data.athletes).forEach(([id, a]: [string, any]) => {
            athletes[Number(id)] = a.name;
        });
        athleteMap.value = athletes;
    } catch (e) {
        console.error(e);
    }

    loading.value = false;
};

const maxVal = (field: string) => {
    if (comparisonData.value.length === 0) {
        return 1;
    }

    return Math.max(...comparisonData.value.map((d: any) => d[field] || 1));
};
</script>

<template>
    <Head title="Perbandingan Performa" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <!-- Header -->
            <div class="flex items-center gap-4 border-b border-border pb-6">
                <div class="rounded-xl bg-secondary p-3 text-accent">
                    <BarChart3 class="h-6 w-6" />
                </div>
                <div>
                    <h1
                        class="text-3xl font-black tracking-tighter text-foreground uppercase md:text-4xl"
                    >
                        Perbandingan Performa
                    </h1>
                    <p
                        class="mt-1 text-xs font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                    >
                        Bandingkan performa antar atlet
                    </p>
                </div>
            </div>

            <!-- Athlete Selection -->
            <div class="rounded-3xl border border-border bg-card p-8 shadow-xl">
                <p
                    class="mb-4 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                >
                    Pilih Atlet (min. 2)
                </p>
                <div class="flex flex-wrap gap-3">
                    <button
                        v-for="athlete in athletes"
                        :key="athlete.id"
                        @click="toggleAthlete(athlete.id)"
                        class="rounded-xl border px-5 py-3 text-xs font-bold transition-all"
                        :class="
                            selectedAthletes.includes(athlete.id)
                                ? 'border-accent bg-accent/10 text-accent'
                                : 'border-border bg-muted/20 text-muted-foreground hover:border-accent/30'
                        "
                    >
                        {{ athlete.name }}
                    </button>
                </div>
                <button
                    @click="fetchComparison"
                    :disabled="selectedAthletes.length < 2 || loading"
                    class="mt-6 flex items-center gap-2 rounded-xl bg-accent px-8 py-3 text-xs font-black tracking-widest text-white uppercase shadow-lg shadow-accent/20 transition-all hover:bg-accent/90 disabled:opacity-50"
                >
                    <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
                    {{ loading ? 'Memuat...' : 'Bandingkan' }}
                </button>
            </div>

            <!-- Results -->
            <div v-if="comparisonData.length > 0" class="flex flex-col gap-8">
                <!-- Comparison Cards -->
                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        v-for="item in comparisonData"
                        :key="item.athlete_id"
                        class="rounded-3xl border border-border bg-card p-8 shadow-xl"
                    >
                        <div class="mb-4 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-accent/10 text-xs font-black text-accent"
                            >
                                {{
                                    (athleteMap[item.athlete_id] || '')
                                        .split(' ')
                                        .map((n) => n[0])
                                        .slice(0, 2)
                                        .join('')
                                }}
                            </div>
                            <h4 class="font-bold text-foreground">
                                {{ athleteMap[item.athlete_id] }}
                            </h4>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div>
                                <p class="text-2xl font-black text-foreground">
                                    {{ item.total_distance_km }}
                                </p>
                                <p
                                    class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                                >
                                    Total KM
                                </p>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-foreground">
                                    {{ item.avg_speed }}
                                </p>
                                <p
                                    class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                                >
                                    Rata-rata KM/H
                                </p>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-foreground">
                                    {{ item.total_duration_minutes }}
                                </p>
                                <p
                                    class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                                >
                                    Total Menit
                                </p>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-foreground">
                                    {{ item.total_sessions }}
                                </p>
                                <p
                                    class="text-[8px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                                >
                                    Total Sesi
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bar Comparison -->
                <div
                    class="rounded-3xl border border-border bg-card p-8 shadow-xl"
                >
                    <h3
                        class="mb-6 text-lg font-black tracking-tight text-foreground uppercase"
                    >
                        Perbandingan Visual
                    </h3>
                    <div
                        class="flex flex-col gap-6"
                        v-for="metric in [
                            {
                                key: 'total_distance_km',
                                label: 'Total Jarak (KM)',
                                color: 'bg-blue-500',
                            },
                            {
                                key: 'avg_speed',
                                label: 'Kecepatan Rata-rata (KM/H)',
                                color: 'bg-accent',
                            },
                            {
                                key: 'total_duration_minutes',
                                label: 'Total Durasi (Menit)',
                                color: 'bg-green-500',
                            },
                        ]"
                        :key="metric.key"
                    >
                        <div>
                            <p
                                class="mb-3 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                {{ metric.label }}
                            </p>
                            <div
                                class="flex flex-col gap-2"
                                v-for="item in comparisonData"
                                :key="item.athlete_id"
                            >
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-24 truncate text-xs font-bold text-foreground"
                                        >{{ athleteMap[item.athlete_id] }}</span
                                    >
                                    <div
                                        class="flex-1 overflow-hidden rounded-full bg-muted/30"
                                        style="height: 24px"
                                    >
                                        <div
                                            :class="metric.color"
                                            class="flex h-full items-center justify-end rounded-full px-3 text-[10px] font-black text-white transition-all duration-700"
                                            :style="{
                                                width: `${((item as any)[metric.key] / maxVal(metric.key)) * 100}%`,
                                            }"
                                        >
                                            {{ (item as any)[metric.key] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
