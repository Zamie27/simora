<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface PhysicalMetric {
    height: number;
    weight: number;
    age: number;
    category: string;
    recorded_at: string;
}

interface Athlete {
    id: number;
    name: string;
    email: string;
    latest_physical_metric?: PhysicalMetric | null;
}

defineProps<{
    athletes: Athlete[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Atlet Saya', href: '/coach/athletes' },
];
</script>

<template>
    <Head title="Atlet Saya" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-10 p-6 md:p-10 max-w-7xl mx-auto w-full min-h-screen bg-background text-foreground">
            
            <div class="flex flex-col md:flex-row items-baseline justify-between gap-4 border-b border-border pb-6">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase tracking-tighter">Monitoring Atlet Saya</h1>
                    <p class="mt-2 text-muted-foreground font-medium italic opacity-80">List atlet yang berada di bawah bimbingan Anda.</p>
                </div>
            </div>

            <div v-if="athletes.length === 0" class="flex flex-col items-center justify-center p-20 bg-muted/10 border border-dashed border-border rounded-3xl">
                <p class="text-muted-foreground font-black uppercase tracking-widest text-xs italic">Anda belum memiliki atlet yang ditugaskan.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="athlete in athletes" :key="athlete.id" class="bg-card border border-border p-6 rounded-2xl flex flex-col justify-between hover:border-accent group transition-all shadow-xl hover:shadow-accent/5">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-full bg-secondary flex items-center justify-center text-secondary-foreground font-black text-xs border border-accent/20">
                            {{ athlete.name.split(' ').map(n => n[0]).slice(0, 2).join('') }}
                        </div>
                        <div class="overflow-hidden">
                            <h3 class="text-sm font-black text-foreground uppercase tracking-tight truncate">{{ athlete.name }}</h3>
                            <p class="text-[10px] text-muted-foreground font-bold truncate opacity-70">{{ athlete.email }}</p>
                        </div>
                    </div>

                    <div class="bg-muted/30 rounded-xl p-4 mb-6 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground opacity-60">Latest Weight</p>
                            <p class="text-lg font-black text-foreground">{{ athlete.latest_physical_metric?.weight || '--' }} <span class="text-[10px] text-muted-foreground">KG</span></p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground opacity-60">Latest Height</p>
                            <p class="text-lg font-black text-foreground">{{ athlete.latest_physical_metric?.height || '--' }} <span class="text-[10px] text-muted-foreground">CM</span></p>
                        </div>
                        <div class="col-span-2">
                             <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground opacity-60">Category</p>
                             <p class="text-xs font-black text-accent uppercase">{{ athlete.latest_physical_metric?.category || 'No Category' }}</p>
                        </div>
                    </div>

                    <Link 
                        :href="`/coach/athletes/${athlete.id}`"
                        class="w-full text-center py-3 bg-accent text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-accent/90 transition-all shadow-lg shadow-accent/20"
                    >
                        Detail & Update Fisik
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
