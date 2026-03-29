<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useAppearance } from '@/composables/useAppearance';
import { dashboard, login, register } from '@/routes';

const { resolvedAppearance } = useAppearance();
const currentTheme = computed(() => resolvedAppearance.value === 'dark' ? 'simoraDark' : 'simoraLight');
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
    <v-app :theme="currentTheme" class="kinetic-archive selection:bg-primary/30 scroll-smooth">
        <Head title="SIMORA | Kinetic Road Archive" />

        <!-- Navigation: The Slipstream Header -->
        <nav 
            class="fixed top-0 left-0 right-0 z-[100] transition-all duration-500 border-b"
            :class="isDark ? 'bg-surface/80 backdrop-blur-md border-white/5' : 'bg-white/80 backdrop-blur-md border-black/5'"
        >
            <div class="max-w-[1600px] mx-auto px-6 md:px-12 py-5 flex items-center justify-between">
                <Link href="/" class="flex items-center text-decoration-none group">
                    <AppLogoIcon class="h-10 md:h-12 text-primary transition-transform duration-700 group-hover:scale-110" />
                </Link>

                <div class="hidden lg:flex items-center gap-16">
                    <a href="#protocol" class="nav-link">PROTOKOL</a>
                    <a href="#metric" class="nav-link">METRIK</a>
                    <a href="#identity" class="nav-link">IDENTITAS</a>
                </div>

                <div class="flex items-center gap-4 lg:gap-10">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard().url"
                        class="nav-link text-primary opacity-100 font-black"
                    >
                        DASHBOARD
                    </Link>
                    <template v-else>
                        <Link
                            :href="login().url"
                            class="nav-link px-4 opacity-100 font-black text-foreground hover:text-primary transition-colors text-[0.8rem] tracking-[0.2em]"
                        >
                            MASUK
                        </Link>
                        <v-btn
                            v-if="canRegister"
                            :href="register().url"
                            color="primary"
                            variant="flat"
                            rounded="sm"
                            class="cta-btn text-[0.75rem] font-black px-12 tracking-[0.3em]"
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
            <section class="relative min-h-screen flex items-center overflow-hidden">
                <!-- Background Imagery: Cinematic focus -->
                <div class="absolute inset-0 z-0">
                    <v-img 
                        src="/images/hero-bg.png" 
                        cover 
                        :class="[
                            'h-full w-full scale-105 transition-all duration-1000',
                            isDark ? 'grayscale-[0.2] opacity-100' : 'grayscale-[0.3] opacity-90'
                        ]"
                    >
                        <template #placeholder>
                            <div class="flex items-center justify-center h-full" :class="isDark ? 'bg-slate-900' : 'bg-slate-50'">
                                <v-progress-circular indeterminate color="primary"></v-progress-circular>
                            </div>
                        </template>
                    </v-img>
                    
                    <!-- Mode-Sensitive Overlays (ASKED BY USER) -->
                    <template v-if="isDark">
                        <div class="absolute inset-0 bg-slate-950/50 z-10"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/80 to-transparent z-20"></div>
                    </template>
                    <template v-else>
                        <!-- Light Mode: Clean White with subtle gradient for text contrast at 70% target -->
                        <div class="absolute inset-0 bg-white/30 z-10"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-white via-white/60 to-transparent z-20"></div>
                    </template>
                    
                    <div class="absolute inset-0 carbon-texture opacity-[0.05] z-30"></div>
                </div>
                
                <v-container fluid class="max-w-[1600px] px-6 md:px-12 relative z-40 pt-16 lg:pt-0">
                    <v-row class="items-center min-h-[70vh]">
                        <v-col cols="12" lg="6" class="hero-text-block mb-12 lg:mb-0">
                            <div class="inline-flex items-center gap-4 mb-10 overflow-hidden">
                                <span class="h-px w-16 bg-primary"></span>
                                <span class="label-sm text-primary font-black tracking-[0.6em] uppercase">Telemetri Performans</span>
                            </div>
                            
                            <h1 class="display-lg mb-10 leading-[0.85] text-foreground transition-colors duration-500">
                                <span class="block">ARSIP</span>
                                <span class="block text-primary italic">KINETIK</span>
                                <span class="block">PRESISI.</span>
                            </h1>
                            
                            <p class="body-md text-foreground/70 mb-14 max-w-[480px] leading-relaxed font-black uppercase tracking-[0.15em] text-[0.75rem] transition-colors duration-500">
                                Dekonstruksi performa atlet balap sepeda melalui monitoring data biometrik harian yang mutakhir.
                            </p>
                            
                            <div class="flex flex-wrap gap-8">
                                <v-btn
                                    v-if="!$page.props.auth.user && canRegister"
                                    :href="register().url"
                                    color="primary"
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
                            <div class="hud-container relative group perspective-2000">
                                <!-- Main Speed Widget -->
                                <div 
                                    class="p-8 md:p-14 relative z-20 shadow-2xl lg:translate-x-12 rounded-sm overflow-hidden transition-all duration-700 hover:rotate-y-3 border"
                                    :class="isDark ? 'bg-white/5 backdrop-blur-2xl border-white/10' : 'bg-white/90 backdrop-blur-xl border-black/5'"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent pointer-events-none"></div>
                                    
                                    <div class="flex justify-between items-start mb-16 relative z-10">
                                        <div>
                                            <div class="label-sm text-primary mb-2 font-black uppercase tracking-widest italic">Live Session</div>
                                            <div class="title-sm font-black text-foreground italic tracking-[0.3em] uppercase">KOHAR CYCLING CLUB</div>
                                        </div>
                                        <div class="status-dot">
                                            <div class="w-3 h-3 rounded-full bg-primary shadow-[0_0_20px_#FF6120] animate-pulse"></div>
                                        </div>
                                    </div>

                                    <div class="flex items-baseline gap-10 mb-16 relative z-10">
                                        <div class="display-md text-foreground leading-none italic flex items-baseline gap-4">
                                            45.8 <span class="headline-sm opacity-40 not-italic uppercase tracking-normal">KM/H</span>
                                        </div>
                                        <v-icon icon="mdi-trending-up" color="primary" size="32" class="animate-bounce-subtle"></v-icon>
                                    </div>

                                    <div 
                                        class="grid grid-cols-2 lg:grid-cols-3 gap-12 relative z-10 border-t pt-12"
                                        :class="isDark ? 'border-white/10' : 'border-black/5'"
                                    >
                                        <div class="metric-segment">
                                            <div class="label-sm text-foreground/40 mb-3 uppercase tracking-widest font-black">Heart Rate</div>
                                            <div class="headline-sm font-black text-foreground italic">174 <span class="label-sm opacity-40 not-italic">BPM</span></div>
                                        </div>
                                        <div class="metric-segment">
                                            <div class="label-sm text-foreground/40 mb-3 uppercase tracking-widest font-black">Cadence</div>
                                            <div class="headline-sm font-black text-foreground italic">96 <span class="label-sm opacity-40 not-italic">RPM</span></div>
                                        </div>
                                        <div class="metric-segment hidden md:block">
                                            <div class="label-sm text-foreground/40 mb-3 uppercase tracking-widest font-black">Power</div>
                                            <div class="headline-sm font-black text-primary italic">Z4</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </section>

            <!-- Protocol Section (Compact Padding) -->
            <section id="protocol" class="py-20 bg-surface-container-low relative">
                <v-container class="max-w-[1400px]">
                    <v-row class="mb-16">
                        <v-col cols="12" lg="7">
                            <div class="label-sm text-primary font-black tracking-[0.6em] mb-4 uppercase">The Precision Protocol</div>
                            <h2 class="display-md text-foreground tracking-tighter uppercase leading-[0.9] mb-4">
                                DOMINANSI DATA <br /> <span class="text-primary italic">TANPA KOMPROMI.</span>
                            </h2>
                        </v-col>
                        <v-col cols="12" lg="5">
                            <p class="body-md text-foreground/50 leading-relaxed uppercase font-black tracking-widest border-l-2 border-primary/20 pl-8 text-[0.75rem] lg:mt-16">
                                Sistem pemantauan yang mendobrak batasan fisik setiap atlet melalui analisis telemetri tingkat tinggi.
                            </p>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col cols="12" md="4" v-for="(item, i) in [
                            { title: 'TELEMETRI REAL-TIME', icon: 'mdi-chart-line-variant', text: 'Pencatatan statistik latihan harian meliputi jarak, kecepatan, dan durasi dengan presisi.' },
                            { title: 'ANALISIS BIOMETRIK', icon: 'mdi-heart-pulse', text: 'Visualisasi zona intensitas detak jantung untuk menjaga performa pada level puncak.' },
                            { title: 'REKOMENDASI ELITE', icon: 'mdi-target-variant', text: 'Umpan balik personal dari pelatih berdasarkan akumulasi data performa historis.' }
                        ]" :key="i">
                            <v-card flat class="performance-card group h-full cursor-default border-none bg-transparent">
                                <div class="p-12 bg-surface rounded-sm h-full flex flex-col justify-between transition-all duration-700 hover:bg-surface-bright border-b-2 border-transparent hover:border-primary shadow-sm hover:shadow-xl">
                                    <div>
                                        <v-icon :icon="item.icon" color="primary" class="mb-12 group-hover:scale-110 transition-transform duration-700" size="44"></v-icon>
                                        <h3 class="title-sm font-black text-foreground mb-6 uppercase italic tracking-[0.3em]">{{ item.title }}</h3>
                                        <p class="label-md text-foreground/60 leading-relaxed font-medium">{{ item.text }}</p>
                                    </div>
                                </div>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-container>
            </section>

            <!-- Identity Section (Compact Padding) -->
            <section id="identity" class="py-20 bg-surface relative overflow-hidden">
                <v-container class="max-w-[1400px] relative z-10">
                    <div class="text-center mb-16">
                        <h2 class="display-md italic tracking-tighter uppercase text-foreground leading-none mb-4">MODUL IDENTITAS.</h2>
                        <span class="label-sm tracking-[1em] text-primary font-black uppercase">Role Based Access</span>
                    </div>

                    <v-row class="items-stretch gap-y-12">
                        <v-col cols="12" lg="4" class="px-6">
                            <div class="role-module group">
                                <div class="relative mb-10 overflow-hidden aspect-[4/5] bg-surface-container-low rounded-sm shadow-2xl">
                                    <v-img src="/images/role-athlete.png" cover class="grayscale transition-all duration-1000 group-hover:grayscale-0 group-hover:scale-105" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-surface via-transparent to-transparent opacity-90"></div>
                                    <div class="absolute top-6 left-6 label-sm bg-primary text-white px-4 py-2 font-black italic tracking-widest">ACCESS: 01</div>
                                </div>
                                <h3 class="headline-sm italic font-black text-foreground mb-4 uppercase tracking-tight group-hover:text-primary transition-colors">ATLET</h3>
                                <p class="label-md text-foreground/50 leading-relaxed max-w-[320px] uppercase font-black tracking-widest">
                                    INPUT DATA LATIHAN, MONITORING GRAFIK PERFORMA, DAN EVALUASI PROGRES MANDIRI.
                                </p>
                            </div>
                        </v-col>

                        <v-col cols="12" lg="4" class="px-6">
                            <div class="role-module group">
                                <div class="relative mb-10 overflow-hidden aspect-[4/5] bg-surface-container-low rounded-sm shadow-2xl">
                                    <v-img src="/images/role-coach.png" cover class="grayscale transition-all duration-1000 group-hover:grayscale-0 group-hover:scale-105" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-surface via-transparent to-transparent opacity-90"></div>
                                    <div class="absolute top-6 left-6 label-sm bg-primary text-white px-4 py-2 font-black italic tracking-widest">ACCESS: 02</div>
                                </div>
                                <h3 class="headline-sm italic font-black text-foreground mb-4 uppercase tracking-tight group-hover:text-primary transition-colors">PELATIH</h3>
                                <p class="label-md text-foreground/50 leading-relaxed max-w-[320px] uppercase font-black tracking-widest">
                                    ANALISIS PERFORMA TIM, MONITORING ATLET SECARA AGREGAT, DAN PEMBERIAN REKOMENDASI LATIHAN.
                                </p>
                            </div>
                        </v-col>

                        <v-col cols="12" lg="4" class="px-6">
                            <div class="role-module group">
                                <div class="relative mb-10 overflow-hidden aspect-[4/5] bg-surface-container-low rounded-sm shadow-2xl">
                                    <v-img src="/images/role-manager.png" cover class="grayscale transition-all duration-1000 group-hover:grayscale-0 group-hover:scale-105" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-surface via-transparent to-transparent opacity-90"></div>
                                    <div class="absolute top-6 left-6 label-sm bg-primary text-white px-4 py-2 font-black italic tracking-widest">ACCESS: 03</div>
                                </div>
                                <h3 class="headline-sm italic font-black text-foreground mb-4 uppercase tracking-tight group-hover:text-primary transition-colors">MANAJEMEN</h3>
                                <p class="label-md text-foreground/50 leading-relaxed max-w-[320px] uppercase font-black tracking-widest">
                                    PENGELOLAAN DATABASE USER, MONITORING ADMINISTRASI SISTEM, DAN TATA KELOLA EKOSISTEM KLUB.
                                </p>
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </section>

            <!-- CTA Section (Compact Padding) -->
            <section class="py-20 bg-primary relative overflow-hidden">
                <div class="absolute inset-0 carbon-texture opacity-[0.1] pointer-events-none"></div>
                <v-container class="text-center relative z-10 py-10">
                    <h2 class="display-lg italic font-black text-white tracking-tighter mb-10 leading-none uppercase">
                        UNLOCK THE <br /> ARCHIVE.
                    </h2>
                    <p class="body-md text-white font-black opacity-80 mb-14 max-w-xl mx-auto uppercase tracking-widest italic text-[0.8rem] drop-shadow-sm">
                        Masuk ke dalam ekosistem monitoring paling presisi. Mulai kumpulkan data, menangkan podium.
                    </p>
                    <v-btn
                        v-if="canRegister"
                        :href="register().url"
                        color="white"
                        flat
                        rounded="sm"
                        size="large"
                        class="cta-btn-white px-20 font-black tracking-[0.4em] h-[72px] shadow-2xl transition-all hover:scale-105 active:scale-95 uppercase text-[0.8rem]"
                    >
                        DAFTAR SEKARANG
                    </v-btn>
                </v-container>
            </section>
        </v-main>

        <!-- Footer -->
        <v-footer class="bg-surface py-20 border-t border-foreground/5">
            <v-container fluid class="max-w-[1500px] px-6 md:px-12">
                <v-row class="gap-y-16 mb-20 items-start">
                    <v-col cols="12" lg="5">
                        <AppLogoIcon class="h-10 text-primary mb-10" />
                        <p class="label-md text-foreground/40 leading-relaxed max-w-[400px] font-black uppercase tracking-[0.2em] italic">
                            SIMORA (Sistem Informasi Monitoring Atlet Sepeda) — Memberikan dekonstruksi data telemetri untuk pencapaian performa atlet elite.
                        </p>
                    </v-col>
                    
                    <v-col cols="6" sm="4" lg="2">
                        <h4 class="label-sm font-black tracking-[0.5em] text-primary mb-10 uppercase italic">Sistem</h4>
                        <div class="flex flex-col gap-6 label-sm font-black opacity-40">
                            <a href="#protocol" class="hover:opacity-100 hover:text-primary transition-all text-decoration-none text-foreground uppercase">PROTOKOL</a>
                            <a href="#metric" class="hover:opacity-100 hover:text-primary transition-all text-decoration-none text-foreground uppercase">METRIK</a>
                            <a href="#identity" class="hover:opacity-100 hover:text-primary transition-all text-decoration-none text-foreground uppercase">IDENTITAS</a>
                        </div>
                    </v-col>

                    <v-col cols="6" sm="4" lg="2">
                        <h4 class="label-sm font-black tracking-[0.5em] text-primary mb-10 uppercase italic">Akses</h4>
                        <div class="flex flex-col gap-6 label-sm font-black opacity-40">
                            <Link :href="login().url" class="hover:opacity-100 hover:text-primary transition-all text-decoration-none text-foreground uppercase">LOGIN</Link>
                            <Link :href="register().url" class="hover:opacity-100 hover:text-primary transition-all text-decoration-none text-foreground uppercase">REGISTRASI</Link>
                        </div>
                    </v-col>

                    <v-col cols="12" sm="4" lg="3">
                        <h4 class="label-sm font-black tracking-[0.5em] text-primary mb-10 uppercase italic">Regional</h4>
                        <p class="label-sm font-black opacity-30 uppercase tracking-[0.3em] leading-loose text-foreground text-[0.65rem]">
                            KOHAR CYCLING CLUB <br />
                            KONTROL PUSAT TELEMETRI <br />
                            JAKARTA, INDONESIA
                        </p>
                    </v-col>
                </v-row>
                
                <div class="pt-10 border-t border-foreground/5 flex flex-col md:flex-row justify-between items-center gap-8 opacity-40">
                    <span class="label-sm font-black tracking-[0.6em] uppercase italic text-foreground text-[0.6rem]">
                        © 2026 SIMORA - KINETIC ROAD ARCHIVE
                    </span>
                    <div class="flex gap-8">
                        <span class="label-sm font-black tracking-[0.3em] uppercase text-foreground text-[0.6rem]">Built for Performance</span>
                        <span class="label-sm font-black tracking-[0.3em] uppercase text-primary text-[0.6rem]">Precision Slipstream v2.0</span>
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
    --surface: #F4F7F7;
    --surface-bright: #FFFFFF;
    --surface-container-low: #EBF0F0;
    --primary: #FF6120;
    --foreground: #0F1414;
}

