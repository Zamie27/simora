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
    athlete_profile?: {
        profile_photo_path?: string;
    } | null;
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
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <div
                class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row"
            >
                <div>
                    <h1
                        class="text-3xl font-black tracking-tighter text-foreground uppercase"
                    >
                        Monitoring Atlet Saya
                    </h1>
                    <p
                        class="mt-2 font-medium text-muted-foreground italic opacity-80"
                    >
                        List atlet yang berada di bawah bimbingan Anda.
                    </p>
                </div>
            </div>

            <div
                v-if="athletes.length === 0"
                class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-border bg-muted/10 p-20"
            >
                <p
                    class="text-xs font-black tracking-widest text-muted-foreground uppercase italic"
                >
                    Anda belum memiliki atlet yang ditugaskan.
                </p>
            </div>

            <div
                v-else
                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="athlete in athletes"
                    :key="athlete.id"
                    class="group flex flex-col justify-between rounded-2xl border border-border bg-card p-6 shadow-xl transition-all hover:border-accent hover:shadow-accent/5"
                >
                    <div class="mb-6 flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border border-accent/20 bg-secondary text-xs font-black text-secondary-foreground uppercase relative"
                        >
                            <img v-if="athlete.athlete_profile?.profile_photo_path" :src="`/documents/${athlete.id}/profile_photo`" class="h-full w-full object-cover" />
                            <span v-else>{{
                                athlete.name
                                    .split(' ')
                                    .map((n) => n[0])
                                    .slice(0, 2)
                                    .join('')
                            }}</span>
                        </div>
                        <div class="overflow-hidden">
                            <h3
                                class="truncate text-sm font-black tracking-tight text-foreground uppercase"
                            >
                                {{ athlete.name }}
                            </h3>
                            <p
                                class="truncate text-[10px] font-bold text-muted-foreground opacity-70"
                            >
                                {{ athlete.email }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mb-6 grid grid-cols-2 gap-4 rounded-xl bg-muted/30 p-4"
                    >
                        <div>
                            <p
                                class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Latest Weight
                            </p>
                            <p class="text-lg font-black text-foreground">
                                {{
                                    athlete.latest_physical_metric?.weight ||
                                    '--'
                                }}
                                <span class="text-[10px] text-muted-foreground"
                                    >KG</span
                                >
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Latest Height
                            </p>
                            <p class="text-lg font-black text-foreground">
                                {{
                                    athlete.latest_physical_metric?.height ||
                                    '--'
                                }}
                                <span class="text-[10px] text-muted-foreground"
                                    >CM</span
                                >
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p
                                class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Category
                            </p>
                            <p class="text-xs font-black text-accent uppercase">
                                {{
                                    athlete.latest_physical_metric?.category ||
                                    'No Category'
                                }}
                            </p>
                        </div>
                    </div>

                    <Link
                        :href="`/coach/athletes/${athlete.id}`"
                        class="w-full rounded-xl bg-accent py-3 text-center text-[10px] font-black tracking-widest text-white uppercase shadow-lg shadow-accent/20 transition-all hover:bg-accent/90 focus:outline-none"
                    >
                        Detail & Update Fisik
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
