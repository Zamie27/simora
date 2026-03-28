<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
</script>

<template>
    <Head title="Verifikasi Tertunda" />

    <div class="flex min-h-screen items-center justify-center bg-background p-6">
        <div class="w-full max-w-md text-center">
            <div class="mb-8 flex justify-center">
                <AppLogo class="h-12 w-auto fill-accent" />
            </div>
            
            <div class="rounded-2xl border border-border bg-card p-8 shadow-2xl">
                <div class="mb-6 flex justify-center">
                    <div class="rounded-full bg-accent/10 p-4">
                        <svg class="h-12 w-12 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                
                <h1 class="mb-4 text-2xl font-black uppercase tracking-tight text-foreground">Akun Menunggu Verifikasi</h1>
                <p class="mb-8 text-muted-foreground">
                    Halo atlet! Akun Anda telah berhasil dibuat. Saat ini tim <strong>Manajemen SIMORA</strong> sedang memverifikasi data Anda dan menghubungkan Anda dengan pelatih yang sesuai.
                </p>
                
                <div class="space-y-4">
                    <p class="text-sm font-semibold text-accent italic">Silakan cek kembali beberapa saat lagi.</p>
                    
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="w-full rounded-xl bg-muted py-3 text-xs font-black uppercase tracking-widest text-muted-foreground transition-all hover:bg-muted/80 hover:text-foreground"
                    >
                        Keluar & Cek Nanti
                    </Link>
                </div>
            </div>
            
            <p class="mt-8 text-xs text-muted-foreground">
                &copy; {{ new Date().getFullYear() }} SIMORA Monitoring Sistem.
            </p>
        </div>
    </div>
</template>
