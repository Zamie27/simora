<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import {
    Activity,
    AlertCircle,
    Calendar,
    ChevronRight,
    Layers,
    Plus,
    Ruler,
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

const props = defineProps<{
    metrics: PhysicalMetric[];
    categories: Category[];
}>();

const { props: pageProps } = usePage<any>();
const user = computed(() => pageProps.auth.user);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Update Profil Fisik', href: '/athlete/physical' },
];

const showAddModal = ref(false);

const form = useForm({
    height: props.metrics[0]?.height || '',
    weight: props.metrics[0]?.weight || '',
    category: props.metrics[0]?.category || '',
    recorded_at: new Date().toISOString().split('T')[0],
});

const categoryOptions = computed(() =>
    props.categories.map((c) => ({ value: c.name, label: c.name })),
);

const submit = () => {
    form.post('/athlete/physical', {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset('recorded_at');
        },
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
        id: 'phys-metrics',
        theme: 'dark',
        background: 'transparent',
        toolbar: { show: false },
    },
    colors: ['#FF6120', '#a4badd'],
    stroke: { curve: 'smooth', width: 3 },
    xaxis: {
        categories: [...props.metrics]
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
        data: [...props.metrics].reverse().map((m) => Number(m.weight)),
    },
    {
        name: 'Height (cm)',
        data: [...props.metrics].reverse().map((m) => Number(m.height)),
    },
]);
</script>

