<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useAppearance } from '@/composables/useAppearance';
import { dashboard, login, register } from '@/routes';

const { resolvedAppearance } = useAppearance();
const currentTheme = computed(() =>
    resolvedAppearance.value === 'dark' ? 'simoraDark' : 'simoraLight',
);
const isDark = computed(() => resolvedAppearance.value === 'dark');

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <v-app
        :theme="currentTheme"
        class="kinetic-archive scroll-smooth selection:bg-accent/30"
    >
        <Head title="SIMORA | Kinetic Road Archive" />

        <!-- Navigation: The Slipstream Header -->
        <nav
            class="fixed top-0 right-0 left-0 z-[100] border-b transition-all duration-500"
            :class="
                isDark
                    ? 'bg-surface/80 border-white/5 backdrop-blur-md'
                    : 'border-black/5 bg-white/80 backdrop-blur-md'
            "
        >
            <div
                class="mx-auto flex max-w-[1600px] items-center justify-between px-6 py-5 md:px-12"
            >
                <Link
                    href="/"
                    class="text-decoration-none group flex items-center"
                >
                    <AppLogoIcon
                        class="h-10 text-accent transition-transform duration-700 group-hover:scale-110 md:h-12"
                    />
                </Link>

                <div class="hidden items-center gap-16 lg:flex">
                    <a href="#protocol" class="nav-link">PROTOKOL</a>
                    <a href="#metric" class="nav-link">METRIK</a>
                    <a href="#identity" class="nav-link">IDENTITAS</a>
                </div>

                <div class="flex items-center gap-4 lg:gap-8">
                    <AppearanceTabs class="hidden md:flex" />

                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard().url"
                        class="nav-link font-black text-accent opacity-100"
                    >
                        DASHBOARD
                    </Link>
                    <template v-else>
                        <Link
                            :href="login().url"
                            class="nav-link px-4 text-[0.8rem] font-black tracking-[0.2em] text-foreground opacity-100 transition-colors hover:text-accent"
                        >
                            MASUK
                        </Link>
                        <v-btn
                            v-if="canRegister"
                            :href="register().url"
                            color="accent"
                            variant="flat"
                            rounded="sm"
                            class="cta-btn px-12 text-[0.75rem] font-black tracking-[0.3em]"
                            height="50"
                        >
                            DAFTAR
                        </v-btn>
                    </template>
                </div>
            </div>
        </nav>

        <v-main class="bg-surface relative overflow-x-hidden">
            <!-- Hero: The Technical Epicenter -->
            <section
                class="relative flex min-h-screen items-center overflow-hidden"
            >
                <!-- Background Imagery: Cinematic focus -->
                <div class="absolute inset-0 z-0">
                    <v-img
                        src="/images/hero-bg.png"
                        cover
                        :class="[
                            'h-full w-full scale-105 transition-all duration-1000',
                            isDark
                                ? 'opacity-100 grayscale-[0.2]'
                                : 'opacity-90 grayscale-[0.3]',
                        ]"
                    >
                        <template #placeholder>
                            <div
                                class="flex h-full items-center justify-center"
                                :class="isDark ? 'bg-slate-900' : 'bg-slate-50'"
                            >
                                <v-progress-circular
                                    indeterminate
                                    color="accent"
                                ></v-progress-circular>
                            </div>
                        </template>
                    </v-img>

                    <!-- Mode-Sensitive Overlays -->
                    <div
                        class="absolute inset-0 z-10"
                        :class="isDark ? 'bg-slate-950/60' : 'bg-white/40'"
                    ></div>
                    <div
                        class="absolute inset-0 z-20 bg-gradient-to-r"
                        :class="
                            isDark
                                ? 'from-slate-950 via-slate-950/80 to-transparent'
                                : 'from-white via-white/70 to-transparent'
                        "
                    ></div>

                    <div
                        class="carbon-texture absolute inset-0 z-30 opacity-[0.05]"
                    ></div>
                </div>

                <v-container
                    fluid
                    class="relative z-40 max-w-[1600px] px-6 pt-16 md:px-12 lg:pt-0"
                >
                    <v-row class="min-h-[70vh] items-center">
                        <v-col
                            cols="12"
                            lg="6"
                            class="hero-text-block mb-12 lg:mb-0"
                        >
                            <div
                                class="mb-10 inline-flex items-center gap-4 overflow-hidden"
                            >
                                <span class="h-px w-16 bg-accent"></span>
                                <span
                                    class="label-sm font-black tracking-[0.6em] text-accent uppercase"
                                    >Telemetri Performans</span
                                >
                            </div>

                            <h1
                                class="display-lg mb-10 leading-[0.85] text-foreground transition-colors duration-500"
                            >
                                <span class="block">ARSIP</span>
                                <span class="block text-accent italic"
                                    >KINETIK</span
                                >
                                <span class="block">PRESISI.</span>
                            </h1>

                            <p
                                class="body-md mb-14 max-w-[480px] text-[0.75rem] leading-relaxed font-black tracking-[0.15em] text-foreground/70 uppercase transition-colors duration-500"
                            >
                                Dekonstruksi performa atlet balap sepeda melalui
                                monitoring data biometrik harian yang mutakhir.
                            </p>

                            <div class="flex flex-wrap gap-8">
                                <v-btn
                                    v-if="!$page.props.auth.user && canRegister"
                                    :href="register().url"
                                    color="accent"
                                    flat
                                    rounded="sm"
                                    height="68"
                                    class="cta-btn px-16 text-xs font-black tracking-[0.4em]"
                                >
                                    MULAI SEKARANG
                                </v-btn>
                            </div>
                        </v-col>

                        <!-- HUD Widget Group -->
                        <v-col cols="12" lg="6" class="relative">
                            <div
                                class="hud-container group perspective-2000 relative"
                            >
                                <!-- Main Speed Widget -->
                                <div
                                    class="relative z-20 overflow-hidden rounded-sm border p-8 shadow-2xl transition-all duration-700 hover:rotate-y-3 md:p-14 lg:translate-x-12"
                                    :class="
                                        isDark
                                            ? 'border-white/10 bg-white/5 backdrop-blur-2xl'
                                            : 'border-black/5 bg-white/90 backdrop-blur-xl'
                                    "
                                >
                                    <div
                                        class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"
                                    ></div>

                                    <div
                                        class="relative z-10 mb-16 flex items-start justify-between"
                                    >
                                        <div>
                                            <div
                                                class="label-sm mb-2 font-black tracking-widest text-accent uppercase italic"
                                            >
                                                Live Session
                                            </div>
                                            <div
                                                class="title-sm font-black tracking-[0.3em] text-foreground uppercase italic"
                                            >
                                                KOHAR CYCLING CLUB
                                            </div>
                                        </div>
                                        <div class="status-dot">
                                            <div
                                                class="h-3 w-3 animate-pulse rounded-full bg-accent shadow-[0_0_20px_#FF6120]"
                                            ></div>
                                        </div>
                                    </div>

                                    <div
                                        class="relative z-10 mb-16 flex items-baseline gap-10"
                                    >
                                        <div
                                            class="display-md flex items-baseline gap-4 leading-none text-foreground italic"
                                        >
                                            45.8
                                            <span
                                                class="headline-sm tracking-normal uppercase not-italic opacity-40"
                                                >KM/H</span
                                            >
                                        </div>
                                        <v-icon
                                            icon="mdi-trending-up"
                                            color="accent"
                                            size="32"
                                            class="animate-bounce-subtle"
                                        ></v-icon>
                                    </div>

                                    <div
                                        class="relative z-10 grid grid-cols-2 gap-12 border-t pt-12 lg:grid-cols-3"
                                        :class="
                                            isDark
                                                ? 'border-white/10'
                                                : 'border-black/5'
                                        "
                                    >
                                        <div class="metric-segment">
                                            <div
                                                class="label-sm mb-3 font-black tracking-widest text-foreground/40 uppercase"
                                            >
                                                Heart Rate
                                            </div>
                                            <div
                                                class="headline-sm font-black text-foreground italic"
                                            >
                                                174
                                                <span
                                                    class="label-sm not-italic opacity-40"
                                                    >BPM</span
                                                >
                                            </div>
                                        </div>
                                        <div class="metric-segment">
                                            <div
                                                class="label-sm mb-3 font-black tracking-widest text-foreground/40 uppercase"
                                            >
                                                Cadence
                                            </div>
                                            <div
                                                class="headline-sm font-black text-foreground italic"
                                            >
                                                96
                                                <span
                                                    class="label-sm not-italic opacity-40"
                                                    >RPM</span
                                                >
                                            </div>
                                        </div>
                                        <div
                                            class="metric-segment hidden md:block"
                                        >
                                            <div
                                                class="label-sm mb-3 font-black tracking-widest text-foreground/40 uppercase"
                                            >
                                                Power
                                            </div>
                                            <div
                                                class="headline-sm font-black text-accent italic"
                                            >
                                                Z4
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </section>

            <!-- Protocol Section (Compact Padding) -->
            <section
                id="protocol"
                class="bg-surface-container-low relative py-20"
            >
                <v-container class="max-w-[1400px]">
                    <v-row class="mb-16">
                        <v-col cols="12" lg="7">
                            <div
                                class="label-sm mb-4 font-black tracking-[0.6em] text-accent uppercase"
                            >
                                The Precision Protocol
                            </div>
                            <h2
                                class="display-md mb-4 leading-[0.9] tracking-tighter text-foreground uppercase"
                            >
                                DOMINANSI DATA <br />
                                <span class="text-accent italic"
                                    >TANPA KOMPROMI.</span
                                >
                            </h2>
                        </v-col>
                        <v-col cols="12" lg="5">
                            <p
                                class="body-md border-l-2 border-accent/20 pl-8 text-[0.75rem] leading-relaxed font-black tracking-widest text-foreground/50 uppercase lg:mt-16"
                            >
                                Sistem pemantauan yang mendobrak batasan fisik
                                setiap atlet melalui analisis telemetri tingkat
                                tinggi.
                            </p>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col
                            cols="12"
                            md="4"
                            v-for="(item, i) in [
                                {
                                    title: 'TELEMETRI REAL-TIME',
                                    icon: 'mdi-chart-line-variant',
                                    text: 'Pencatatan statistik latihan harian meliputi jarak, kecepatan, dan durasi dengan presisi.',
                                },
                                {
                                    title: 'ANALISIS BIOMETRIK',
                                    icon: 'mdi-heart-pulse',
                                    text: 'Visualisasi zona intensitas detak jantung untuk menjaga performa pada level puncak.',
                                },
                                {
                                    title: 'REKOMENDASI ELITE',
                                    icon: 'mdi-target-variant',
                                    text: 'Umpan balik personal dari pelatih berdasarkan akumulasi data performa historis.',
                                },
                            ]"
                            :key="i"
                        >
                            <v-card
                                flat
                                class="performance-card group h-full cursor-default border-none bg-transparent"
                            >
                                <div
                                    class="bg-surface hover:bg-surface-bright flex h-full flex-col justify-between rounded-sm border-b-2 border-transparent p-12 shadow-sm transition-all duration-700 hover:border-accent hover:shadow-xl"
                                >
                                    <div>
                                        <v-icon
                                            :icon="item.icon"
                                            color="accent"
                                            class="mb-12 transition-transform duration-700 group-hover:scale-110"
                                            size="44"
                                        ></v-icon>
                                        <h3
                                            class="title-sm mb-6 font-black tracking-[0.3em] text-foreground uppercase italic"
                                        >
                                            {{ item.title }}
                                        </h3>
                                        <p
                                            class="label-md leading-relaxed font-medium text-foreground/60"
                                        >
                                            {{ item.text }}
                                        </p>
                                    </div>
                                </div>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-container>
            </section>

            <!-- Identity Section (Compact Padding) -->
            <section
                id="identity"
                class="bg-surface relative overflow-hidden py-20"
            >
                <v-container class="relative z-10 max-w-[1400px]">
                    <div class="mb-16 text-center">
                        <h2
                            class="display-md mb-4 leading-none tracking-tighter text-foreground uppercase italic"
                        >
                            MODUL IDENTITAS.
                        </h2>
                        <span
                            class="label-sm font-black tracking-[1em] text-accent uppercase"
                            >Role Based Access</span
                        >
                    </div>

                    <v-row class="items-stretch gap-y-12">
                        <v-col cols="12" lg="4" class="px-6">
                            <div class="role-module group">
                                <div
                                    class="bg-surface-container-low relative mb-10 aspect-[4/5] overflow-hidden rounded-sm shadow-2xl"
                                >
                                    <v-img
                                        src="/images/role-athlete.png"
                                        cover
                                        class="grayscale transition-all duration-1000 group-hover:scale-105 group-hover:grayscale-0"
                                    />
                                    <div
                                        class="from-surface absolute inset-0 bg-gradient-to-t via-transparent to-transparent opacity-90"
                                    ></div>
                                    <div
                                        class="label-sm absolute top-6 left-6 bg-accent px-4 py-2 font-black tracking-widest text-white italic"
                                    >
                                        ACCESS: 01
                                    </div>
                                </div>
                                <h3
                                    class="headline-sm mb-4 font-black tracking-tight text-foreground uppercase italic transition-colors group-hover:text-accent"
                                >
                                    ATLET
                                </h3>
                                <p
                                    class="label-md max-w-[320px] leading-relaxed font-black tracking-widest text-foreground/50 uppercase"
                                >
                                    INPUT DATA LATIHAN, MONITORING GRAFIK
                                    PERFORMA, DAN EVALUASI PROGRES MANDIRI.
                                </p>
                            </div>
                        </v-col>

                        <v-col cols="12" lg="4" class="px-6">
                            <div class="role-module group">
                                <div
                                    class="bg-surface-container-low relative mb-10 aspect-[4/5] overflow-hidden rounded-sm shadow-2xl"
                                >
                                    <v-img
                                        src="/images/role-coach.png"
                                        cover
                                        class="grayscale transition-all duration-1000 group-hover:scale-105 group-hover:grayscale-0"
                                    />
                                    <div
                                        class="from-surface absolute inset-0 bg-gradient-to-t via-transparent to-transparent opacity-90"
                                    ></div>
                                    <div
                                        class="label-sm absolute top-6 left-6 bg-accent px-4 py-2 font-black tracking-widest text-white italic"
                                    >
                                        ACCESS: 02
                                    </div>
                                </div>
                                <h3
                                    class="headline-sm mb-4 font-black tracking-tight text-foreground uppercase italic transition-colors group-hover:text-accent"
                                >
                                    PELATIH
                                </h3>
                                <p
                                    class="label-md max-w-[320px] leading-relaxed font-black tracking-widest text-foreground/50 uppercase"
                                >
                                    ANALISIS PERFORMA TIM, MONITORING ATLET
                                    SECARA AGREGAT, DAN PEMBERIAN REKOMENDASI
                                    LATIHAN.
                                </p>
                            </div>
                        </v-col>

                        <v-col cols="12" lg="4" class="px-6">
                            <div class="role-module group">
                                <div
                                    class="bg-surface-container-low relative mb-10 aspect-[4/5] overflow-hidden rounded-sm shadow-2xl"
                                >
                                    <v-img
                                        src="/images/role-manager.png"
                                        cover
                                        class="grayscale transition-all duration-1000 group-hover:scale-105 group-hover:grayscale-0"
                                    />
                                    <div
                                        class="from-surface absolute inset-0 bg-gradient-to-t via-transparent to-transparent opacity-90"
                                    ></div>
                                    <div
                                        class="label-sm absolute top-6 left-6 bg-accent px-4 py-2 font-black tracking-widest text-white italic"
                                    >
                                        ACCESS: 03
                                    </div>
                                </div>
                                <h3
                                    class="headline-sm mb-4 font-black tracking-tight text-foreground uppercase italic transition-colors group-hover:text-accent"
                                >
                                    MANAJEMEN
                                </h3>
                                <p
                                    class="label-md max-w-[320px] leading-relaxed font-black tracking-widest text-foreground/50 uppercase"
                                >
                                    PENGELOLAAN DATABASE USER, MONITORING
                                    ADMINISTRASI SISTEM, DAN TATA KELOLA
                                    EKOSISTEM KLUB.
                                </p>
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </section>

            <!-- CTA Section (Compact Padding) -->
            <section class="relative overflow-hidden bg-accent py-24">
                <div
                    class="carbon-texture pointer-events-none absolute inset-0 opacity-[0.15]"
                ></div>
                <v-container class="relative z-10 py-12 text-center">
                    <h2
                        class="display-lg mb-8 leading-none font-black tracking-tighter text-white uppercase italic"
                    >
                        UNLOCK THE <br />
                        ARCHIVE.
                    </h2>
                    <p
                        class="body-md mx-auto mb-16 max-w-xl text-[0.85rem] font-black tracking-widest text-white uppercase italic opacity-90 drop-shadow-md"
                    >
                        Masuk ke dalam ekosistem monitoring paling presisi.
                        Mulai kumpulkan data, menangkan podium.
                    </p>
                    <v-btn
                        v-if="canRegister"
                        :href="register().url"
                        color="white"
                        flat
                        rounded="sm"
                        size="large"
                        class="cta-btn-white h-[72px] px-20 text-[0.9rem] font-black tracking-[0.4em] uppercase shadow-2xl transition-all hover:scale-105 active:scale-95"
                    >
                        DAFTAR SEKARANG
                    </v-btn>
                </v-container>
            </section>
        </v-main>

        <!-- Footer -->
        <v-footer class="bg-surface border-t border-foreground/5 py-20">
            <v-container fluid class="max-w-[1500px] px-6 md:px-12">
                <v-row class="mb-20 items-start gap-y-16">
                    <v-col cols="12" lg="5">
                        <AppLogoIcon class="mb-10 h-10 text-accent" />
                        <p
                            class="label-md max-w-[400px] leading-relaxed font-black tracking-[0.2em] text-foreground/40 uppercase italic"
                        >
                            SIMORA (Sistem Informasi Monitoring Atlet Sepeda) —
                            Memberikan dekonstruksi data telemetri untuk
                            pencapaian performa atlet elite.
                        </p>
                    </v-col>

                    <v-col cols="6" sm="4" lg="2">
                        <h4
                            class="label-sm mb-10 font-black tracking-[0.5em] text-accent uppercase italic"
                        >
                            Sistem
                        </h4>
                        <div
                            class="label-sm flex flex-col gap-6 font-black opacity-40"
                        >
                            <a
                                href="#protocol"
                                class="text-decoration-none text-foreground uppercase transition-all hover:text-accent hover:opacity-100"
                                >PROTOKOL</a
                            >
                            <a
                                href="#metric"
                                class="text-decoration-none text-foreground uppercase transition-all hover:text-accent hover:opacity-100"
                                >METRIK</a
                            >
                            <a
                                href="#identity"
                                class="text-decoration-none text-foreground uppercase transition-all hover:text-accent hover:opacity-100"
                                >IDENTITAS</a
                            >
                        </div>
                    </v-col>

                    <v-col cols="6" sm="4" lg="2">
                        <h4
                            class="label-sm mb-10 font-black tracking-[0.5em] text-accent uppercase italic"
                        >
                            Akses
                        </h4>
                        <div
                            class="label-sm flex flex-col gap-6 font-black opacity-40"
                        >
                            <Link
                                :href="login().url"
                                class="text-decoration-none text-foreground uppercase transition-all hover:text-accent hover:opacity-100"
                                >LOGIN</Link
                            >
                            <Link
                                :href="register().url"
                                class="text-decoration-none text-foreground uppercase transition-all hover:text-accent hover:opacity-100"
                                >REGISTRASI</Link
                            >
                        </div>
                    </v-col>

                    <v-col cols="12" sm="4" lg="3">
                        <h4
                            class="label-sm mb-10 font-black tracking-[0.5em] text-accent uppercase italic"
                        >
                            Regional
                        </h4>
                        <p
                            class="label-sm text-[0.65rem] leading-loose font-black tracking-[0.3em] text-foreground uppercase opacity-30"
                        >
                            KOHAR CYCLING CLUB <br />
                            KONTROL PUSAT TELEMETRI <br />
                            JAKARTA, INDONESIA
                        </p>
                    </v-col>
                </v-row>

                <div
                    class="flex flex-col items-center justify-between gap-8 border-t border-foreground/5 pt-10 opacity-40 md:flex-row"
                >
                    <span
                        class="label-sm text-[0.6rem] font-black tracking-[0.6em] text-foreground uppercase italic"
                    >
                        © 2026 SIMORA - KINETIC ROAD ARCHIVE
                    </span>
                    <div class="flex gap-8">
                        <span
                            class="label-sm text-[0.6rem] font-black tracking-[0.3em] text-foreground uppercase"
                            >Built for Performance</span
                        >
                        <span
                            class="label-sm text-[0.6rem] font-black tracking-[0.3em] text-accent uppercase"
                            >Precision Slipstream v2.0</span
                        >
                    </div>
                </div>
            </v-container>
        </v-footer>
    </v-app>
