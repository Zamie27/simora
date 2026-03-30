<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    Calendar,
    ChevronRight,
    MapPin,
    Trophy,
    History,
    FileText,
    Activity,
    Award,
    X,
    Users,
} from 'lucide-vue-next';
import { ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';

interface EventType {
    id: number;
    name: string;
}

interface EventPoint {
    id: number;
    name: string;
}

interface Event {
    id: number;
    title: string;
    description: string | null;
    location: string | null;
    event_date: string;
    event_type_id: number | null;
    type: EventType | null;
    athletes: {
        id: number;
        name: string;
        pivot: {
            event_point_id: number | null;
            point: EventPoint | null;
            status: string;
        }
    }[];
    pivot: {
        status: string;
        result: string | null;
        notes: string | null;
        event_point_id: number | null;
        point: EventPoint | null;
    };
}

defineProps<{
    upcomingEvents: Event[];
    pastEvents: Event[];
}>();

const { props: pageProps } = usePage();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Agenda Event', href: '/athlete/events' },
];

const selectedEvent = ref<Event | null>(null);
const showDetailModal = ref(false);


const openDetail = (event: Event) => {
    selectedEvent.value = event;
    showDetailModal.value = true;
};


const formatDate = (date: string) => {
    const d = new Date(date);

    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const getTypeColor = (id: number | null) => {
    if (!id) {
        return 'bg-muted text-muted-foreground border-border';
    }
    const colors = [
        'bg-orange-500/10 text-orange-500 border-orange-500/20',
        'bg-blue-500/10 text-blue-500 border-blue-500/20',
        'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
        'bg-purple-500/10 text-purple-500 border-purple-500/20',
    ];
    return colors[id % colors.length];
};

const getStatusColor = (status: string) => {
    switch(status) {
        case 'participated': return 'text-emerald-500';
        case 'planned': return 'text-accent';
        case 'cancelled': return 'text-destructive';
        default: return 'text-muted-foreground';
    }
};
</script>

<template>
    <Head title="Agenda & Riwayat Event" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10">
            
            <!-- Header -->
            <div class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-orange-500/10 p-3 text-orange-500">
                        <Award class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tighter text-foreground uppercase md:text-4xl">Target & Agenda Event</h1>
                        <p class="mt-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Your competition roadmap & achievements</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <!-- Upcoming Agenda (Left) -->
                <div class="lg:col-span-12 flex flex-col gap-8">
                    <div class="flex items-center gap-4">
                         <div class="rounded-xl bg-secondary p-3 text-accent"><Trophy class="h-5 w-5" /></div>
                        <h2 class="text-2xl font-black uppercase tracking-tight italic">Upcoming Missions</h2>
                    </div>

                    <div v-if="upcomingEvents.length === 0" class="rounded-[3rem] border border-dashed border-border p-16 text-center bg-card shadow-xl opacity-60">
                        <p class="text-xs font-black tracking-[0.2em] text-muted-foreground uppercase opacity-40">No scheduled events ahead.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="event in upcomingEvents" :key="event.id" 
                            @click="openDetail(event)"
                            class="group cursor-pointer relative overflow-hidden rounded-[2.5rem] border border-border bg-card p-8 shadow-xl transition-all hover:border-accent hover:shadow-2xl">
                            
                            <div class="flex flex-col h-full justify-between gap-6">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span :class="getTypeColor(event.event_type_id)" class="rounded-lg border px-3 py-1 text-[8px] font-black tracking-widest uppercase">
                                            {{ event.type?.name || 'Uncategorized' }}
                                        </span>
                                        <span class="text-[9px] font-black text-accent uppercase tracking-widest">Planned</span>
                                    </div>

                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-[10px] font-black text-accent uppercase">
                                            <Calendar class="h-3 w-3" /> {{ formatDate(event.event_date) }}
                                        </div>
                                        <h4 class="text-xl font-black uppercase italic tracking-tighter text-foreground group-hover:text-accent transition-colors leading-tight">
                                            {{ event.title }}
                                        </h4>
                                        <div v-if="event.pivot.point" class="flex items-center gap-2 text-[9px] font-black text-orange-500 uppercase tracking-widest bg-orange-500/5 px-2 py-1 rounded-lg w-fit">
                                            Category: {{ event.pivot.point.name }}
                                        </div>
                                        <div v-if="event.location" class="flex items-center gap-2 text-[10px] font-bold text-muted-foreground italic opacity-60">
                                            <MapPin class="h-3 w-3" /> {{ event.location }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between border-t border-border pt-4">
                                    <span class="text-[9px] font-black text-muted-foreground uppercase opacity-40">Read Details</span>
                                    <ChevronRight class="h-4 w-4 text-muted-foreground group-hover:translate-x-1 group-hover:text-accent transition-all" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Participation History (Bottom) -->
                <div class="lg:col-span-12 flex flex-col gap-8">
                     <div class="flex items-center gap-4">
                        <div class="rounded-xl bg-secondary p-3 text-accent"><History class="h-5 w-5" /></div>
                        <h2 class="text-2xl font-black uppercase tracking-tight italic">Achievement & History</h2>
                    </div>

                    <div v-if="pastEvents.length === 0" class="rounded-[3rem] border border-border p-16 text-center bg-card shadow-xl opacity-30">
                        <Award class="mx-auto mb-4 h-10 w-10 text-muted-foreground opacity-10" />
                        <p class="text-xs font-black tracking-[0.2em] text-muted-foreground uppercase opacity-40">Your legacy is yet to be written.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                         <div v-for="event in pastEvents" :key="event.id" 
                            @click="openDetail(event)"
                            class="group cursor-pointer relative overflow-hidden rounded-[2.5rem] border border-border bg-card p-8 shadow-xl transition-all hover:border-accent hover:shadow-2xl">
                            
                            <div class="flex flex-col h-full justify-between gap-6">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span :class="getTypeColor(event.event_type_id)" class="rounded-lg border px-3 py-1 text-[8px] font-black tracking-widest uppercase">
                                            {{ event.type?.name || 'Uncategorized' }}
                                        </span>
                                        <div v-if="event.pivot.result" class="flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-3 py-1 text-[10px] font-black text-emerald-500">
                                            <Award class="h-3 w-3" /> {{ event.pivot.result }}
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                         <div class="flex items-center gap-2 text-[10px] font-black text-muted-foreground uppercase opacity-60">
                                            <Calendar class="h-3 w-3" /> {{ formatDate(event.event_date) }}
                                        </div>
                                        <h4 class="text-lg font-black uppercase italic tracking-tighter text-foreground group-hover:text-accent transition-colors leading-tight">
                                            {{ event.title }}
                                        </h4>
                                        <div v-if="event.pivot.point" class="text-[9px] font-bold text-muted-foreground uppercase opacity-60">
                                            Kategori: {{ event.pivot.point.name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between border-t border-border pt-4">
                                    <div class="flex flex-col">
                                        <span class="text-[8px] font-black text-muted-foreground uppercase opacity-40">Participation Status</span>
                                        <span :class="getStatusColor(event.pivot.status)" class="text-[10px] font-black uppercase">{{ event.pivot.status }}</span>
                                    </div>
                                    <ChevronRight class="h-4 w-4 text-muted-foreground group-hover:translate-x-1 group-hover:text-accent transition-all" />
                                </div>
                            </div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Detail Modal -->
            <div v-if="showDetailModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-background/95 p-4 backdrop-blur-3xl">
                <div class="w-full max-w-3xl animate-in fade-in zoom-in duration-300 rounded-[3rem] border border-white/10 bg-card shadow-2xl overflow-hidden relative">
                    <!-- Close button in corner -->
                    <button @click="showDetailModal = false" class="absolute right-8 top-8 z-10 rounded-full bg-white/5 p-4 text-muted-foreground hover:bg-white/10 hover:text-white transition-all shadow-xl backdrop-blur-md">
                        <X class="h-6 w-6" />
                    </button>

                    <div class="flex items-center justify-between border-b border-white/5 bg-accent/5 p-8 md:p-10">
                        <div>
                            <p class="text-[10px] font-black tracking-[0.2em] text-accent uppercase opacity-80 italic">Event Overview</p>
                            <h2 class="text-2xl font-black tracking-tighter text-foreground uppercase italic mt-1">{{ selectedEvent?.title }}</h2>
                            <div v-if="selectedEvent?.pivot.point" class="mt-2 text-[10px] font-black text-orange-500 uppercase tracking-widest">
                                Assigned Category: {{ selectedEvent.pivot.point.name }}
                            </div>
                        </div>
                    </div>

                    <div class="max-h-[75vh] overflow-y-auto p-10 custom-scrollbar flex flex-col gap-10">
                        <!-- Core Info -->
                        <div class="grid grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2 rounded-2xl border border-border bg-muted/10 p-6">
                                <div class="flex items-center gap-2 text-[10px] font-black text-muted-foreground uppercase opacity-60">
                                    <Calendar class="h-3 w-3" /> Tanggal
                                </div>
                                <p class="text-base font-black">{{ selectedEvent ? formatDate(selectedEvent.event_date) : '' }}</p>
                            </div>
                            <div class="flex flex-col gap-2 rounded-2xl border border-border bg-muted/10 p-6">
                                <div class="flex items-center gap-2 text-[10px] font-black text-muted-foreground uppercase opacity-60">
                                    <MapPin class="h-3 w-3" /> Lokasi
                                </div>
                                <p class="text-base font-black truncate">{{ selectedEvent?.location || 'Not Specified' }}</p>
                            </div>
                        </div>

                        <!-- Description Box -->
                        <div v-if="selectedEvent?.description" class="space-y-4">
                             <h4 class="text-[10px] font-black uppercase text-accent tracking-[0.3em] flex items-center gap-2 italic">
                                <FileText class="h-3 w-3" /> Target & Informasi Pelatih
                            </h4>
                            <div class="rounded-3xl border border-border bg-muted/5 p-8">
                                <p class="text-sm font-medium italic text-muted-foreground leading-relaxed">
                                    "{{ selectedEvent.description }}"
                                </p>
                            </div>
                        </div>

                        <!-- Results & Notes (for past events) -->
                        <div v-if="selectedEvent && new Date(selectedEvent.event_date) < new Date()" class="flex flex-col gap-8 border-t border-border pt-10">
                            <div class="flex flex-col gap-6">
                                <h4 class="text-[10px] font-black uppercase text-emerald-500 tracking-[0.3em] flex items-center gap-2 italic">
                                    <Award class="h-3.5 w-3.5" /> Hasil Partisipasi
                                </h4>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div class="flex flex-col gap-2 rounded-2xl bg-emerald-500/5 border border-emerald-500/20 p-6">
                                        <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest">Achieved Result</span>
                                        <p class="text-xl font-black text-foreground italic">{{ selectedEvent.pivot.result || 'No Result Recorded' }}</p>
                                    </div>
                                    <div class="flex flex-col gap-2 rounded-2xl bg-muted/10 border border-border p-6">
                                        <span class="text-[8px] font-black text-muted-foreground uppercase opacity-40 tracking-widest">Report Status</span>
                                        <p class="text-xl font-black text-foreground uppercase tracking-tighter">{{ selectedEvent.pivot.status }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="selectedEvent.pivot.notes" class="flex flex-col gap-4">
                                <h4 class="text-[10px] font-black uppercase text-muted-foreground tracking-[0.3em] flex items-center gap-2 italic">
                                    <Activity class="h-3 w-3" /> Catatan Tambahan
                                </h4>
                                <p class="text-sm font-medium italic text-muted-foreground opacity-80 px-4">
                                    {{ selectedEvent.pivot.notes }}
                                </p>
                            </div>
                        </div>

                        <!-- Participant List for Athletes -->
                        <div v-if="selectedEvent?.athletes?.length" class="flex flex-col gap-6 border-t border-border pt-10">
                            <h4 class="flex items-center gap-2 text-xs font-black tracking-widest uppercase">
                                <Users class="h-4 w-4 text-orange-500" /> Daftar Atlet Peserta ({{ selectedEvent.athletes.length }})
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="athlete in selectedEvent.athletes" :key="athlete.id" 
                                    :class="athlete.id === pageProps.auth.user.id ? 'border-accent bg-accent/5' : 'border-border/50 bg-muted/10'"
                                    class="flex items-center gap-3 rounded-2xl border p-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-card text-[10px] font-black shadow-sm">
                                        {{ athlete.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-black uppercase truncate">{{ athlete.name }}</span>
                                        <span v-if="athlete.pivot.point" class="text-[8px] font-bold text-accent uppercase">{{ athlete.pivot.point.name }}</span>
                                        <span v-else class="text-[8px] font-bold text-muted-foreground uppercase opacity-40">General</span>
                                    </div>
                                    <div v-if="athlete.id === pageProps.auth.user.id" class="ml-auto">
                                        <div class="rounded-full bg-accent/20 px-2 py-0.5 text-[8px] font-black text-accent uppercase">You</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button @click="showDetailModal = false" class="md:hidden w-full py-6 rounded-[2rem] bg-accent text-[10px] font-black uppercase tracking-widest text-white shadow-xl">
                            Close Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.1);
}
</style>
