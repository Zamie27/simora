<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    Calendar,
    CheckCircle2,
    CreditCard,
    FileImage,
    FileText,
    Mail,
    Save,
    Shield,
    UploadCloud,
    UserCircle,
    Users,
    X,
} from 'lucide-vue-next';

import { ref } from 'vue';
import { Input } from '@/components/ui/input';
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

interface User {
    id: number;
    name: string;
    email: string;
    coach?: { name: string };
    created_at: string;
    athlete_profile?: Profile;
}

interface Coach {
    id: number;
    name: string;
}

const props = defineProps<{
    athlete: User;
    coaches: Coach[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Daftar Atlet Terverifikasi', href: '/management/athletes' },
    {
        title: `Detail: ${props.athlete.name}`,
        href: `/management/athletes/${props.athlete.id}`,
    },
];

const coachForm = useForm({
    coach_id: props.athlete.coach_id || null,
});

const form = useForm({
    uci_id: props.athlete.athlete_profile?.uci_id || '',
    license_valid_until: props.athlete.athlete_profile?.license_valid_until
        ? props.athlete.athlete_profile.license_valid_until.split('T')[0]
        : '',
    license_file: null as File | null,
});

const isEditingCoach = ref(false);

const toggleEditCoach = () => {
    isEditingCoach.value = !isEditingCoach.value;
};

const updateCoach = () => {
    coachForm.patch(`/management/users/${props.athlete.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditingCoach.value = false;
        },
    });
};

const handleFileUpload = (e: Event) => {
    const target = e.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        form.license_file = target.files[0];
    }
};

const submitLicense = () => {
    form.post(`/management/athletes/${props.athlete.id}/license`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            // Reset file input only
            form.license_file = null;
            const fileInput = document.getElementById(
                'license_file',
            ) as HTMLInputElement;

            if (fileInput) {
                fileInput.value = '';
            }
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const isLicenseValid = (validUntil?: string) => {
    if (!validUntil) {
        return false;
    }

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
    <Head :title="`Detail Atlet - ${athlete.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex min-h-screen w-full max-w-6xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <!-- Header -->
            <div
                class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row"
            >
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-accent/10 p-3 text-accent">
                        <UserCircle class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-black tracking-tighter text-foreground uppercase md:text-4xl"
                        >
                            Profile Atlet
                        </h1>
                        <p
                            class="mt-1 flex items-center gap-2 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                        >
                            <span
                                class="rounded bg-accent/10 px-2 py-0.5 text-accent"
                                >{{ athlete.name }}</span
                            >
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <!-- Left Column: Personal Data & Coach -->
                <div class="flex flex-col gap-8 md:col-span-1">
                    <div
                        class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-xl"
                    >
                        <div
                            class="flex flex-col items-center bg-muted/20 p-8 pb-6"
                        >
                            <div
                                @click="
                                    athlete.athlete_profile?.profile_photo_path
                                        ? showPreview(
                                              `/documents/${athlete.id}/profile_photo`,
                                          )
                                        : null
                                "
                                :class="{
                                    'cursor-pointer hover:border-accent/40 hover:shadow-accent/5':
                                        athlete.athlete_profile
                                            ?.profile_photo_path,
                                }"
                                class="relative mb-4 flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border border-secondary bg-secondary/80 text-2xl font-black text-secondary-foreground uppercase shadow-inner transition-all"
                            >
                                <img
                                    v-if="
                                        athlete.athlete_profile
                                            ?.profile_photo_path
                                    "
                                    :src="`/documents/${athlete.id}/profile_photo`"
                                    class="h-full w-full object-cover"
                                />
                                <span v-else>{{
                                    athlete.name.substring(0, 2)
                                }}</span>
                            </div>
                            <h2
                                class="text-center text-xl font-black tracking-tight text-foreground uppercase"
                            >
                                {{ athlete.name }}
                            </h2>
                            <p
                                class="mt-1 flex items-center gap-2 text-[10px] font-bold tracking-widest text-muted-foreground uppercase"
                            >
                                <Mail class="h-3 w-3" /> {{ athlete.email }}
                            </p>
                        </div>
                        <div class="border-t border-border bg-card p-6">
                            <div class="flex flex-col gap-4">
                                <div
                                    class="flex items-center justify-between rounded-2xl bg-muted/30 p-4"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <p
                                            class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                        >
                                            Dilatih Oleh
                                        </p>
                                        <button
                                            v-if="!isEditingCoach"
                                            @click="toggleEditCoach"
                                            class="text-[9px] font-bold text-accent transition-all hover:underline"
                                        >
                                            GANTI
                                        </button>
                                    </div>
                                    <div
                                        v-if="isEditingCoach"
                                        class="mt-1 flex items-center gap-2"
                                    >
                                        <select
                                            v-model="coachForm.coach_id"
                                            class="flex-1 rounded-lg border border-muted bg-background px-2 py-1 text-xs font-bold text-foreground focus:ring-accent focus:outline-none dark:[color-scheme:dark]"
                                        >
                                            <option :value="null">
                                                Belum Ada Pelatih
                                            </option>
                                            <option
                                                v-for="c in coaches"
                                                :key="c.id"
                                                :value="c.id"
                                            >
                                                {{ c.name }}
                                            </option>
                                        </select>
                                        <button
                                            @click="updateCoach"
                                            :disabled="coachForm.processing"
                                            class="rounded-lg bg-emerald-500 p-1.5 text-white transition-all hover:bg-emerald-600 disabled:opacity-50"
                                        >
                                            <Save class="h-3 w-3" />
                                        </button>
                                        <button
                                            @click="toggleEditCoach"
                                            class="rounded-lg bg-muted p-1.5 text-muted-foreground transition-all hover:bg-muted-foreground hover:text-white"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                    <p
                                        v-else
                                        class="mt-0.5 text-sm font-black text-foreground uppercase"
                                    >
                                        {{
                                            athlete.coach?.name ||
                                            'BELUM ADA PELATIH'
                                        }}
                                    </p>
                                    <div
                                        class="rounded-full bg-orange-500/10 p-2 text-orange-500"
                                    >
                                        <Activity class="h-4 w-4" />
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-2xl bg-muted/30 p-4"
                                >
                                    <div>
                                        <p
                                            class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                        >
                                            Tanggal Bergabung
                                        </p>
                                        <p
                                            class="mt-0.5 text-sm font-black text-foreground uppercase"
                                        >
                                            {{ formatDate(athlete.created_at) }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-full bg-blue-500/10 p-2 text-blue-500"
                                    >
                                        <Calendar class="h-4 w-4" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Documents Viewer -->
                    <div
                        class="rounded-[2.5rem] border border-border bg-card p-8 shadow-xl"
                    >
                        <h3
                            class="mb-6 flex items-center gap-2 text-[12px] font-black tracking-widest text-foreground uppercase"
                        >
                            <FileText class="h-4 w-4 text-orange-500" /> Dokumen
                            Legal
                        </h3>

                        <div class="flex flex-col gap-3">
                            <div
                                class="flex items-center justify-between rounded-xl border border-border bg-muted/20 p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <UserCircle
                                        class="h-4 w-4 text-orange-500"
                                    />
                                    <span
                                        class="text-[10px] font-black tracking-widest text-foreground uppercase"
                                        >Pas Foto (Foto Profil)</span
                                    >
                                </div>
                                <button
                                    type="button"
                                    v-if="
                                        athlete.athlete_profile
                                            ?.profile_photo_path
                                    "
                                    @click="
                                        showPreview(
                                            `/documents/${athlete.id}/profile_photo`,
                                        )
                                    "
                                    class="rounded-full bg-accent/10 px-3 py-1 text-[9px] font-black text-accent uppercase transition-all hover:bg-accent hover:text-white"
                                >
                                    Lihat
                                </button>
                                <span
                                    v-else
                                    class="text-[9px] font-black text-destructive uppercase"
                                    >Kosong</span
                                >
                            </div>

                            <div
                                class="flex items-center justify-between rounded-xl border border-border bg-muted/20 p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <FileImage
                                        class="h-4 w-4 text-purple-500"
                                    />
                                    <span
                                        class="text-[10px] font-black tracking-widest text-foreground uppercase"
                                        >Akte Kelahiran</span
                                    >
                                </div>
                                <button
                                    type="button"
                                    v-if="
                                        athlete.athlete_profile
                                            ?.birth_certificate_path
                                    "
                                    @click="
                                        showPreview(
                                            `/documents/${athlete.id}/birth_certificate`,
                                        )
                                    "
                                    class="rounded-full bg-accent/10 px-3 py-1 text-[9px] font-black text-accent uppercase transition-all hover:bg-accent hover:text-white"
                                >
                                    Lihat
                                </button>
                                <span
                                    v-else
                                    class="text-[9px] font-black text-destructive uppercase"
                                    >Kosong</span
                                >
                            </div>

                            <div
                                class="flex items-center justify-between rounded-xl border border-border bg-muted/20 p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <Users class="h-4 w-4 text-emerald-500" />
                                    <span
                                        class="text-[10px] font-black tracking-widest text-foreground uppercase"
                                        >Kartu Keluarga</span
                                    >
                                </div>
                                <button
                                    type="button"
                                    v-if="
                                        athlete.athlete_profile
                                            ?.family_card_path
                                    "
                                    @click="
                                        showPreview(
                                            `/documents/${athlete.id}/family_card`,
                                        )
                                    "
                                    class="rounded-full bg-accent/10 px-3 py-1 text-[9px] font-black text-accent uppercase transition-all hover:bg-accent hover:text-white"
                                >
                                    Lihat
                                </button>
                                <span
                                    v-else
                                    class="text-[9px] font-black text-destructive uppercase"
                                    >Kosong</span
                                >
                            </div>

                            <div
                                class="flex items-center justify-between rounded-xl border border-border bg-muted/20 p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <CreditCard class="h-4 w-4 text-blue-500" />
                                    <span
                                        class="text-[10px] font-black tracking-widest text-foreground uppercase"
                                        >KTP</span
                                    >
                                </div>
                                <button
                                    type="button"
                                    v-if="athlete.athlete_profile?.id_card_path"
                                    @click="
                                        showPreview(
                                            `/documents/${athlete.id}/id_card`,
                                        )
                                    "
                                    class="rounded-full bg-accent/10 px-3 py-1 text-[9px] font-black text-accent uppercase transition-all hover:bg-accent hover:text-white"
                                >
                                    Lihat
                                </button>
                                <span
                                    v-else
                                    class="text-[9px] font-black text-destructive uppercase"
                                    >Kosong</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: License Management -->
                <div class="flex flex-col gap-8 md:col-span-2">
                    <div
                        class="relative overflow-hidden rounded-[2.5rem] border border-accent/20 bg-accent/5 p-8 shadow-2xl"
                    >
                        <div class="absolute -top-10 -right-10 text-accent/5">
                            <Shield class="h-64 w-64" />
                        </div>
                        <div class="relative z-10 flex flex-col gap-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="rounded-2xl bg-accent p-4 text-white shadow-xl shadow-accent/30"
                                >
                                    <Shield class="h-8 w-8" />
                                </div>
                                <div>
                                    <h2
                                        class="text-2xl font-black tracking-widest text-accent uppercase"
                                    >
                                        Manajemen Lisensi
                                    </h2>
                                    <p
                                        class="mt-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-80"
                                    >
                                        Data Utama & UCI ID
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="
                                    athlete.athlete_profile?.license_path &&
                                    athlete.athlete_profile?.uci_id
                                "
                                class="grid grid-cols-1 gap-6 rounded-3xl border border-border bg-card p-6 shadow-sm md:grid-cols-2"
                            >
                                <div>
                                    <p
                                        class="mb-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                    >
                                        UCI ID Saat Ini
                                    </p>
                                    <p
                                        class="font-mono text-xl font-black tracking-tighter text-foreground"
                                    >
                                        {{ athlete.athlete_profile.uci_id }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="mb-1 text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                    >
                                        Status Keberlakuan
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <span
                                            v-if="
                                                isLicenseValid(
                                                    athlete.athlete_profile
                                                        .license_valid_until,
                                                )
                                            "
                                            class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[10px] font-black tracking-widest text-emerald-500 uppercase"
                                        >
                                            <CheckCircle2 class="h-3 w-3" />
                                            Mengikat (Valid)
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-2 rounded-full border border-destructive/30 bg-destructive/10 px-3 py-1 text-[10px] font-black tracking-widest text-destructive uppercase"
                                        >
                                            <AlertCircle class="h-3 w-3" />
                                            Kadaluarsa
                                        </span>
                                    </div>
                                    <div
                                        class="mt-2 text-xs font-bold text-foreground"
                                    >
                                        Hingga:
                                        <span class="text-accent">{{
                                            formatDate(
                                                athlete.athlete_profile
                                                    .license_valid_until!,
                                            )
                                        }}</span>
                                    </div>
                                </div>
                                <div
                                    class="border-t border-border pt-2 md:col-span-2"
                                >
                                    <button
                                        type="button"
                                        @click="
                                            showPreview(
                                                `/documents/${athlete.id}/license`,
                                            )
                                        "
                                        class="inline-flex items-center gap-2 text-xs font-black tracking-widest text-accent uppercase hover:underline"
                                    >
                                        <FileText class="h-4 w-4" /> Buka
                                        Dokumen Lisensi PDF/JPG Saat Ini
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload License Form -->
                    <form
                        @submit.prevent="submitLicense"
                        class="flex flex-col gap-6 rounded-[2.5rem] border border-border bg-card p-8 shadow-xl"
                    >
                        <div class="border-b border-border pb-4">
                            <h3
                                class="text-lg font-black tracking-tight text-foreground uppercase"
                            >
                                Perbarui/Unggah Lisensi
                            </h3>
                            <p
                                class="mt-1 text-xs font-medium text-muted-foreground"
                            >
                                Masukkan Nomor UCI ID dan Lampiran File Lisensi
                                resmi (PDF/JPG) dari federasi.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <Label
                                    class="text-[10px] font-black tracking-widest uppercase opacity-70"
                                    >Nomor UCI ID</Label
                                >
                                <Input
                                    v-model="form.uci_id"
                                    class="h-14 rounded-2xl border-none bg-muted/40 px-6 font-mono text-sm font-bold"
                                    placeholder="Contoh: 100 123 456 78"
                                    required
                                />
                                <p
                                    v-if="form.errors.uci_id"
                                    class="text-[10px] font-black text-destructive uppercase"
                                >
                                    {{ form.errors.uci_id }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-2">
                                <Label
                                    class="text-[10px] font-black tracking-widest uppercase opacity-70"
                                    >Berlaku Hingga</Label
                                >
                                <Input
                                    type="date"
                                    v-model="form.license_valid_until"
                                    class="h-14 rounded-2xl border-none bg-muted/40 px-6 font-mono text-sm font-bold"
                                    required
                                />
                                <p
                                    v-if="form.errors.license_valid_until"
                                    class="text-[10px] font-black text-destructive uppercase"
                                >
                                    {{ form.errors.license_valid_until }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-2 md:col-span-2">
                                <Label
                                    class="text-[10px] font-black tracking-widest uppercase opacity-70"
                                    >Dokumen Lisensi (PDF/Image)</Label
                                >
                                <div
                                    class="flex flex-col gap-2 rounded-2xl border border-dashed border-accent/30 bg-accent/5 p-6 transition-all hover:border-accent/50 hover:bg-accent/10"
                                >
                                    <input
                                        id="license_file"
                                        type="file"
                                        @change="handleFileUpload"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        class="text-xs font-medium file:mr-4 file:rounded-full file:border-none file:bg-accent file:px-6 file:py-3 file:text-[10px] file:font-black file:text-white file:uppercase hover:file:bg-accent/90"
                                    />
                                </div>
                                <p
                                    class="mt-1 text-[10px] font-bold text-muted-foreground italic"
                                >
                                    *Jika file tidak dipilih, hanya UCI ID &
                                    Tanggal Kadaluarsa yang akan diperbarui.
                                </p>
                                <p
                                    v-if="form.errors.license_file"
                                    class="text-[10px] font-black text-destructive uppercase"
                                >
                                    {{ form.errors.license_file }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex items-center gap-2 rounded-[2rem] bg-accent px-8 py-4 text-[10px] font-black tracking-widest text-white uppercase shadow-lg shadow-accent/30 transition-all hover:scale-105 active:scale-95 disabled:opacity-50"
                            >
                                <UploadCloud class="h-4 w-4" />
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'TERBITKAN LISENSI'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Document Preview Modal -->
        <div
            v-if="previewUrl"
            class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 p-4 backdrop-blur-md sm:p-8"
            @click="closePreview"
        >
            <div
                class="relative flex h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-[2rem] border border-border bg-card shadow-2xl"
                @click.stop
            >
                <div
                    class="flex shrink-0 items-center justify-between border-b border-border bg-muted/20 p-4 md:p-6"
                >
                    <h3
                        class="truncate text-sm font-black tracking-widest text-muted-foreground uppercase"
                    >
                        Pratinjau Dokumen
                    </h3>
                    <button
                        @click="closePreview"
                        class="rounded-full px-4 py-2 text-xs font-black text-muted-foreground uppercase transition-all hover:bg-destructive/10 hover:text-destructive"
                    >
                        Tutup (X)
                    </button>
                </div>
                <div
                    class="relative flex-1 overflow-hidden bg-muted/30 p-2 md:p-6"
                >
                    <!-- Using iframe as universal previewer for both image and PDF from Laravel generic file stream -->
                    <iframe
                        :src="previewUrl"
                        class="h-full w-full rounded-xl border border-border bg-white shadow-inner"
                        title="Document Preview"
                    ></iframe>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