<template>
    <Head title="Profil Fisik Atlet" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <div
                class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row"
            >
                <div>
                    <h1
                        class="text-4xl font-black tracking-tighter text-foreground uppercase"
                    >
                        Profil Fisik Saya
                    </h1>
                    <p
                        class="mt-2 text-[10px] font-medium tracking-[0.2em] text-muted-foreground uppercase italic opacity-80"
                    >
                        Lacak Kemajuan Fisik & Kesiapan Lomba
                    </p>
                </div>
                <button
                    @click="showAddModal = true"
                    class="flex items-center gap-2 rounded-xl bg-accent px-8 py-4 text-xs font-black tracking-widest text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-95"
                >
                    <Plus class="h-4 w-4" /> Update Fisik Baru
                </button>
            </div>

            <!-- Warning if DOB or Gender is missing -->
            <div
                v-if="!user.date_of_birth || !user.gender"
                class="group flex flex-col items-center justify-between gap-6 rounded-3xl border border-destructive/20 bg-destructive/10 p-8 shadow-2xl shadow-destructive/5 transition-all hover:border-destructive/40 md:flex-row"
            >
                <div class="flex items-center gap-6">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-2xl bg-destructive text-white shadow-2xl shadow-destructive/20 transition-transform group-hover:scale-110"
                    >
                        <AlertCircle class="h-8 w-8" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <h3
                            class="text-xl font-black tracking-tight text-foreground uppercase"
                        >
                            Lengkapi Profil Anda
                        </h3>
                        <p
                            class="text-sm font-bold text-muted-foreground italic opacity-70"
                        >
                            <template
                                v-if="!user.date_of_birth && !user.gender"
                            >
                                Anda belum menyetel tanggal lahir dan jenis
                                kelamin di profil.
                            </template>
                            <template v-else-if="!user.date_of_birth">
                                Anda belum menyetel tanggal lahir di profil.
                            </template>
                            <template v-else>
                                Anda belum menyetel jenis kelamin di profil.
                            </template>
                            Hal ini diperlukan untuk menghitung usia dan BMI
                            otomatis setiap kali menginput data fisik.
                        </p>
                    </div>
                </div>
                <Link
                    href="/settings/profile"
                    class="flex items-center gap-3 rounded-2xl bg-foreground px-8 py-4 text-[10px] font-black tracking-[0.2em] text-background uppercase transition-all hover:bg-foreground/80 active:scale-95"
                >
                    Lengkapi Profil <ChevronRight class="h-4 w-4" />
                </Link>
            </div>

            <!-- Dashboard Grid -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Chart Area -->
                <div
                    class="relative overflow-hidden rounded-3xl border border-border bg-card p-8 shadow-2xl backdrop-blur-md lg:col-span-8"
                >
                    <div
                        class="absolute top-0 right-0 -mt-32 -mr-32 h-64 w-64 rounded-full bg-accent/5 blur-[100px]"
                    ></div>
                    <div class="mb-8 flex items-center gap-4">
                        <div class="rounded-xl bg-secondary p-3 text-accent">
                            <Activity class="h-5 w-5" />
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

                <!-- Latest Stats Sidebar -->
                <div class="flex flex-col gap-6 lg:col-span-4">
                    <div
                        class="group relative overflow-hidden rounded-3xl border border-white/5 bg-secondary p-8 shadow-2xl"
                    >
                        <div
                            class="absolute -top-4 -right-4 h-32 w-32 scale-150 rotate-12 rounded-full bg-white/5 transition-transform group-hover:scale-110"
                        ></div>
                        <div class="relative z-10 mb-8 flex items-center gap-3">
                            <div
                                class="rounded-lg bg-white/5 p-2 text-[#a4badd]"
                            >
                                <Weight class="h-3 w-3" />
                            </div>
                            <p
                                class="text-[10px] font-black tracking-[0.2em] text-[#a4badd] uppercase opacity-60"
                            >
                                Latest Measurement
                            </p>
                        </div>

                        <div class="relative z-10 flex flex-col gap-6">
                            <div>
                                <span
                                    class="text-6xl leading-none font-black text-white"
                                    >{{ metrics[0]?.weight || '--' }}</span
                                >
                                <span
                                    class="ml-2 text-xs font-black text-[#a4badd] opacity-60"
                                    >KG</span
                                >
                                <p
                                    class="mt-1 text-[10px] font-black tracking-widest text-[#a4badd] uppercase opacity-40"
                                >
                                    Weight
                                </p>
                            </div>
                            <div>
                                <span
                                    class="text-6xl leading-none font-black text-white"
                                    >{{ metrics[0]?.height || '--' }}</span
                                >
                                <span
                                    class="ml-2 text-xs font-black text-[#a4badd] opacity-60"
                                    >CM</span
                                >
                                <p
                                    class="mt-1 text-[10px] font-black tracking-widest text-[#a4badd] uppercase opacity-40"
                                >
                                    Height
                                </p>
                            </div>
                            <div>
                                <span
                                    class="text-6xl leading-none font-black text-white"
                                    >{{ metrics[0]?.bmi || '--' }}</span
                                >
                                <p
                                    class="mt-1 text-[10px] font-black tracking-widest text-accent uppercase opacity-70"
                                >
                                    BMI Index ({{
                                        metrics[0]?.bmi_status || 'N/A'
                                    }})
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col gap-4 rounded-3xl border border-border bg-card p-8 shadow-xl"
                    >
                        <div class="mb-2 flex items-center gap-3">
                            <div
                                class="rounded-lg bg-secondary p-2 text-accent"
                            >
                                <UserIcon class="h-3 w-3" />
                            </div>
                            <h3
                                class="text-xs font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Summary Info
                            </h3>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div
                                class="flex items-center justify-between border-b border-border/50 pb-2"
                            >
                                <span
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                                    >Kategori</span
                                >
                                <span
                                    class="text-xs font-black text-accent uppercase"
                                    >{{ metrics[0]?.category || '-' }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-border/50 pb-2"
                            >
                                <span
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                                    >Usia Saat Terakhir</span
                                >
                                <span class="text-xs font-black text-foreground"
                                    >{{ metrics[0]?.age || '-' }} Years</span
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                                    >Jenis Kelamin</span
                                >
                                <span
                                    class="text-xs font-black text-foreground uppercase"
                                    >{{
                                        user.gender === 'male'
                                            ? 'Laki-laki'
                                            : user.gender === 'female'
                                              ? 'Perempuan'
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
                        History Physical Performance
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
                                <th class="px-8 py-5">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="m in metrics"
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
                                <td class="px-8 py-6">
                                    <span
                                        class="rounded-lg border border-white/5 bg-secondary px-3 py-1.5 text-[9px] font-black text-secondary-foreground uppercase"
                                        >{{ m.category }}</span
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Modal -->
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
                                Record My Physical Data
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
                            v-if="!user.date_of_birth || !user.gender"
                            class="mb-8 rounded-2xl border border-destructive/20 bg-destructive/10 p-6"
                        >
                            <h4
                                class="mb-2 flex items-center gap-2 text-sm font-black tracking-widest text-destructive uppercase"
                            >
                                <AlertCircle class="h-4 w-4" /> Stop!
                            </h4>
                            <p
                                class="mb-4 text-xs font-medium text-foreground/80 italic"
                            >
                                <template
                                    v-if="!user.date_of_birth && !user.gender"
                                >
                                    Anda belum menyetel tanggal lahir dan jenis
                                    kelamin di profil.
                                </template>
                                <template v-else-if="!user.date_of_birth">
                                    Anda belum menyetel tanggal lahir di profil.
                                </template>
                                <template v-else>
                                    Anda belum menyetel jenis kelamin di profil.
                                </template>
                            </p>
                            <Link
                                href="/settings/profile"
                                class="text-[10px] font-black tracking-widest text-destructive uppercase underline decoration-2 underline-offset-4"
                                >Lengkapi Profil Sekarang</Link
                            >
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
                                    for="category"
                                    class="flex items-center gap-2 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                >
                                    <Layers class="h-2.5 w-2.5" /> Kategori
                                </Label>
                                <CustomSelect
                                    v-model="form.category"
                                    :options="categoryOptions"
                                    placeholder="Pilih Kategori"
                                />
                                <p
                                    v-if="form.errors.category"
                                    class="text-[10px] font-bold text-destructive"
                                >
                                    {{ form.errors.category }}
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
                                    !user.date_of_birth ||
                                    !user.gender
                                "
                                class="col-span-2 rounded-2xl bg-accent py-5 text-[10px] font-black tracking-[0.2em] text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-[0.98] disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Data Fisik Saya'
                                }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
