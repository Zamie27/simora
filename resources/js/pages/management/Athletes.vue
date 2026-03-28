<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface User {
    id: number;
    name: string;
    email: string;
    coach?: { name: string };
    created_at: string;
}

interface Coach {
    id: number;
    name: string;
}

const props = defineProps<{
    athletes: User[];
    coaches: Coach[];
    filters: {
        coach_id?: string;
        sort?: string;
        direction?: string;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Daftar Atlet Terverifikasi', href: '/management/athletes' },
];

const selectedCoach = ref(props.filters.coach_id || '');
const sortField = ref(props.filters.sort || 'name');
const sortDirection = ref(props.filters.direction || 'asc');

const updateFilters = () => {
    router.get('/management/athletes', {
        coach_id: selectedCoach.value,
        sort: sortField.value,
        direction: sortDirection.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch([selectedCoach, sortField, sortDirection], () => {
    updateFilters();
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Monitoring Atlet" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-10 p-6 md:p-10 max-w-7xl mx-auto w-full min-h-screen bg-background text-foreground">
            
            <div class="flex flex-col md:flex-row items-baseline justify-between gap-4 border-b border-border pb-6">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">Monitoring Atlet & Pelatih</h1>
                    <p class="mt-2 text-muted-foreground font-medium">Pantau profil dan daftar atlet beserta coach yang melatih mereka.</p>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-card border border-border p-6 rounded-2xl shadow-xl">
                <div class="flex flex-col gap-2">
                    <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Filter Berdasarkan Pelatih</Label>
                    <select 
                        v-model="selectedCoach" 
                        class="w-full rounded-xl border border-muted bg-background px-3 py-2.5 text-xs focus:ring-accent focus:outline-none text-foreground font-bold appearance-none bg-no-repeat bg-[right_0.75rem_center] bg-[length:1em_1em]"
                    >
                        <option value="">Semua Pelatih</option>
                        <option v-for="coach in coaches" :key="coach.id" :value="coach.id">{{ coach.name }}</option>
                    </select>
                </div>

                <div class="flex flex-col gap-2">
                    <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Urutkan Berdasarkan</Label>
                    <div class="flex gap-2">
                        <select 
                            v-model="sortField" 
                            class="flex-1 rounded-xl border border-muted bg-background px-3 py-2.5 text-xs focus:ring-accent focus:outline-none text-foreground font-bold"
                        >
                            <option value="name">Nama Atlet</option>
                            <option value="email">Email</option>
                            <option value="created_at">Tanggal Terdaftar</option>
                        </select>
                        <button 
                            @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                            class="px-3 rounded-xl border border-muted bg-background text-muted-foreground hover:bg-accent hover:text-white transition-all"
                        >
                            <svg v-if="sortDirection === 'asc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-1v12m0 0l-4-4m4 4l4-4"></path></svg>
                        </button>
                    </div>
                </div>
                
                <div class="flex flex-col gap-2 justify-end">
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1 text-right">Total Atlet: {{ athletes.length }}</p>
                    <div class="flex justify-end gap-2">
                        <button class="text-[10px] bg-accent/10 text-accent font-black uppercase tracking-widest px-4 py-2.5 rounded-xl border border-accent/20 hover:bg-accent/20 transition-all font-inter">Download CSV</button>
                    </div>
                </div>
            </div>

            <!-- Athletes List -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="athlete in athletes" :key="athlete.id" class="bg-card border border-border p-5 rounded-2xl flex items-center justify-between hover:border-accent/40 shadow-lg hover:shadow-accent/5 transition-all group overflow-hidden relative">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-accent/5 rounded-full blur-2xl group-hover:bg-accent/10 transition-all"></div>
                    
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-14 h-14 rounded-full bg-secondary flex items-center justify-center text-secondary-foreground font-black text-xs border border-secondary-foreground/10 shadow-inner overflow-hidden uppercase">
                            {{ athlete.name.split(' ').map(n => n[0]).slice(0, 2).join('') }}
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-foreground uppercase tracking-tight">{{ athlete.name }}</h3>
                            <p class="text-[11px] text-muted-foreground font-bold truncate max-w-[150px]">{{ athlete.email }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-[10px] font-black uppercase tracking-widest text-accent bg-accent/5 px-2 py-0.5 rounded border border-accent/10">ATLET</span>
                                <span class="text-[10px] text-muted-foreground font-semibold">Tgl. Terdaftar: {{ formatDate(athlete.created_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right relative z-10">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">DILATIH OLEH</p>
                        <div v-if="athlete.coach" class="flex flex-col items-end">
                            <span class="text-xs font-black text-foreground uppercase">{{ athlete.coach.name }}</span>
                            <span class="text-[9px] text-accent/80 font-black uppercase tracking-tighter italic">CERTIFIED COACH</span>
                        </div>
                        <span v-else class="text-xs font-black text-destructive/80 uppercase">No Coach Assigned</span>
                    </div>
                </div>
            </div>

            <div v-if="athletes.length === 0" class="flex flex-col items-center justify-center p-20 bg-muted/20 border border-dashed border-border rounded-2xl">
                <p class="text-muted-foreground font-black uppercase tracking-widest text-xs italic">Tidak ada atlet yang cocok dengan filter.</p>
            </div>

        </div>
    </AppLayout>
</template>
