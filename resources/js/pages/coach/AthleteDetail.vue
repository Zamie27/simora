<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import {
    AlertCircle,
    Calendar,
    Layers,
    Plus,
    Ruler,
    Save,
    User as UserIcon,
    Weight,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

import CustomSelect from '@/components/ui/CustomSelect.vue';
import DatePicker from '@/components/ui/DatePicker.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface PhysicalMetric {
    id: number;
    height: number;
    weight: number;
    bmi: number;
    bmi_status: string;
    age: number;
    category: string;
    recorded_at: string;
}

interface Category {
    id: number;
    name: string;
}

interface Athlete {
    id: number;
    name: string;
    email: string;
    gender: string | null;
    date_of_birth: string | null;
    category_id: number | null;
    category: Category | null;
    physical_metrics: PhysicalMetric[];
}

const props = defineProps<{
    athlete: Athlete;
    categories: Category[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Atlet Saya', href: '/coach/athletes' },
    { title: props.athlete.name, href: `/coach/athletes/${props.athlete.id}` },
];

const showAddModal = ref(false);

const form = useForm({
    height: props.athlete.physical_metrics[0]?.height || '',
    weight: props.athlete.physical_metrics[0]?.weight || '',
    recorded_at: new Date().toISOString().split('T')[0],
});

const categoryForm = useForm({
    category_id: props.athlete.category_id?.toString() || '',
});

const submit = () => {
    form.post(`/coach/athletes/${props.athlete.id}/metrics`, {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset('recorded_at');
        },
    });
};

const updateCategory = () => {
    categoryForm.patch(`/coach/athletes/${props.athlete.id}/category`, {
        preserveScroll: true,
    });
};

const formatDate = (date: string) => {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    return `${day}/${month}/${year}`;
};

// Chart Data
const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        id: 'athlete-metrics',
        theme: 'dark',
        background: 'transparent',
        toolbar: { show: false },
    },
    colors: ['#FF6120', '#102844'],
    stroke: { curve: 'smooth', width: 3 },
    xaxis: {
        categories: [...props.athlete.physical_metrics]
            .reverse()
            .map((m) => formatDate(m.recorded_at)),
        labels: { style: { colors: '#94a3b8' } },
        axisBorder: { show: false },
    },
    yaxis: {
        labels: { style: { colors: '#94a3b8' } },
    },
    grid: { borderColor: '#1e293b', strokeDashArray: 4 },
    tooltip: { theme: 'dark' },
}));

const chartSeries = computed(() => [
    {
        name: 'Weight (kg)',
        data: [...props.athlete.physical_metrics]
            .reverse()
            .map((m) => Number(m.weight)),
    },
    {
        name: 'Height (cm)',
        data: [...props.athlete.physical_metrics]
            .reverse()
            .map((m) => Number(m.height)),
    },
]);
</script>

