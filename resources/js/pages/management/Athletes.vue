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
    router.get(
        '/management/athletes',
        {
            coach_id: selectedCoach.value,
            sort: sortField.value,
            direction: sortDirection.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
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
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <div
                class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row"
            >
                <div>
                    <h1
                        class="text-3xl font-black tracking-tight text-foreground uppercase"
                    >
                        Monitoring Atlet & Pelatih
                    </h1>
                    <p class="mt-2 font-medium text-muted-foreground">
                        Pantau profil dan daftar atlet beserta coach yang
                        melatih mereka.
                    </p>
                </div>
            </div>

            <!-- Filters Section -->
            <div
                class="grid grid-cols-1 gap-6 rounded-2xl border border-border bg-card p-6 shadow-xl md:grid-cols-3"
            >
                <div class="flex flex-col gap-2">
                    <Label
                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                        >Filter Berdasarkan Pelatih</Label
                    >
                    <select
                        v-model="selectedCoach"
                        class="w-full appearance-none rounded-xl border border-muted bg-background bg-[length:1em_1em] bg-[right_0.75rem_center] bg-no-repeat px-3 py-2.5 text-xs font-bold text-foreground focus:ring-accent focus:outline-none"
                    >
                        <option value="">Semua Pelatih</option>
                        <option
                            v-for="coach in coaches"
                            :key="coach.id"
                            :value="coach.id"
                        >
                            {{ coach.name }}
                        </option>
                    </select>
                </div>

                <div class="flex flex-col gap-2">
                    <Label
                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                        >Urutkan Berdasarkan</Label
                    >
                    <div class="flex gap-2">
                        <select
                            v-model="sortField"
                            class="flex-1 rounded-xl border border-muted bg-background px-3 py-2.5 text-xs font-bold text-foreground focus:ring-accent focus:outline-none"
                        >
                            <option value="name">Nama Atlet</option>
                            <option value="email">Email</option>
                            <option value="created_at">
                                Tanggal Terdaftar
                            </option>
                        </select>
                        <button
                            @click="
                                sortDirection =
                                    sortDirection === 'asc' ? 'desc' : 'asc'
                            "
                            class="rounded-xl border border-muted bg-background px-3 text-muted-foreground transition-all hover:bg-accent hover:text-white"
                        >
                            <svg
                                v-if="sortDirection === 'asc'"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"
                                ></path>
                            </svg>
                            <svg
                                v-else
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 4h13M3 8h9m-9 4h9m5-1v12m0 0l-4-4m4 4l4-4"
                                ></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex flex-col justify-end gap-2">
                    <p
                        class="mb-1 text-right text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                    >
                        Total Atlet: {{ athletes.length }}
                    </p>
                    <div class="flex justify-end gap-2">
                        <button
                            class="font-inter rounded-xl border border-accent/20 bg-accent/10 px-4 py-2.5 text-[10px] font-black tracking-widest text-accent uppercase transition-all hover:bg-accent/20"
                        >
                            Download CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Athletes List -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div
                    v-for="athlete in athletes"
                    :key="athlete.id"
                    class="group relative flex items-center justify-between overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-lg transition-all hover:border-accent/40 hover:shadow-accent/5"
                >
                    <div
                        class="absolute -top-4 -right-4 h-20 w-20 rounded-full bg-accent/5 blur-2xl transition-all group-hover:bg-accent/10"
                    ></div>

                    <div class="relative z-10 flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border border-secondary-foreground/10 bg-secondary text-xs font-black text-secondary-foreground uppercase shadow-inner"
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
                            <h3
                                class="text-sm font-black tracking-tight text-foreground uppercase"
                            >
                                {{ athlete.name }}
                            </h3>
                            <p
                                class="max-w-[150px] truncate text-[11px] font-bold text-muted-foreground"
                            >
                                {{ athlete.email }}
                            </p>
                            <div class="mt-2 flex items-center gap-2">
                                <span
                                    class="rounded border border-accent/10 bg-accent/5 px-2 py-0.5 text-[10px] font-black tracking-widest text-accent uppercase"
                                    >ATLET</span
                                >
                                <span
                                    class="text-[10px] font-semibold text-muted-foreground"
                                    >Tgl. Terdaftar:
                                    {{ formatDate(athlete.created_at) }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <div class="relative z-10 text-right">
                        <p
                            class="mb-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                        >
                            DILATIH OLEH
                        </p>
                        <div
                            v-if="athlete.coach"
                            class="flex flex-col items-end"
                        >
                            <span
                                class="text-xs font-black text-foreground uppercase"
                                >{{ athlete.coach.name }}</span
                            >
                            <span
                                class="text-[9px] font-black tracking-tighter text-accent/80 uppercase italic"
                                >CERTIFIED COACH</span
                            >
                        </div>
                        <span
                            v-else
                            class="text-xs font-black text-destructive/80 uppercase"
                            >No Coach Assigned</span
                        >
                    </div>
                </div>
            </div>

            <div
                v-if="athletes.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-muted/20 p-20"
            >
                <p
                    class="text-xs font-black tracking-widest text-muted-foreground uppercase italic"
                >
                    Tidak ada atlet yang cocok dengan filter.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
