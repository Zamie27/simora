<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <div
            class="relative hidden h-full flex-col border-r border-border p-10 text-white lg:flex"
        >
            <div
                class="absolute inset-0 z-10 bg-primary/80 mix-blend-multiply"
            />
            <img
                src="/images/cycling-bg.png"
                alt="Cycling"
                class="absolute inset-0 z-0 h-full w-full object-cover"
            />
            <Link
                :href="home()"
                class="relative z-20 flex items-center text-lg font-medium"
            >
                <AppLogoIcon class="mr-3 size-10 fill-current text-accent" />
                <span
                    class="text-2xl font-black tracking-widest text-white italic"
                    >SIMORA</span
                >
            </Link>
            <div class="relative z-20 mt-auto">
                <blockquote class="space-y-4">
                    <p class="text-xl leading-relaxed font-medium">
                        &ldquo;Sistem Informasi Monitoring Atlet Sepeda terbaik
                        untuk mencatat, menganalisis, dan meningkatkan performa
                        latihan&rdquo;
                    </p>
                    <footer class="flex items-center gap-2 text-sm opacity-80">
                        <span class="inline-block h-px w-6 bg-accent"></span>
                        Pemantauan Prestasi Tanpa Batas
                    </footer>
                </blockquote>
            </div>
        </div>
        <div class="lg:p-8">
            <div
                class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]"
            >
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-xl font-medium tracking-tight" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