</template>

<style>
/* Global Smooth Scroll Fix */
html {
    scroll-behavior: smooth !important;
}
</style>

<style scoped>
/* DYNAMIC KINETIC ARCHIVE THEMING */
.kinetic-archive {
    background-color: rgb(var(--v-theme-background)) !important;
    color: rgb(var(--v-theme-on-background)) !important;
    font-family: 'Inter', sans-serif !important;
}

:root:not(.dark) .kinetic-archive {
    --surface: #f4f7f7;
    --surface-bright: #ffffff;
    --surface-container-low: #ebf0f0;
    --foreground: #0f1414;
}

.dark .kinetic-archive {
    --surface: #0b1326;
    --surface-bright: #171c1c;
    --surface-container-low: #131b2e;
    --foreground: #ffffff;
}

.bg-surface {
    background-color: var(--surface) !important;
}
.bg-surface-bright {
    background-color: var(--surface-bright) !important;
}
.bg-surface-container-low {
    background-color: var(--surface-container-low) !important;
}
.text-foreground {
    color: var(--foreground) !important;
}
.text-accent {
    color: var(--accent) !important;
}

/* Typography Scale */
.display-lg {
    font-size: clamp(2.5rem, 9vw, 6rem);
    font-weight: 900;
    letter-spacing: -0.05em;
    text-transform: uppercase;
}

