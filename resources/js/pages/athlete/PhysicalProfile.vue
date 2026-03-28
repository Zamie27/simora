<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import { Activity, AlertCircle, Calendar, ChevronRight, Layers, Plus, Ruler, User as UserIcon, Weight } from 'lucide-vue-next';
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
    props.categories.map(c => ({ value: c.name, label: c.name }))
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
        categories: [...props.metrics].reverse().map((m) => formatDate(m.recorded_at)),
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

const getChange = (current: number, index: number, field: 'weight' | 'height') => {
    const next = props.metrics[index + 1];
    
    if (!next) {
        return { value: 0, text: '-', class: 'text-muted-foreground' };
    }

    const diff = Number(current) - Number(next[field]);
    
    if (diff > 0) {
        return { value: diff, text: `+${diff.toFixed(1)}`, class: 'text-accent font-bold' };
    }
    
    if (diff < 0) {
        return { value: diff, text: `${diff.toFixed(1)}`, class: 'text-blue-400 font-bold' };
    }
    
    return { value: 0, text: '0', class: 'text-muted-foreground' };
};
</script>

<template>
    <Head title="Profil Fisik Atlet" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-10 p-6 md:p-10 max-w-7xl mx-auto w-full min-h-screen bg-background text-foreground">
            
            <div class="flex flex-col md:flex-row items-baseline justify-between gap-4 border-b border-border pb-6">
                <div>
                    <h1 class="text-4xl font-black tracking-tighter text-foreground uppercase tracking-tighter">Profil Fisik Saya</h1>
                    <p class="mt-2 text-muted-foreground font-medium italic opacity-80 uppercase tracking-[0.2em] text-[10px]">Lacak Kemajuan Fisik & Kesiapan Lomba</p>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="bg-accent hover:bg-accent/90 text-white font-black uppercase tracking-widest text-xs px-8 py-4 rounded-xl shadow-xl shadow-accent/20 transition-all active:scale-95 flex items-center gap-2"
                >
                    <Plus class="w-4 h-4" /> Update Fisik Baru
                </button>
            </div>

            <!-- Warning if DOB is missing -->
            <div v-if="!user.date_of_birth" class="bg-destructive/10 border border-destructive/20 p-8 rounded-3xl flex flex-col md:flex-row items-center justify-between gap-6 group hover:border-destructive/40 transition-all shadow-2xl shadow-destructive/5">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-destructive rounded-2xl flex items-center justify-center text-white shadow-2xl shadow-destructive/20 group-hover:scale-110 transition-transform">
                        <AlertCircle class="w-8 h-8" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <h3 class="text-xl font-black text-foreground uppercase tracking-tight">Lengkapi Profil Anda</h3>
                        <p class="text-sm text-muted-foreground font-bold italic opacity-70">Anda belum menyetel tanggal lahir di profil. Hal ini diperlukan untuk menghitung usia otomatis setiap kali menginput data fisik.</p>
                    </div>
                </div>
                <Link 
                    href="/settings/profile"
                    class="bg-foreground text-background px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-foreground/80 transition-all flex items-center gap-3 active:scale-95"
                >
                    Setel Tanggal Lahir <ChevronRight class="w-4 h-4" />
                </Link>
            </div>

            <!-- Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Chart Area -->
                <div class="lg:col-span-8 bg-card border border-border rounded-3xl p-8 shadow-2xl relative overflow-hidden backdrop-blur-md">
                     <div class="absolute top-0 right-0 w-64 h-64 bg-accent/5 rounded-full blur-[100px] -mr-32 -mt-32"></div>
                    <div class="flex items-center gap-4 mb-8">
                         <div class="p-3 bg-secondary rounded-xl text-accent">
                             <Activity class="w-5 h-5" />
                         </div>
                         <h3 class="text-xl font-black uppercase tracking-tight">Progress Grafik Fisik</h3>
                    </div>
                    <div id="chart">
                        <VueApexCharts width="100%" height="350" type="line" :options="chartOptions" :series="chartSeries"></VueApexCharts>
                    </div>
                </div>

                <!-- Latest Stats Sidebar -->
                <div class="lg:col-span-4 flex flex-col gap-6">
                    <div class="bg-secondary p-8 rounded-3xl border border-white/5 relative group overflow-hidden shadow-2xl">
                        <div class="absolute -right-4 -top-4 w-32 h-32 bg-white/5 rounded-full scale-150 rotate-12 transition-transform group-hover:scale-110"></div>
                        <div class="flex items-center gap-3 mb-8 relative z-10">
                            <div class="p-2 bg-white/5 rounded-lg text-[#a4badd]">
                                <Weight class="w-3 h-3" />
                            </div>
                            <p class="text-[10px] uppercase font-black tracking-[0.2em] text-[#a4badd] opacity-60">Latest Measurement</p>
                        </div>
                        
                        <div class="flex flex-col gap-6 relative z-10">
                            <div>
                                <span class="text-6xl font-black text-white leading-none">{{ metrics[0]?.weight || '--' }}</span>
                                <span class="text-xs font-black text-[#a4badd] ml-2 opacity-60">KG</span>
                                <p class="text-[10px] uppercase font-black tracking-widest text-[#a4badd] opacity-40 mt-1">Weight</p>
                            </div>
                            <div>
                                <span class="text-6xl font-black text-white leading-none">{{ metrics[0]?.height || '--' }}</span>
                                <span class="text-xs font-black text-[#a4badd] ml-2 opacity-60">CM</span>
                                <p class="text-[10px] uppercase font-black tracking-widest text-[#a4badd] opacity-40 mt-1">Height</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-3xl p-8 shadow-xl flex flex-col gap-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-secondary rounded-lg text-accent">
                                <UserIcon class="w-3 h-3" />
                            </div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-muted-foreground opacity-60">Summary Info</h3>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div class="flex justify-between items-center pb-2 border-b border-border/50">
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-50">Kategori</span>
                                <span class="text-xs font-black text-accent uppercase">{{ metrics[0]?.category || '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-border/50">
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-50">Usia Saat Terakhir</span>
                                <span class="text-xs font-black text-foreground">{{ metrics[0]?.age || '-' }} Years</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="flex flex-col gap-6">
                 <div class="flex items-center gap-4">
                     <div class="p-3 bg-secondary rounded-xl text-accent">
                         <Calendar class="w-5 h-5" />
                     </div>
                     <h2 class="text-2xl font-black text-foreground tracking-tight uppercase">History Physical Performance</h2>
                 </div>
                 <div class="bg-card border border-border rounded-3xl overflow-hidden shadow-2xl">
                    <table class="w-full text-left">
                        <thead class="bg-muted/40 border-b border-border">
                            <tr class="text-[10px] uppercase font-black tracking-widest text-muted-foreground">
                                <th class="px-8 py-5">Tanggal</th>
                                <th class="px-8 py-5">Berat (KG)</th>
                                <th class="px-8 py-5">Status</th>
                                <th class="px-8 py-5">Tinggi (CM)</th>
                                <th class="px-8 py-5">Pertumbuhan</th>
                                <th class="px-8 py-5">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="(m, index) in metrics" :key="m.id" class="hover:bg-muted/10 transition-colors">
                                <td class="px-8 py-6 font-bold text-xs">{{ formatDate(m.recorded_at) }}</td>
                                <td class="px-8 py-6 font-black text-foreground">{{ m.weight }}</td>
                                <td class="px-8 py-6 text-xs" :class="getChange(m.weight, index, 'weight').class">
                                    {{ getChange(m.weight, index, 'weight').text }}
                                </td>
                                <td class="px-8 py-6 font-black text-foreground">{{ m.height }}</td>
                                <td class="px-8 py-6 text-xs" :class="getChange(m.height, index, 'height').class">
                                    {{ getChange(m.height, index, 'height').text }}
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-[9px] font-black uppercase px-3 py-1.5 rounded-lg bg-secondary text-secondary-foreground border border-white/5">{{ m.category }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                 </div>
            </div>

            <!-- Add Modal -->
            <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/90 backdrop-blur-xl">
                <div class="bg-card w-full max-w-xl rounded-[2.5rem] border border-border shadow-[0_50px_100px_-20px_rgba(255,97,32,0.15)] overflow-hidden animate-in fade-in zoom-in duration-300">
                    <div class="p-10 border-b border-border flex justify-between items-center bg-muted/20">
                         <div>
                            <h2 class="text-2xl font-black text-foreground uppercase tracking-tight">Record My Physical Data</h2>
                            <p class="text-xs text-muted-foreground font-bold mt-1 uppercase tracking-widest opacity-60">Athlete Monitoring System</p>
                         </div>
                        <button @click="showAddModal = false" class="p-3 rounded-full hover:bg-muted/50 text-muted-foreground hover:text-foreground transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="p-10">
                        <div v-if="!user.date_of_birth" class="mb-8 p-6 bg-destructive/10 border border-destructive/20 rounded-2xl">
                             <h4 class="text-destructive text-sm font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                                <AlertCircle class="w-4 h-4" /> Stop!
                             </h4>
                             <p class="text-xs text-foreground/80 font-medium italic mb-4">Anda belum menyetel tanggal lahir di profil Anda. Data usia tidak dapat dihitung otomatis.</p>
                             <Link href="/settings/profile" class="text-[10px] font-black text-destructive uppercase tracking-widest underline decoration-2 underline-offset-4">Setel Tanggal Lahir Sekarang</Link>
                        </div>

                        <form @submit.prevent="submit" class="grid grid-cols-2 gap-8">
                            <div class="flex flex-col gap-2">
                                <Label for="height" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-60 flex items-center gap-2">
                                    <Ruler class="w-2.5 h-2.5" /> Tinggi Badan (CM)
                                </Label>
                                <Input id="height" type="number" step="0.1" v-model="form.height" class="h-14 bg-muted/30 border-none rounded-2xl px-6 text-lg font-black focus:ring-2 focus:ring-accent" placeholder="170.5" />
                                <p v-if="form.errors.height" class="text-destructive text-[10px] font-bold">{{ form.errors.height }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <Label for="weight" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-60 flex items-center gap-2">
                                    <Weight class="w-2.5 h-2.5" /> Berat Badan (KG)
                                </Label>
                                <Input id="weight" type="number" step="0.1" v-model="form.weight" class="h-14 bg-muted/30 border-none rounded-2xl px-6 text-lg font-black focus:ring-2 focus:ring-accent" placeholder="65.2" />
                                <p v-if="form.errors.weight" class="text-destructive text-[10px] font-bold">{{ form.errors.weight }}</p>
                            </div>
                            <div class="flex flex-col gap-2 col-span-2">
                                <Label for="category" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-60 flex items-center gap-2">
                                    <Layers class="w-2.5 h-2.5" /> Kategori
                                </Label>
                                <CustomSelect
                                    v-model="form.category"
                                    :options="categoryOptions"
                                    placeholder="Pilih Kategori"
                                />
                                <p v-if="form.errors.category" class="text-destructive text-[10px] font-bold">{{ form.errors.category }}</p>
                            </div>
                            <div class="flex flex-col gap-2 col-span-2">
                                <Label for="recorded_at" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-60 flex items-center gap-2">
                                    <Calendar class="w-2.5 h-2.5" /> Tanggal Perekaman
                                </Label>
                                <DatePicker v-model="form.recorded_at" />
                                <p v-if="form.errors.recorded_at" class="text-destructive text-[10px] font-bold">{{ form.errors.recorded_at }}</p>
                                <p v-if="$page.props.errors.date_of_birth" class="text-destructive text-[10px] font-bold">{{ $page.props.errors.date_of_birth }}</p>
                            </div>

                            <button 
                                type="submit"
                                :disabled="form.processing || !user.date_of_birth"
                                class="col-span-2 py-5 bg-accent text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-accent/20 hover:bg-accent/90 transition-all active:scale-[0.98] disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Data Fisik Saya' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
