<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import {
    Download,
    FileText,
    Calendar,
    RotateCcw,
    TrendingUp,
    Milestone,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

import CustomSelect from '@/components/ui/CustomSelect.vue';
import DatePicker from '@/components/ui/DatePicker.vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Athlete {
    id: number;
    name: string;
    email: string;
}
interface Statistics {
    total_distance_km: number;
    total_duration_minutes: number;
    avg_speed: number;
    avg_rpm: number;
    total_sessions: number;
    completed_sessions: number;
}
interface ReportItem {
    athlete: Athlete;
    statistics: Statistics;
}

const props = defineProps<{
    athletes: Athlete[];
    reportData: ReportItem[];
    filters: {
        period: string;
        start_date: string | null;
        end_date: string | null;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Laporan', href: '/coach/reports' },
];

const periods = [
    { value: 'today', label: 'Hari Ini' },
    { value: 'this_week', label: 'Minggu Ini' },
    { value: 'this_month', label: 'Bulan Ini' },
    { value: 'this_4_months', label: '4 Bulanan (Caturwulan)' },
    { value: 'this_year', label: 'Tahun Ini' },
    { value: 'custom', label: 'Rentang Kustom' },
];

const period = ref(props.filters.period);
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

watch(period, (newVal) => {
    if (newVal !== 'custom') {
        applyFilters();
    }
});

const applyFilters = () => {
    router.get(
        '/coach/reports',
        {
            period: period.value,
            start_date: period.value === 'custom' ? startDate.value : null,
            end_date: period.value === 'custom' ? endDate.value : null,
        },
        { preserveState: true },
    );
};

const exportCsv = (athleteId?: number) => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/coach/reports/export';

    const csrfInput = document.createElement('input');
    csrfInput.name = '_token';
    csrfInput.value =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';
    form.appendChild(csrfInput);

    const formatInput = document.createElement('input');
    formatInput.name = 'format';
    formatInput.value = 'csv';
    form.appendChild(formatInput);

    const periodInput = document.createElement('input');
    periodInput.name = 'period';
    periodInput.value = period.value;
    form.appendChild(periodInput);

    if (period.value === 'custom') {
        const startInput = document.createElement('input');
        startInput.name = 'start_date';
        startInput.value = startDate.value;
        form.appendChild(startInput);

        const endInput = document.createElement('input');
        endInput.name = 'end_date';
        endInput.value = endDate.value;
        form.appendChild(endInput);
    }

    if (athleteId) {
        const atInput = document.createElement('input');
        atInput.name = 'athlete_id';
        atInput.value = athleteId.toString();
        form.appendChild(atInput);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};
</script>

<template>
    <Head title="Laporan Performa" />
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
                        <FileText class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-black tracking-tighter text-foreground uppercase md:text-4xl"
                        >
                            Laporan Performa
                        </h1>
                        <p
                            class="mt-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                        >
                            Analytics & Historical Performance
                        </p>
                    </div>
                </div>
                <button
                    @click="exportCsv()"
                    class="flex items-center gap-2 rounded-xl bg-accent px-8 py-4 text-xs font-black tracking-widest text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-95"
                >
                    <Download class="h-4 w-4" /> Download All (CSV)
                </button>
            </div>

            <!-- Filters -->
            <div
                class="flex flex-col gap-6 rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl shadow-black/5 md:flex-row md:items-end"
            >
                <div class="flex-1 space-y-2">
                    <label
                        class="flex items-center gap-2 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                    >
                        <Calendar class="h-3 w-3" /> Periode Laporan
                    </label>
                    <CustomSelect v-model="period" :options="periods" />
                </div>

                <template v-if="period === 'custom'">
                    <div class="flex-1 space-y-2">
                        <label
                            class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >Dari</label
                        >
                        <DatePicker v-model="startDate" />
                    </div>
                    <div class="flex-1 space-y-2">
                        <label
                            class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >Sampai</label
                        >
                        <DatePicker v-model="endDate" />
                    </div>
                    <button
                        @click="applyFilters"
                        class="rounded-xl bg-secondary px-6 py-3.5 text-[10px] font-black tracking-widest text-accent uppercase hover:bg-secondary/80"
                    >
                        Terapkan
                    </button>
                </template>
            </div>

            <!-- Report Grid -->
            <div class="grid grid-cols-1 gap-6">
                <div
                    class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-2xl shadow-black/5"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border bg-muted/20">
                                    <th
                                        class="px-8 py-6 text-left text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50"
                                    >
                                        Atlet Binaaan
                                    </th>
                                    <th
                                        class="px-6 py-6 text-center text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50"
                                    >
                                        Sesi
                                    </th>
                                    <th
                                        class="px-6 py-6 text-center text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50"
                                    >
                                        Jarak
                                    </th>
                                    <th
                                        class="px-6 py-6 text-center text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50"
                                    >
                                        Speed
                                    </th>
                                    <th
                                        class="px-6 py-6 text-center text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50"
                                    >
                                        RPM
                                    </th>
                                    <th
                                        class="px-6 py-6 text-center text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50"
                                    >
                                        Progress
                                    </th>
                                    <th
                                        class="px-8 py-6 text-right text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-50"
                                    >
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/50">
                                <tr
                                    v-for="item in reportData"
                                    :key="item.athlete.id"
                                    class="group transition-colors hover:bg-muted/10"
                                >
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-accent/10 text-xs font-black text-accent transition-transform group-hover:scale-110"
                                            >
                                                {{
                                                    item.athlete.name
                                                        .split(' ')
                                                        .map((n) => n[0])
                                                        .slice(0, 2)
                                                        .join('')
                                                }}
                                            </div>
                                            <div>
                                                <Link
                                                    :href="`/coach/athletes/${item.athlete.id}`"
                                                    class="group/link"
                                                >
                                                    <h4
                                                        class="font-black text-foreground uppercase transition-colors group-hover/link:text-accent"
                                                    >
                                                        {{ item.athlete.name }}
                                                    </h4>
                                                    <p
                                                        class="text-[10px] font-bold text-muted-foreground opacity-50"
                                                    >
                                                        {{ item.athlete.email }}
                                                    </p>
                                                </Link>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex flex-col items-center">
                                            <span
                                                class="text-xl font-black text-foreground italic"
                                                >{{
                                                    item.statistics
                                                        .total_sessions
                                                }}</span
                                            >
                                            <span
                                                class="text-[8px] font-black text-muted-foreground uppercase opacity-40"
                                                >Total Session</span
                                            >
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="flex items-baseline gap-1"
                                            >
                                                <span
                                                    class="text-xl font-black text-foreground italic"
                                                    >{{
                                                        item.statistics
                                                            .total_distance_km
                                                    }}</span
                                                >
                                                <span
                                                    class="text-[9px] font-black text-accent uppercase"
                                                    >km</span
                                                >
                                            </div>
                                            <div
                                                class="mt-1 flex items-center gap-1 text-[8px] font-black text-muted-foreground uppercase opacity-40"
                                            >
                                                <Milestone class="h-2 w-2" />
                                                Distance
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="flex items-baseline gap-1"
                                            >
                                                <span
                                                    class="text-xl font-black text-foreground italic"
                                                    >{{
                                                        item.statistics
                                                            .avg_speed
                                                    }}</span
                                                >
                                                <span
                                                    class="text-[9px] font-black text-accent uppercase"
                                                    >kph</span
                                                >
                                            </div>
                                            <div
                                                class="mt-1 flex items-center gap-1 text-[8px] font-black text-muted-foreground uppercase opacity-40"
                                            >
                                                <TrendingUp class="h-2 w-2" />
                                                Avg Speed
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="flex items-baseline gap-1"
                                            >
                                                <span
                                                    class="text-xl font-black text-foreground italic"
                                                    >{{
                                                        item.statistics.avg_rpm
                                                    }}</span
                                                >
                                                <span
                                                    class="text-[9px] font-black text-accent uppercase"
                                                    >rpm</span
                                                >
                                            </div>
                                            <div
                                                class="mt-1 flex items-center gap-1 text-[8px] font-black text-muted-foreground uppercase opacity-40"
                                            >
                                                <RotateCcw class="h-2 w-2" />
                                                Cadence
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div
                                            class="flex flex-col items-center gap-2"
                                        >
                                            <div
                                                class="relative h-2 w-32 overflow-hidden rounded-full bg-muted/30"
                                            >
                                                <div
                                                    class="absolute inset-y-0 left-0 bg-accent transition-all duration-1000"
                                                    :style="{
                                                        width: `${(item.statistics.completed_sessions / (item.statistics.total_sessions || 1)) * 100}%`,
                                                    }"
                                                ></div>
                                            </div>
                                            <span
                                                class="text-[10px] font-black text-foreground italic"
                                            >
                                                {{
                                                    item.statistics
                                                        .completed_sessions
                                                }}/{{
                                                    item.statistics
                                                        .total_sessions
                                                }}
                                                <small
                                                    class="text-muted-foreground opacity-40"
                                                    >Selesai</small
                                                >
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <button
                                            @click="exportCsv(item.athlete.id)"
                                            class="rounded-xl border border-border bg-card px-4 py-2 text-[10px] font-black tracking-widest text-muted-foreground uppercase transition-all hover:border-accent hover:text-accent hover:shadow-lg active:scale-95"
                                        >
                                            <Download
                                                class="mr-2 inline h-3 w-3"
                                            />
                                            Export
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="reportData.length === 0">
                                    <td
                                        colspan="7"
                                        class="px-8 py-20 text-center"
                                    >
                                        <Info
                                            class="mx-auto mb-4 h-10 w-10 text-muted-foreground opacity-20"
                                        />
                                        <p
                                            class="text-xs font-black tracking-[0.2em] text-muted-foreground uppercase opacity-60"
                                        >
                                            No performance records found for
                                            this period.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
