<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GearCalculator from '@/components/tools/GearCalculator.vue';
import AppLayout from '@/layouts/AppLayout.vue';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Gear Calculator', href: '/tools/gear-calculator' },
];
</script>

<template>
    <Head title="Gear Calculator" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-foreground uppercase italic">Athletic Tools</h1>
                <p class="mt-2 text-[10px] font-medium tracking-widest text-muted-foreground uppercase italic opacity-80">
                    High-performance utilities for road cycling precision.
                </p>
            </div>

            <GearCalculator />
        </div>
    </AppLayout>
</template>