.dark .kinetic-archive {
    --surface: #0B1326;
    --surface-bright: #171c1c;
    --surface-container-low: #131B2E;
    --primary: #FF6120;
    --foreground: #FFFFFF;
}

.bg-surface { background-color: var(--surface) !important; }
.bg-surface-bright { background-color: var(--surface-bright) !important; }
.bg-surface-container-low { background-color: var(--surface-container-low) !important; }
.text-foreground { color: var(--foreground) !important; }
.text-primary { color: var(--primary) !important; }

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
    color: var(--primary) !important;
    letter-spacing: 0.5em;
}

/* CTA Buttons */
.cta-btn {
    background: linear-gradient(135deg, #FFB59C 0%, var(--primary) 100%) !important;
    color: #380C00 !important;
    font-weight: 900 !important;
    box-shadow: 0 10px 30px rgba(255, 97, 32, 0.3) !important;
}

.cta-btn-white {
    background: #FFFFFF !important;
    color: #0B1326 !important;
    font-weight: 900 !important;
}

/* Textures */
.carbon-texture {
    background-image: 
        radial-gradient(circle at 1px 1px, rgba(var(--v-theme-on-background), 0.15) 1px, transparent 1px);
    background-size: 28px 28px;
}

/* Animations */
.animate-bounce-subtle {
    animation: bounce-subtle 3s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@keyframes bounce-subtle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
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
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

@media (max-width: 959px) {
    .display-lg { font-size: 3.5rem; }
    .hero-text-block { border-left: 4px solid var(--primary); padding-left: 2rem; }
}
</style>