.display-md {
    font-size: clamp(2rem, 7vw, 4rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    text-transform: uppercase;
}

.headline-sm {
    font-size: 1.5rem;
    font-weight: 900;
    letter-spacing: -0.03em;
}

.title-sm {
    font-size: 1.125rem;
    font-weight: 900;
    letter-spacing: 0.25em;
}

.body-md {
    font-size: 1rem;
    font-weight: 800;
}

.label-sm {
    font-size: 0.65rem;
    font-weight: 900;
    letter-spacing: 0.4em;
}

.label-md {
    font-size: 0.82rem;
    font-weight: 800;
}

/* Nav Link Protocol - THICKER & MORE CONTRAST */
.nav-link {
    font-size: 0.75rem;
    font-weight: 900;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    text-decoration: none;
    color: var(--foreground) !important;
    opacity: 0.6;
    transition: all 0.4s cubic-bezier(0.19, 1, 0.22, 1);
}

.nav-link:hover {
    opacity: 1;
    color: var(--accent) !important;
    letter-spacing: 0.5em;
}

/* CTA Buttons */
.cta-btn {
    background: linear-gradient(
        135deg,
        #ffb59c 0%,
        var(--accent) 100%
    ) !important;
    color: #380c00 !important;
    font-weight: 900 !important;
    box-shadow: 0 10px 30px rgba(255, 97, 32, 0.3) !important;
}

.cta-btn-white {
    background: #ffffff !important;
    color: #0b1326 !important;
    font-weight: 900 !important;
}

/* Textures */
.carbon-texture {
    background-image: radial-gradient(
        circle at 1px 1px,
        rgba(var(--v-theme-on-background), 0.15) 1px,
        transparent 1px
    );
    background-size: 28px 28px;
}

/* Animations */
.animate-bounce-subtle {
    animation: bounce-subtle 3s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@keyframes bounce-subtle {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

.perspective-2000 {
    perspective: 2000px;
}

.rotate-y-3 {
    transform: rotateY(3deg);
}

.performance-card {
    transition: all 0.7s cubic-bezier(0.19, 1, 0.22, 1);
}

.performance-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

@media (max-width: 959px) {
    .display-lg {
        font-size: 3.5rem;
    }
    .hero-text-block {
        border-left: 4px solid var(--accent);
        padding-left: 2rem;
    }
}
</style>