<template>
    <Head :title="`Detail Atlet | ${athlete.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <div
                class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row"
            >
                <div class="flex items-center gap-6">
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-accent/20 bg-accent/20 text-2xl font-black text-accent shadow-2xl shadow-accent/10"
                    >
                        {{
                            athlete.name
                                .split(' ')
                                .map((n) => n[0])
                                .slice(0, 2)
                                .join('')
                        }}
                    </div>
                    <div>
                        <h1
                            class="text-4xl font-black tracking-tighter text-foreground uppercase"
                        >
                            {{ athlete.name }}
                        </h1>
                        <p
                            class="mt-1 text-xs font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                        >
                            {{ athlete.email }}
                        </p>
                    </div>
                </div>
                <button
                    @click="showAddModal = true"
                    class="flex items-center gap-2 rounded-xl bg-accent px-8 py-4 text-xs font-black tracking-widest text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-95"
                >
                    <Plus class="h-4 w-4" /> Update Data Fisik
                </button>
            </div>

            <!-- Danger Zone/Warning for Coach -->
            <div
                v-if="!athlete.date_of_birth || !athlete.gender"
                class="group flex items-center justify-between gap-6 rounded-3xl border border-destructive/20 bg-destructive/10 p-6"
            >
                <div class="flex items-center gap-6">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive text-white shadow-xl shadow-destructive/20 transition-transform group-hover:scale-110"
                    >
                        <AlertCircle class="h-6 w-6" />
                    </div>
                    <div>
                        <h3
                            class="text-lg font-black tracking-tight text-foreground uppercase"
                        >
                            Data Profil Atlet Tidak Lengkap
                        </h3>
                        <p
                            class="text-xs font-medium text-muted-foreground italic opacity-70"
                        >
                            <template
                                v-if="!athlete.date_of_birth && !athlete.gender"
                            >
                                Atlet ini belum mengisi tanggal lahir dan jenis
                                kelamin.
                            </template>
                            <template v-else-if="!athlete.date_of_birth">
                                Atlet ini belum mengisi tanggal lahir.
                            </template>
                            <template v-else>
                                Atlet ini belum mengisi jenis kelamin.
                            </template>
                            Beritahu atlet untuk melengkapinya di pengaturan
                            profil agar usia dan BMI dapat dihitung otomatis.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Left Column -->
                <div class="flex flex-col gap-8 lg:col-span-8">
                    <!-- Chart Area -->
                    <div
                        class="relative overflow-hidden rounded-3xl border border-border bg-card p-8 shadow-2xl"
                    >
                        <div
                            class="absolute top-0 right-0 -mt-32 -mr-32 h-64 w-64 rounded-full bg-accent/5 blur-[100px]"
                        ></div>
                        <div class="mb-8 flex items-center gap-4">
                            <div class="rounded-xl bg-secondary p-3 text-accent">
                                <Layers class="h-5 w-5" />
                            </div>
                            <h3 class="text-xl font-black tracking-tight uppercase">
                                Progress Grafik Fisik
                            </h3>
                        </div>
                        <div id="chart">
                            <VueApexCharts
                                width="100%"
                                height="350"
                                type="line"
                                :options="chartOptions"
                                :series="chartSeries"
                            ></VueApexCharts>
                        </div>
                    </div>

                    <!-- Latest Metric Area (Moved from sidebar) -->
                    <div
                        class="group relative overflow-hidden rounded-3xl border border-white/5 bg-secondary p-8 shadow-2xl"
                    >
                        <div
                            class="absolute -top-4 -right-4 h-32 w-32 scale-150 rotate-12 rounded-full bg-white/5 transition-transform group-hover:scale-110"
                        ></div>
                        <div class="relative z-10 mb-8 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-lg bg-white/5 p-2 text-[#a4badd]"
                                >
                                    <Weight class="h-4 w-4" />
                                </div>
                                <h3
                                    class="text-sm font-black tracking-widest text-[#a4badd] uppercase opacity-60"
                                >
                                    Latest Metric Summary
                                </h3>
                            </div>
                        </div>
                        <div class="relative z-10 grid grid-cols-1 gap-8 md:grid-cols-3">
                            <div class="rounded-2xl bg-white/5 p-6 transition-colors hover:bg-white/10">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-5xl font-black text-white">
                                        {{ athlete.physical_metrics[0]?.weight || '--' }}
                                    </span>
                                    <span class="text-xs font-black text-[#a4badd] opacity-60">KG</span>
                                </div>
                                <p class="mt-2 text-[10px] font-black tracking-widest text-[#a4badd] uppercase opacity-40">
                                    Current Weight
                                </p>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-6 transition-colors hover:bg-white/10">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-5xl font-black text-white">
                                        {{ athlete.physical_metrics[0]?.height || '--' }}
                                    </span>
                                    <span class="text-xs font-black text-[#a4badd] opacity-60">CM</span>
                                </div>
                                <p class="mt-2 text-[10px] font-black tracking-widest text-[#a4badd] uppercase opacity-40">
                                    Current Height
                                </p>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-6 border border-accent/20 bg-accent/5 transition-colors hover:bg-accent/10">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-5xl font-black text-white">
                                        {{ athlete.physical_metrics[0]?.bmi || '--' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-[10px] font-black tracking-widest text-[#FF6120] uppercase">
                                    BMI Status: {{ athlete.physical_metrics[0]?.bmi_status || 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="flex flex-col gap-8 lg:col-span-4">
                    <!-- Category Management -->
                    <div
                        class="flex flex-col gap-6 rounded-3xl border border-border bg-card p-10 shadow-xl transition-all hover:shadow-2xl"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="rounded-xl bg-orange-500/10 p-3 text-orange-500">
                                    <Layers class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="text-xs font-black tracking-[0.2em] text-muted-foreground uppercase opacity-80">
                                        Manajemen Kategori
                                    </h3>
                                    <p class="text-[10px] font-bold text-muted-foreground/40 uppercase">Assign Sport Division</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4">
                            <form @submit.prevent="updateCategory" class="flex flex-col gap-4">
                                <div class="space-y-2">
                                    <CustomSelect
                                        v-model="categoryForm.category_id"
                                        :options="props.categories.map(c => ({ value: c.id.toString(), label: c.name }))"
                                        placeholder="Pilih Kategori"
                                    />
                                    <p v-if="categoryForm.errors.category_id" class="text-[10px] font-bold text-destructive">
                                        {{ categoryForm.errors.category_id }}
                                    </p>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="categoryForm.processing"
                                    class="group flex w-full items-center justify-center gap-3 rounded-2xl bg-accent px-6 py-5 text-[10px] font-black tracking-[0.2em] text-white uppercase transition-all hover:bg-accent/90 disabled:opacity-50"
                                >
                                    <Save v-if="!categoryForm.processing" class="h-3.5 w-3.5 transition-transform group-hover:scale-110" />
                                    <span>{{ categoryForm.processing ? 'Menyimpan...' : 'Simpan Kategori' }}</span>
                                </button>
                                <Transition
                                    enter-active-class="transition duration-300 ease-out"
                                    enter-from-class="opacity-0 -translate-y-2"
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition duration-200 ease-in"
                                    leave-from-class="opacity-100"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="categoryForm.recentlySuccessful" class="text-center text-[10px] font-black text-green-500 uppercase tracking-widest">
                                        Perubahan Disimpan!
                                    </p>
                                </Transition>
                            </form>
                        </div>
                    </div>

                    <!-- Summary Info -->
                    <div
                        class="flex flex-col gap-8 rounded-3xl border border-border bg-card p-10 shadow-xl"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-xl bg-secondary p-3 text-accent"
                            >
                                <UserIcon class="h-5 w-5" />
                            </div>
                            <div>
                                <h3
                                    class="text-xs font-black tracking-[0.2em] text-muted-foreground uppercase opacity-80"
                                >
                                    Summary Info
                                </h3>
                                <p class="text-[10px] font-bold text-muted-foreground/40 uppercase">Identity Details</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-5">
                            <div
                                class="flex items-center justify-between border-b border-white/[0.03] pb-4"
                            >
                                <span
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                    >Kategori</span
                                >
                                <span
                                    class="rounded-lg bg-orange-500/10 px-3 py-1.5 text-[10px] font-black text-orange-500 uppercase tracking-wider"
                                    >{{
                                        athlete.category?.name ||
                                        '-'
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-white/[0.03] pb-4"
                            >
                                <span
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                    >Usia Saat Ini</span
                                >
                                <span class="text-xs font-black text-foreground"
                                    >{{
                                        athlete.physical_metrics[0]?.age || '-'
                                    }}
                                    Thn</span
                                >
                            </div>
                            <div class="flex items-center justify-between border-b border-white/[0.03] pb-4">
                                <span
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                    >Jenis Kelamin</span
                                >
                                <span
                                    class="text-xs font-black text-foreground uppercase"
                                    >{{
                                        athlete.gender === 'male'
                                            ? 'Laki-laki'
                                            : athlete.gender === 'female'
                                              ? 'Perempuan'
                                              : '-'
                                    }}</span
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40"
                                    >Last Update</span
                                >
                                <span
                                    class="text-xs font-black text-muted-foreground"
                                    >{{
                                        athlete.physical_metrics[0]
                                            ? formatDate(
                                                  athlete.physical_metrics[0]
                                                      .recorded_at,
                                              )
                                            : '-'
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="flex flex-col gap-6">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-secondary p-3 text-accent">
                        <Calendar class="h-5 w-5" />
                    </div>
                    <h2
                        class="text-2xl font-black tracking-tight text-foreground uppercase"
                    >
                        Riwayat Peningkatan Fisik
                    </h2>
                </div>
                <div
                    class="overflow-hidden rounded-3xl border border-border bg-card shadow-2xl"
                >
                    <table class="w-full text-left">
                        <thead class="border-b border-border bg-muted/40">
                            <tr
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                            >
                                <th class="px-8 py-5">Tanggal</th>
                                <th class="px-8 py-5">Berat (KG)</th>
                                <th class="px-8 py-5">BMI</th>
                                <th class="px-8 py-5">Status BMI</th>
                                <th class="px-8 py-5">Tinggi (CM)</th>
                                <th class="px-8 py-5">Usia</th>
                                <th class="px-8 py-5">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="m in athlete.physical_metrics"
                                :key="m.id"
                                class="transition-colors hover:bg-muted/10"
                            >
                                <td class="px-8 py-6 text-xs font-bold">
                                    {{ formatDate(m.recorded_at) }}
                                </td>
                                <td
                                    class="px-8 py-6 font-black text-foreground"
                                >
                                    {{ m.weight }}
                                </td>
                                <td
                                    class="px-8 py-6 font-black text-foreground"
                                >
                                    {{ m.bmi }}
                                </td>
                                <td
                                    class="px-8 py-6 text-xs font-bold"
                                    :class="
                                        m.bmi_status
                                            .toLowerCase()
                                            .includes('normal')
                                            ? 'text-green-400'
                                            : 'text-accent'
                                    "
                                >
                                    {{ m.bmi_status }}
                                </td>
                                <td
                                    class="px-8 py-6 font-black text-foreground"
                                >
                                    {{ m.height }}
                                </td>
                                <td
                                    class="px-8 py-6 text-xs font-bold text-muted-foreground"
                                >
                                    {{ m.age }} Thn
                                </td>
                                <td class="px-8 py-6">
                                    <span
                                        class="rounded-lg border border-accent/20 bg-accent/10 px-3 py-1.5 text-[9px] font-black text-accent uppercase"
                                        >{{ m.category }}</span
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Update Modal -->
            <div
                v-if="showAddModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-xl"
            >
                <div
                    class="w-full max-w-xl animate-in overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-[0_50px_100px_-20px_rgba(255,97,32,0.15)] duration-300 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-border bg-muted/20 p-10"
                    >
                        <div>
                            <h2
                                class="text-2xl font-black tracking-tight text-foreground uppercase"
                            >
                                Record Physical Metric
                            </h2>
                            <p
                                class="mt-1 text-xs font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Athlete Monitoring System
                            </p>
                        </div>
                        <button
                            @click="showAddModal = false"
                            class="rounded-full p-3 text-muted-foreground transition-all hover:bg-muted/50 hover:text-foreground"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                ></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-10">
                        <div
                            v-if="!athlete.date_of_birth || !athlete.gender"
                            class="mb-8 rounded-2xl border border-destructive/20 bg-destructive/10 p-6"
                        >
                            <h4
                                class="mb-2 flex items-center gap-2 text-sm font-black tracking-widest text-destructive uppercase"
                            >
                                <AlertCircle class="h-4 w-4" /> Stop!
                            </h4>
                            <p
                                class="text-xs font-medium text-foreground/80 italic"
                            >
                                <template
                                    v-if="
                                        !athlete.date_of_birth &&
                                        !athlete.gender
                                    "
                                >
                                    Atlet belum mengisi tanggal lahir dan jenis
                                    kelamin.
                                </template>
                                <template v-else-if="!athlete.date_of_birth">
                                    Atlet belum mengisi tanggal lahir.
                                </template>
                                <template v-else>
                                    Atlet belum mengisi jenis kelamin.
                                </template>
                                Penginputan data fisik ditangguhkan sampai atlet
                                melengkapi profilnya.
                            </p>
                        </div>

                        <form
                            @submit.prevent="submit"
                            class="grid grid-cols-2 gap-8"
                        >
                            <div class="flex flex-col gap-2">
                                <Label
                                    for="height"
                                    class="flex items-center gap-2 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                >
                                    <Ruler class="h-2.5 w-2.5" /> Tinggi Badan
                                    (CM)
                                </Label>
                                <Input
                                    id="height"
                                    type="number"
                                    step="0.1"
                                    v-model="form.height"
                                    class="h-14 rounded-2xl border-none bg-muted/30 px-6 text-lg font-black focus:ring-2 focus:ring-accent"
                                    placeholder="170.5"
                                />
                                <p
                                    v-if="form.errors.height"
                                    class="text-[10px] font-bold text-destructive"
                                >
                                    {{ form.errors.height }}
                                </p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <Label
                                    for="weight"
                                    class="flex items-center gap-2 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                >
                                    <Weight class="h-2.5 w-2.5" /> Berat Badan
                                    (KG)
                                </Label>
                                <Input
                                    id="weight"
                                    type="number"
                                    step="0.1"
                                    v-model="form.weight"
                                    class="h-14 rounded-2xl border-none bg-muted/30 px-6 text-lg font-black focus:ring-2 focus:ring-accent"
                                    placeholder="65.2"
                                />
                                <p
                                    v-if="form.errors.weight"
                                    class="text-[10px] font-bold text-destructive"
                                >
                                    {{ form.errors.weight }}
                                </p>
                            </div>
                             <div class="col-span-2 flex flex-col gap-2">
                                <Label
                                    for="recorded_at"
                                    class="flex items-center gap-2 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                >
                                    <Calendar class="h-2.5 w-2.5" /> Tanggal
                                    Perekaman
                                </Label>
                                <DatePicker v-model="form.recorded_at" />
                                <p
                                    v-if="form.errors.recorded_at"
                                    class="text-[10px] font-bold text-destructive"
                                >
                                    {{ form.errors.recorded_at }}
                                </p>
                                <p
                                    v-if="$page.props.errors.category"
                                    class="text-[10px] font-bold text-destructive"
                                >
                                    {{ $page.props.errors.category }}
                                </p>
                                <p
                                    v-if="$page.props.errors.date_of_birth"
                                    class="text-[10px] font-bold text-destructive"
                                >
                                    {{ $page.props.errors.date_of_birth }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="
                                    form.processing ||
                                    !athlete.date_of_birth ||
                                    !athlete.gender
                                "
                                class="col-span-2 rounded-2xl bg-accent py-5 text-[10px] font-black tracking-[0.2em] text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-[0.98] disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Data Performa Fisik'
                                }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
