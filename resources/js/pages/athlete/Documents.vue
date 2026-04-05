<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    FileText,
    UploadCloud,
    CheckCircle2,
    AlertCircle,
    UserCircle,
    FileImage,
    CreditCard,
    Shield,
    Calendar,
} from 'lucide-vue-next';
import { ref } from 'vue';

import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface Profile {
    id: number;
    profile_photo_path?: string;
    birth_certificate_path?: string;
    family_card_path?: string;
    id_card_path?: string;
    license_path?: string;
    uci_id?: string;
    license_valid_until?: string;
}

defineProps<{
    profile: Profile | null;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/athlete/dashboard' },
    { title: 'Dokumen Pribadi', href: '/athlete/documents' },
];

const form = useForm({
    profile_photo: null as File | null,
    birth_certificate: null as File | null,
    family_card: null as File | null,
    id_card: null as File | null,
});

const handleFileUpload = (e: Event, field: keyof typeof form) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form[field] = target.files[0] as any;
    }
};

const submit = () => {
    // We send via POST with multipart/form-data rules in Inertia
    form.post('/athlete/documents', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const formatDate = (date: string) => {
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const isLicenseValid = (validUntil?: string) => {
    if (!validUntil) return false;
    return new Date(validUntil) >= new Date();
};

const previewUrl = ref<string | null>(null);
const showPreview = (url: string) => {
    previewUrl.value = url;
};
const closePreview = () => {
    previewUrl.value = null;
};
</script>

<template>
    <Head title="Dokumen Pribadi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex min-h-screen w-full max-w-5xl flex-col gap-10 bg-background p-6 text-foreground md:p-10">
            <!-- Header -->
            <div class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-orange-500/10 p-3 text-orange-500">
                        <FileText class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tighter text-foreground uppercase md:text-4xl">
                            Dokumen Pribadi
                        </h1>
                        <p class="mt-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">
                            Kelola identitas dan lisensi balap
                        </p>
                    </div>
                </div>
            </div>

            <!-- License Card (Read Only) -->
            <div class="relative overflow-hidden rounded-[2.5rem] border border-accent/20 bg-accent/5 p-8 shadow-2xl">
                <div class="absolute -right-10 -top-10 text-accent/5">
                    <Shield class="h-64 w-64" />
                </div>
                <div class="relative z-10 flex flex-col gap-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-2xl bg-accent p-4 text-white shadow-xl shadow-accent/30">
                            <Shield class="h-8 w-8" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black tracking-widest text-accent uppercase">
                                Lisensi Balap Sepeda
                            </h2>
                            <p class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-80">
                                Diterbitkan oleh Manajemen
                            </p>
                        </div>
                    </div>

                    <div v-if="profile?.license_path && profile?.uci_id" class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                            <p class="mb-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60">
                                UCI ID
                            </p>
                            <p class="text-xl font-black tracking-tighter text-foreground font-mono">
                                {{ profile.uci_id }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                            <p class="mb-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60">
                                Berlaku Hingga
                            </p>
                            <div class="flex items-center gap-2">
                                <Calendar class="h-4 w-4 text-accent" />
                                <p class="text-lg font-black tracking-tighter text-foreground uppercase">
                                    {{ formatDate(profile.license_valid_until!) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col items-start justify-center gap-2">
                            <span v-if="isLicenseValid(profile.license_valid_until)" class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-[10px] font-black tracking-widest text-emerald-500 uppercase">
                                <CheckCircle2 class="h-3 w-3" /> Lisensi Aktif
                            </span>
                            <span v-else class="inline-flex items-center gap-2 rounded-full border border-destructive/30 bg-destructive/10 px-4 py-2 text-[10px] font-black tracking-widest text-destructive uppercase">
                                <AlertCircle class="h-3 w-3" /> Kadaluarsa
                            </span>
                            <button type="button" @click="showPreview(`/documents/${$page.props.auth.user.id}/license`)" class="mt-2 text-[10px] font-black tracking-widest text-accent uppercase hover:underline text-left">
                                Lihat Dokumen Lisensi →
                            </button>
                        </div>
                    </div>
                    <div v-else class="flex items-center justify-center rounded-2xl border border-dashed border-accent/20 bg-background/50 p-10">
                        <p class="text-sm font-bold text-muted-foreground italic">
                            Belum ada lisensi yang ditautkan ke akun Anda. Harap hubungi pelatih atau manajemen.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Upload Forms -->
            <form @submit.prevent="submit" class="flex flex-col gap-8 rounded-[2.5rem] border border-border bg-card p-8 shadow-xl">
                <div>
                    <h3 class="text-xl font-black tracking-tight text-foreground uppercase">
                        Dokumen Identitas Diri
                    </h3>
                    <p class="mt-1 text-sm font-medium text-muted-foreground">
                        Unggah salinan dokumen resmi Anda untuk keperluan verifikasi pendaftaran kompetisi. (Maks 5MB per file)
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <!-- Photo Profile -->
                    <div class="flex flex-col gap-3">
                        <Label class="flex items-center gap-2 text-xs font-black uppercase text-foreground">
                            <UserCircle class="h-4 w-4 text-orange-500" /> Pas Foto Pribadi
                        </Label>
                        <div class="flex flex-col gap-2 rounded-2xl border border-dashed border-border bg-muted/20 p-6 transition-all hover:bg-muted/40">
                            <input type="file" @change="(e) => handleFileUpload(e, 'profile_photo')" accept="image/*" class="text-xs font-medium file:mr-4 file:rounded-full file:border-none file:bg-orange-500/10 file:px-4 file:py-2 file:text-[10px] file:font-black file:uppercase file:text-orange-500 hover:file:bg-orange-500/20" />
                            <div v-if="profile?.profile_photo_path" class="mt-2 flex items-center justify-between rounded-xl bg-card p-3 shadow-sm border border-border">
                                <div class="flex items-center gap-2">
                                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                                    <span class="text-[10px] font-black uppercase text-muted-foreground">Tersimpan</span>
                                </div>
                                <button type="button" @click="showPreview(`/documents/${$page.props.auth.user.id}/profile_photo`)" class="text-[10px] font-black uppercase text-accent hover:underline">Lihat</button>
                            </div>
                        </div>
                        <p v-if="form.errors.profile_photo" class="text-[10px] font-black text-destructive uppercase">{{ form.errors.profile_photo }}</p>
                    </div>

                    <!-- KTP -->
                    <div class="flex flex-col gap-3">
                        <Label class="flex items-center gap-2 text-xs font-black uppercase text-foreground">
                            <CreditCard class="h-4 w-4 text-blue-500" /> Kartu Tanda Penduduk (KTP)
                        </Label>
                        <div class="flex flex-col gap-2 rounded-2xl border border-dashed border-border bg-muted/20 p-6 transition-all hover:bg-muted/40">
                            <input type="file" @change="(e) => handleFileUpload(e, 'id_card')" accept=".pdf,.jpg,.jpeg,.png" class="text-xs font-medium file:mr-4 file:rounded-full file:border-none file:bg-blue-500/10 file:px-4 file:py-2 file:text-[10px] file:font-black file:uppercase file:text-blue-500 hover:file:bg-blue-500/20" />
                            <div v-if="profile?.id_card_path" class="mt-2 flex items-center justify-between rounded-xl bg-card p-3 shadow-sm border border-border">
                                <div class="flex items-center gap-2">
                                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                                    <span class="text-[10px] font-black uppercase text-muted-foreground">Tersimpan</span>
                                </div>
                                <button type="button" @click="showPreview(`/documents/${$page.props.auth.user.id}/id_card`)" class="text-[10px] font-black uppercase text-accent hover:underline">Lihat</button>
                            </div>
                        </div>
                        <p v-if="form.errors.id_card" class="text-[10px] font-black text-destructive uppercase">{{ form.errors.id_card }}</p>
                    </div>

                    <!-- KK -->
                    <div class="flex flex-col gap-3">
                        <Label class="flex items-center gap-2 text-xs font-black uppercase text-foreground">
                            <Users class="h-4 w-4 text-emerald-500" /> Kartu Keluarga (KK)
                        </Label>
                        <div class="flex flex-col gap-2 rounded-2xl border border-dashed border-border bg-muted/20 p-6 transition-all hover:bg-muted/40">
                            <input type="file" @change="(e) => handleFileUpload(e, 'family_card')" accept=".pdf,.jpg,.jpeg,.png" class="text-xs font-medium file:mr-4 file:rounded-full file:border-none file:bg-emerald-500/10 file:px-4 file:py-2 file:text-[10px] file:font-black file:uppercase file:text-emerald-500 hover:file:bg-emerald-500/20" />
                            <div v-if="profile?.family_card_path" class="mt-2 flex items-center justify-between rounded-xl bg-card p-3 shadow-sm border border-border">
                                <div class="flex items-center gap-2">
                                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                                    <span class="text-[10px] font-black uppercase text-muted-foreground">Tersimpan</span>
                                </div>
                                <button type="button" @click="showPreview(`/documents/${$page.props.auth.user.id}/family_card`)" class="text-[10px] font-black uppercase text-accent hover:underline">Lihat</button>
                            </div>
                        </div>
                        <p v-if="form.errors.family_card" class="text-[10px] font-black text-destructive uppercase">{{ form.errors.family_card }}</p>
                    </div>

                    <!-- Akte Kelahiran -->
                    <div class="flex flex-col gap-3">
                        <Label class="flex items-center gap-2 text-xs font-black uppercase text-foreground">
                            <FileImage class="h-4 w-4 text-purple-500" /> Akte Kelahiran
                        </Label>
                        <div class="flex flex-col gap-2 rounded-2xl border border-dashed border-border bg-muted/20 p-6 transition-all hover:bg-muted/40">
                            <input type="file" @change="(e) => handleFileUpload(e, 'birth_certificate')" accept=".pdf,.jpg,.jpeg,.png" class="text-xs font-medium file:mr-4 file:rounded-full file:border-none file:bg-purple-500/10 file:px-4 file:py-2 file:text-[10px] file:font-black file:uppercase file:text-purple-500 hover:file:bg-purple-500/20" />
                            <div v-if="profile?.birth_certificate_path" class="mt-2 flex items-center justify-between rounded-xl bg-card p-3 shadow-sm border border-border">
                                <div class="flex items-center gap-2">
                                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                                    <span class="text-[10px] font-black uppercase text-muted-foreground">Tersimpan</span>
                                </div>
                                <button type="button" @click="showPreview(`/documents/${$page.props.auth.user.id}/birth_certificate`)" class="text-[10px] font-black uppercase text-accent hover:underline">Lihat</button>
                            </div>
                        </div>
                        <p v-if="form.errors.birth_certificate" class="text-[10px] font-black text-destructive uppercase">{{ form.errors.birth_certificate }}</p>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-[2rem] bg-accent px-8 py-4 text-[10px] font-black tracking-widest text-white uppercase shadow-lg shadow-accent/30 transition-all hover:scale-105 active:scale-95 disabled:opacity-50">
                        <UploadCloud class="h-4 w-4" />
                        {{ form.processing ? 'Mengunggah...' : 'Simpan Dokumen' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Document Preview Modal -->
        <div v-if="previewUrl" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-8 backdrop-blur-md bg-background/80" @click="closePreview">
            <div class="relative w-full max-w-6xl h-[90vh] bg-card rounded-[2rem] border border-border overflow-hidden shadow-2xl flex flex-col" @click.stop>
                <div class="flex justify-between items-center p-4 md:p-6 border-b border-border bg-muted/20 shrink-0">
                    <h3 class="text-sm font-black tracking-widest uppercase truncate text-muted-foreground">Pratinjau Dokumen</h3>
                    <button @click="closePreview" class="text-muted-foreground hover:bg-destructive/10 hover:text-destructive rounded-full px-4 py-2 text-xs font-black transition-all uppercase">
                        Tutup (X)
                    </button>
                </div>
                <div class="relative flex-1 bg-muted/30 p-2 md:p-6 overflow-hidden">
                    <!-- Using iframe as universal previewer for both image and PDF from Laravel generic file stream -->
                    <iframe :src="previewUrl" class="w-full h-full border border-border shadow-inner rounded-xl bg-white" title="Document Preview"></iframe>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
