<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { dashboard, login, register } from '@/routes';

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
    <Head title="Welcome to SIMORA" />
    <div
        class="flex min-h-screen flex-col bg-background font-sans text-foreground"
    >
        <!-- Navbar -->
        <header
            class="sticky top-0 z-50 w-full border-b border-border bg-card/80 backdrop-blur-md"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <AppLogoIcon class="h-12 w-auto" />
                    </div>
                    <nav class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="text-sm font-medium text-foreground transition-colors hover:text-accent"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="text-sm font-medium text-foreground transition-colors hover:text-primary"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                            >
                                Register
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-grow">
            <section class="relative overflow-hidden bg-primary dark:bg-background py-24 sm:py-32 transition-colors duration-300">
                <div class="absolute inset-0 z-0">
                    <img
                        src="/images/cycling-bg.png"
                        alt="Hero Background"
                        class="h-full w-full object-cover opacity-70 mix-blend-multiply"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-primary/20 dark:from-background/80 to-transparent"
                    ></div>
                </div>

                <div
                    class="relative z-10 mx-auto max-w-7xl px-4 text-center text-white sm:px-6 lg:px-8"
                >
                    <h1
                        class="mb-6 text-4xl leading-tight font-extrabold tracking-tight drop-shadow-md sm:text-6xl"
                    >
                        Sistem Informasi Monitoring <br /><span
                            class="text-accent"
                            >Atlet Sepeda</span
                        >
                    </h1>
                    <p
                        class="mx-auto mt-4 mb-10 max-w-2xl text-lg text-gray-200 drop-shadow sm:text-xl"
                    >
                        Pantau, analisis, dan tingkatkan performa latihan secara
                        komprehensif bersama Kohar Cycling Club. Raih limit
                        terbaikmu!
                    </p>
                    <div class="flex justify-center gap-4">
                        <Link
                            v-if="!$page.props.auth.user && canRegister"
                            :href="register()"
                            class="transform rounded-md bg-accent px-8 py-3 text-base font-medium text-white shadow-lg transition-transform hover:-translate-y-1 hover:bg-accent/90 focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:outline-none"
                        >
                            Mulai Sekarang
                        </Link>
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="login()"
                            class="rounded-md border border-white/20 bg-white/10 px-8 py-3 text-base font-medium text-white backdrop-blur-sm transition-colors hover:bg-white/20"
                        >
                            Sign In
                        </Link>
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="rounded-md bg-accent px-8 py-3 text-base font-medium text-white shadow-lg transition-colors hover:bg-accent/90"
                        >
                            Buka Dashboard
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="bg-muted/30 py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mb-16 text-center">
                        <h2 class="text-3xl font-bold text-primary sm:text-4xl">
                            Fitur Utama SIMORA
                        </h2>
                        <p class="mt-4 text-lg text-muted-foreground">
                            Sistem terintegrasi untuk Atlet, Pelatih, dan
                            Manajemen klub.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <!-- Feature 1 -->
                        <div
                            class="rounded-xl border border-border bg-card p-8 shadow-sm transition-shadow hover:shadow-md"
                        >
                            <div
                                class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg bg-accent/10"
                            >
                                <svg
                                    class="h-6 w-6 text-accent"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    ></path>
                                </svg>
                            </div>
                            <h3 class="mb-3 text-xl font-semibold">
                                Pencatatan Latihan
                            </h3>
                            <p class="text-muted-foreground">
                                Atlet dapat merekam statistik harian termasuk
                                jarak, durasi, kecepatan rata-rata, denjut
                                jantung, hingga kalori yang terbakar.
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div
                            class="rounded-xl border border-border bg-card p-8 shadow-sm transition-shadow hover:shadow-md"
                        >
                            <div
                                class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10"
                            >
                                <svg
                                    class="h-6 w-6 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                            <h3 class="mb-3 text-xl font-semibold">
                                Analisis Performa
                            </h3>
                            <p class="text-muted-foreground">
                                Pelatih dapat memantau dan menganalisis
                                perkembangan fisik atlet melalui grafik
                                komprehensif berdasarkan riwayat latihan.
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div
                            class="rounded-xl border border-border bg-card p-8 shadow-sm transition-shadow hover:shadow-md"
                        >
                            <div
                                class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg bg-accent/10"
                            >
                                <svg
                                    class="h-6 w-6 text-accent"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
                                    ></path>
                                </svg>
                            </div>
                            <h3 class="mb-3 text-xl font-semibold">
                                Rekomendasi Pintar
                            </h3>
                            <p class="text-muted-foreground">
                                Memberikan umpan balik (feedback) dan instruksi
                                penyesuaian intensitas latihan yang
                                terpersonalisasi untuk setiap atlet.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="mt-auto border-t border-border bg-card py-10">
            <div
                class="mx-auto flex max-w-7xl flex-col items-center px-4 sm:px-6 lg:px-8"
            >
                <AppLogoIcon class="mb-4 h-8 w-auto opacity-50 grayscale" />
                <p class="text-sm text-muted-foreground">
                    &copy; {{ new Date().getFullYear() }} Kuukok. Hak Cipta
                    Dilindungi.
                </p>
                <div
                    class="mt-4 flex gap-4 text-sm font-medium text-muted-foreground"
                >
                    <a href="#" class="hover:text-primary">Tentang</a>
                    <a href="#" class="hover:text-primary">Tim</a>
                    <a href="#" class="hover:text-primary">Kontak</a>
                </div>
            </div>
        </footer>
    </div>
</template>
