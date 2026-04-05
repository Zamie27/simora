<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { MoveLeft, AlertTriangle, ShieldX, Ghost, ServerCrash, Construction } from 'lucide-vue-next';

const props = defineProps<{
    status: number;
}>();

const { resolvedAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');

const errorDetails = computed(() => {
    return {
        401: {
            title: 'Sesi Berakhir',
            icon: ShieldX,
            description: 'Maaf, Anda harus masuk kembali untuk melanjutkan akses ke sistem.',
            color: 'text-amber-500',
        },
        403: {
            title: 'Akses Dibatalkan',
            icon: ShieldX,
            description: 'Maaf, Anda tidak memiliki izin untuk mengakses area protokol ini.',
            color: 'text-red-500',
        },
        404: {
            title: 'Data Tak Ditemukan',
            icon: Ghost,
            description: 'Halaman yang Anda cari telah keluar dari jalur lintasan atau tidak tersedia.',
            color: 'text-accent',
        },
        419: {
            title: 'Sesi Kedaluwarsa',
            icon: AlertTriangle,
            description: 'Halaman telah kedaluwarsa karena tidak ada aktivitas dalam waktu lama. Silakan muat ulang.',
            color: 'text-amber-500',
        },
        500: {
            title: 'Kegagalan Sistem',
            icon: ServerCrash,
            description: 'Terjadi gangguan teknis pada mesin server kami. Mohon coba beberapa saat lagi.',
            color: 'text-red-600',
        },
        503: {
            title: 'Pemeliharaan',
            icon: Construction,
            description: 'Sistem sedang dalam optimalisasi teknis rutin. Kami akan kembali secepat mungkin.',
            color: 'text-blue-500',
        },
    }[props.status] || {
        title: 'Kesalahan Sistem',
        icon: AlertTriangle,
        description: 'Terjadi gangguan yang tidak terduga pada sistem telemetri.',
        color: 'text-accent',
    };
});

const reloadPage = () => {
    window.location.reload();
};
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col items-center justify-center p-6 transition-colors duration-700"
        :class="isDark ? 'bg-slate-950 text-white' : 'bg-slate-50 text-slate-900'"
    >
        <Head :title="`${status} - ${errorDetails.title}`" />

        <!-- Carbon Texture Overlay -->
        <div class="pointer-events-none absolute inset-0 z-0 opacity-[0.03]" :class="{ 'opacity-[0.07]': isDark }">
            <div class="h-full w-full bg-[radial-gradient(circle_at_1px_1px,rgba(var(--v-theme-on-background),1)_1px,transparent_1px)] bg-[size:32px_32px]"></div>
        </div>

        <div class="relative z-10 w-full max-w-2xl text-center">
            <!-- Large Status Code Watermark -->
            <div
                class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 select-none text-[15rem] font-black italic leading-none opacity-[0.04]"
                :class="isDark ? 'text-white' : 'text-slate-900'"
            >
                {{ status }}
            </div>

            <!-- Content Group -->
            <div class="relative py-20">
                <div class="mb-10 flex justify-center">
                    <div class="relative">
                        <component
                            :is="errorDetails.icon"
                            class="h-24 w-24 animate-pulse-subtle"
                            :class="errorDetails.color"
                        />
                        <div class="absolute -inset-4 z-[-1] animate-ping rounded-full opacity-20" :class="errorDetails.color.replace('text-', 'bg-')"></div>
                    </div>
                </div>

                <div class="mb-4 flex items-center justify-center gap-4 overflow-hidden">
                    <span class="h-px w-12 bg-accent opacity-50"></span>
                    <span class="text-xs font-black tracking-[0.6em] text-accent uppercase">Protocol Error</span>
                    <span class="h-px w-12 bg-accent opacity-50"></span>
                </div>

                <h1 class="display-md mb-6 leading-none italic tracking-tighter">
                    {{ errorDetails.title }}
                </h1>

                <p class="mx-auto mb-16 max-w-md text-sm font-medium tracking-widest text-foreground/60 uppercase leading-relaxed">
                    {{ errorDetails.description }}
                </p>

                <div class="flex flex-col items-center justify-center gap-6 sm:flex-row">
                    <Link
                        href="/"
                        class="group flex items-center gap-3 rounded-sm border border-accent/30 bg-accent/5 px-8 py-4 text-xs font-black tracking-[0.3em] uppercase text-accent transition-all hover:bg-accent hover:text-white"
                    >
                        <MoveLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        Kembali Ke Beranda
                    </Link>

                    <button
                        @click="reloadPage"
                        class="text-xs font-black tracking-[0.3em] uppercase text-foreground/40 transition-colors hover:text-accent"
                    >
                        Muat Ulang Laman
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Meta -->
        <div class="absolute bottom-10 left-0 right-0 text-center">
            <span class="text-[0.6rem] font-black tracking-[0.5em] text-foreground/20 uppercase italic">
                SIMORA KINETIC ARCHIVE — SUBSYSTEM {{ status }}
            </span>
        </div>
    </div>
</template>

<style scoped>
.display-md {
    font-size: clamp(2.5rem, 8vw, 4.5rem);
    font-weight: 950;
    text-transform: uppercase;
}

@keyframes bounce-subtle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.animate-pulse-subtle {
    animation: bounce-subtle 4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

.perspective-2000 {
    perspective: 2000px;
}
</style>
