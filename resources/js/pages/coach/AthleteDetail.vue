<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import { AlertCircle, Calendar, Layers, Plus, Ruler, User as UserIcon, Weight } from 'lucide-vue-next';
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

interface Athlete {
    id: number;
    name: string;
    email: string;
    date_of_birth: string | null;
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
    category: props.athlete.physical_metrics[0]?.category || '',
    recorded_at: new Date().toISOString().split('T')[0],
});

const categoryOptions = computed(() =>
    props.categories.map(c => ({ value: c.name, label: c.name }))
);

const submit = () => {
    form.post(`/coach/athletes/${props.athlete.id}/metrics`, {
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
        id: 'athlete-metrics',
        theme: 'dark',
        background: 'transparent',
        toolbar: { show: false },
    },
    colors: ['#FF6120', '#102844'],
    stroke: { curve: 'smooth', width: 3 },
    xaxis: {
        categories: [...props.athlete.physical_metrics].reverse().map((m) => formatDate(m.recorded_at)),
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
        data: [...props.athlete.physical_metrics].reverse().map((m) => Number(m.weight)),
    },
    {
        name: 'Height (cm)',
        data: [...props.athlete.physical_metrics].reverse().map((m) => Number(m.height)),
    },
]);

const getChange = (current: number, index: number, field: 'weight' | 'height') => {
    const next = props.athlete.physical_metrics[index + 1];
    
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
    <Head :title="`Detail Atlet | ${athlete.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-10 p-6 md:p-10 max-w-7xl mx-auto w-full min-h-screen bg-background text-foreground">
            
            <div class="flex flex-col md:flex-row items-baseline justify-between gap-4 border-b border-border pb-6">
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 rounded-full bg-accent/20 flex items-center justify-center text-accent text-2xl font-black border-2 border-accent/20 shadow-2xl shadow-accent/10">
                         {{ athlete.name.split(' ').map(n => n[0]).slice(0, 2).join('') }}
                    </div>
                    <div>
                        <h1 class="text-4xl font-black tracking-tighter text-foreground uppercase tracking-tighter">{{ athlete.name }}</h1>
                        <p class="mt-1 text-muted-foreground font-bold tracking-widest opacity-60 uppercase text-xs">{{ athlete.email }}</p>
                    </div>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="bg-accent hover:bg-accent/90 text-white font-black uppercase tracking-widest text-xs px-8 py-4 rounded-xl shadow-xl shadow-accent/20 transition-all active:scale-95 flex items-center gap-2"
                >
                    <Plus class="w-4 h-4" /> Update Data Fisik
                </button>
            </div>

            <!-- Danger Zone/Warning for Coach -->
            <div v-if="!athlete.date_of_birth" class="bg-destructive/10 border border-destructive/20 p-6 rounded-3xl flex items-center justify-between gap-6 group">
                <div class="flex items-center gap-6">
                    <div class="w-14 h-14 bg-destructive rounded-2xl flex items-center justify-center text-white shadow-xl shadow-destructive/20 group-hover:scale-110 transition-transform">
                        <AlertCircle class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-foreground uppercase tracking-tight">Data Profil Tidak Lengkap</h3>
                        <p class="text-xs text-muted-foreground font-medium italic opacity-70">Atlet ini belum mengisi tanggal lahir. Beritahu atlet untuk mengisinya di pengaturan akun agar usia dapat dihitung otomatis.</p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Chart Area -->
                <div class="lg:col-span-8 bg-card border border-border rounded-3xl p-8 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-accent/5 rounded-full blur-[100px] -mr-32 -mt-32"></div>
                    <div class="flex items-center gap-4 mb-8">
                         <div class="p-3 bg-secondary rounded-xl text-accent">
                             <Layers class="w-5 h-5" />
                         </div>
                         <h3 class="text-xl font-black uppercase tracking-tight">Progress Grafik Fisik</h3>
                    </div>
                    <div id="chart">
                        <VueApexCharts width="100%" height="350" type="line" :options="chartOptions" :series="chartSeries"></VueApexCharts>
                    </div>
                </div>

                <!-- Latest & History Stats -->
                <div class="lg:col-span-4 flex flex-col gap-8">
                    <div class="bg-secondary rounded-3xl p-8 border border-white/5 shadow-2xl relative group overflow-hidden">
                        <div class="absolute -right-4 -top-4 w-32 h-32 bg-white/5 rounded-full scale-150 rotate-12 transition-transform group-hover:scale-110"></div>
                        <div class="flex items-center gap-3 mb-6 relative z-10">
                            <div class="p-2 bg-white/5 rounded-lg text-[#a4badd]">
                                <Weight class="w-3 h-3" />
                            </div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-[#a4badd] opacity-60">Latest Metric (Current)</h3>
                        </div>
                        <div class="flex flex-col gap-6 relative z-10">
                            <div>
                                <span class="text-5xl font-black text-white leading-none">{{ athlete.physical_metrics[0]?.weight || '--' }}</span>
                                <span class="text-xs font-black text-[#a4badd] ml-2 opacity-60">KG</span>
                                <p class="text-[10px] uppercase font-black tracking-widest text-[#a4badd] opacity-40 mt-1">Weight</p>
                            </div>
                            <div>
                                <span class="text-5xl font-black text-white leading-none">{{ athlete.physical_metrics[0]?.height || '--' }}</span>
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
                                <span class="text-xs font-black text-accent uppercase">{{ athlete.physical_metrics[0]?.category || '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-border/50">
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-50">Usia Saat Terakhir</span>
                                <span class="text-xs font-black text-foreground">{{ athlete.physical_metrics[0]?.age || '-' }} Years</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-50">Last Record</span>
                                <span class="text-xs font-black text-foreground">{{ athlete.physical_metrics[0] ? formatDate(athlete.physical_metrics[0].recorded_at) : '-' }}</span>
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
                     <h2 class="text-2xl font-black text-foreground tracking-tight uppercase">Riwayat Peningkatan Fisik</h2>
                 </div>
                 <div class="bg-card border border-border rounded-3xl overflow-hidden shadow-2xl">
                    <table class="w-full text-left">
                        <thead class="bg-muted/40 border-b border-border">
                            <tr class="text-[10px] uppercase font-black tracking-widest text-muted-foreground">
                                <th class="px-8 py-5">Tanggal</th>
                                <th class="px-8 py-5">Berat (KG)</th>
                                <th class="px-8 py-5">± Berat</th>
                                <th class="px-8 py-5">Tinggi (CM)</th>
                                <th class="px-8 py-5">± Tinggi</th>
                                <th class="px-8 py-5">Usia</th>
                                <th class="px-8 py-5">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="(m, index) in athlete.physical_metrics" :key="m.id" class="hover:bg-muted/10 transition-colors">
                                <td class="px-8 py-6 font-bold text-xs">{{ formatDate(m.recorded_at) }}</td>
                                <td class="px-8 py-6 font-black text-foreground">{{ m.weight }}</td>
                                <td class="px-8 py-6 text-xs" :class="getChange(m.weight, index, 'weight').class">
                                    {{ getChange(m.weight, index, 'weight').text }}
                                </td>
                                <td class="px-8 py-6 font-black text-foreground">{{ m.height }}</td>
                                <td class="px-8 py-6 text-xs" :class="getChange(m.height, index, 'height').class">
                                    {{ getChange(m.height, index, 'height').text }}
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-muted-foreground">{{ m.age }} Thn</td>
                                <td class="px-8 py-6">
                                    <span class="text-[9px] font-black uppercase px-3 py-1.5 rounded-lg bg-accent/10 text-accent border border-accent/20">{{ m.category }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                 </div>
            </div>

            <!-- Update Modal -->
            <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/90 backdrop-blur-xl">
                <div class="bg-card w-full max-w-xl rounded-[2.5rem] border border-border shadow-[0_50px_100px_-20px_rgba(255,97,32,0.15)] overflow-hidden animate-in fade-in zoom-in duration-300">
                    <div class="p-10 border-b border-border flex justify-between items-center bg-muted/20">
                         <div>
                            <h2 class="text-2xl font-black text-foreground uppercase tracking-tight">Record Physical Metric</h2>
                            <p class="text-xs text-muted-foreground font-bold mt-1 uppercase tracking-widest opacity-60">Athlete Monitoring System</p>
                         </div>
                        <button @click="showAddModal = false" class="p-3 rounded-full hover:bg-muted/50 text-muted-foreground hover:text-foreground transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="p-10">
                        <div v-if="!athlete.date_of_birth" class="mb-8 p-6 bg-destructive/10 border border-destructive/20 rounded-2xl">
                             <h4 class="text-destructive text-sm font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                                <AlertCircle class="w-4 h-4" /> Stop!
                             </h4>
                             <p class="text-xs text-foreground/80 font-medium italic">Atlet belum mengisi tanggal lahir. Penginputan data fisik ditangguhkan sampai atlet melengkapi profilnya.</p>
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
                                :disabled="form.processing || !athlete.date_of_birth"
                                class="col-span-2 py-5 bg-accent text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-accent/20 hover:bg-accent/90 transition-all active:scale-[0.98] disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Data Performa Fisik' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
