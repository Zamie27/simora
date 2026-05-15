<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Shield, 
    FileText, 
    UserCircle, 
    FileImage, 
    Users, 
    CreditCard, 
    Save, 
    X, 
    CheckCircle2, 
    AlertCircle,
    Eye,
    Upload,
    Calendar,
    Search,
    Download
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { useSnackbar } from '@/composables/useSnackbar';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';

interface AthleteProfile {
    id: number;
    uci_id: string | null;
    license_valid_until: string | null;
    profile_photo_path: string | null;
    birth_certificate_path: string | null;
    family_card_path: string | null;
    id_card_path: string | null;
    license_path: string | null;
}

interface User {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    coach?: { name: string };
    athlete_profile: AthleteProfile | null;
}

const props = defineProps<{
    athlete?: User;
    athletes?: User[];
    role: 'Manajemen' | 'Pelatih' | 'Atlet';
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Lisensi UCI & Dokumen', href: '#' },
];

const snackbar = useSnackbar();

// --- ATLET LOGIC ---
const profilePhotoInput = ref<HTMLInputElement | null>(null);
const birthCertificateInput = ref<HTMLInputElement | null>(null);
const familyCardInput = ref<HTMLInputElement | null>(null);
const idCardInput = ref<HTMLInputElement | null>(null);

const personalDocForm = useForm({
    profile_photo: null as File | null,
    birth_certificate: null as File | null,
    family_card: null as File | null,
    id_card: null as File | null,
});

const isAnyFileSelected = computed(() => {
    return personalDocForm.profile_photo !== null ||
           personalDocForm.birth_certificate !== null ||
           personalDocForm.family_card !== null ||
           personalDocForm.id_card !== null;
});

const localPreviews = ref({
    profile_photo: null as string | null,
    birth_certificate: null as string | null,
    family_card: null as string | null,
    id_card: null as string | null,
});

const handleFileSelect = (key: 'profile_photo' | 'birth_certificate' | 'family_card' | 'id_card', event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] || null;
    personalDocForm[key] = file;
    
    if (file) {
        if (localPreviews.value[key]) {
            URL.revokeObjectURL(localPreviews.value[key]!);
        }
        localPreviews.value[key] = URL.createObjectURL(file);
    }
};

const submitPersonalDocs = () => {
    if (!isAnyFileSelected.value) {
        snackbar.info('Tidak ada dokumen yang diubah.');
        return;
    }

    personalDocForm.post('/lisensi-uci/upload', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            snackbar.success('Dokumen pribadi berhasil diunggah dan diperbarui.');
            // Clear local previews after successful upload
            Object.values(localPreviews.value).forEach(url => {
                if (url) URL.revokeObjectURL(url);
            });
            localPreviews.value = {
                profile_photo: null,
                birth_certificate: null,
                family_card: null,
                id_card: null,
            };
            personalDocForm.reset();
        },
        onError: () => {
            snackbar.error('Gagal mengunggah dokumen. Silakan periksa kembali file Anda.');
        }
    });
};

// --- MANAJEMEN LOGIC ---
const searchQuery = ref('');
const selectedAthlete = ref<User | null>(null);
const detailedAthlete = ref<User | null>(null);

const filteredAthletes = computed(() => {
    if (!props.athletes) return [];
    return props.athletes.filter(a => 
        a.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        a.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const licenseForm = useForm({
    uci_id: '',
    license_valid_until: '',
    license_file: null as File | null,
});

const openLicenseModal = (athlete: User) => {
    selectedAthlete.value = athlete;
    licenseForm.uci_id = athlete.athlete_profile?.uci_id || '';
    licenseForm.license_valid_until = athlete.athlete_profile?.license_valid_until || '';
    licenseForm.license_file = null;
};

const submitLicense = () => {
    const targetId = detailedAthlete.value?.id || selectedAthlete.value?.id;
    if (!targetId) return;
    
    licenseForm.post(`/lisensi-uci/update/${targetId}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            snackbar.success(`Lisensi UCI berhasil diperbarui.`);
            selectedAthlete.value = null;
            // Update detailedAthlete with new data if it's the one being edited
            if (detailedAthlete.value && detailedAthlete.value.id === targetId) {
                // Since props are updated, Inertia will refresh the data. 
                // However, detailedAthlete is a local ref. 
                // We should probably rely on the props update.
                // For simplicity, let's just close the modal if it's from modal.
            }
            licenseForm.reset();
        },
        onError: () => {
            snackbar.error('Gagal memperbarui lisensi UCI.');
        }
    });
};

// --- PREVIEW LOGIC ---
const previewUrl = ref<string | null>(null);
const showPreview = (url: string) => {
    previewUrl.value = url;
};
const closePreview = () => {
    previewUrl.value = null;
};

const viewAthleteDetail = (athlete: User) => {
    detailedAthlete.value = athlete;
    // Pre-fill license form in case management wants to edit
    licenseForm.uci_id = athlete.athlete_profile?.uci_id || '';
    licenseForm.license_valid_until = athlete.athlete_profile?.license_valid_until || '';
    licenseForm.license_file = null;
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const isLicenseValid = (dateString: string | null) => {
    if (!dateString) return false;
    return new Date(dateString) >= new Date();
};

const getThumbnailUrl = (athleteId: number, type: string) => {
    return `/documents/${athleteId}/${type}?v=${new Date().getTime()}`;
};

</script>

<template>
    <Head title="Lisensi UCI & Dokumen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto min-h-screen w-full max-w-7xl p-6 md:p-10">
            <!-- Header -->
            <div class="mb-10 flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">
                        Lisensi UCI & Dokumen Legal
                    </h1>
                    <p class="mt-2 font-medium text-muted-foreground">
                        Kelola data lisensi balap dan dokumen identitas atlet.
                    </p>
                </div>
            </div>

            <!-- VIEW FOR ATLET -->
            <div v-if="role === 'Atlet' && athlete" class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Left: Upload Section -->
                <div class="lg:col-span-8 space-y-8">
                    <div class="rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl">
                        <h3 class="mb-8 flex items-center gap-3 text-xl font-black tracking-tight uppercase">
                            <Upload class="h-6 w-6 text-accent" /> Unggah Dokumen Pribadi
                        </h3>
                        
                        <div class="space-y-8">
                            <div v-if="Object.keys(personalDocForm.errors).length > 0" class="p-4 rounded-2xl bg-destructive/10 border border-destructive/20 mb-6">
                                <div class="flex items-center gap-3 text-destructive mb-2">
                                    <AlertCircle class="h-5 w-5" />
                                    <span class="text-xs font-black uppercase">Terjadi Kesalahan</span>
                                </div>
                                <ul class="list-disc list-inside space-y-1">
                                    <li v-for="(error, key) in personalDocForm.errors" :key="key" class="text-[10px] font-bold text-destructive/80 uppercase">
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Profile Photo -->
                                <div class="flex flex-col gap-4 p-6 rounded-3xl border border-border bg-muted/20 relative overflow-hidden group">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-xl bg-accent/10 text-accent">
                                                <UserCircle class="h-5 w-5" />
                                            </div>
                                            <span class="text-xs font-black uppercase">Foto Profil</span>
                                        </div>
                                        <CheckCircle2 v-if="athlete.athlete_profile?.profile_photo_path || personalDocForm.profile_photo" class="h-5 w-5 text-emerald-500" />
                                    </div>
                                    
                                    <div class="relative aspect-square w-32 mx-auto overflow-hidden rounded-2xl border-4 border-card shadow-lg bg-secondary/50 group/thumb">
                                        <img v-if="localPreviews.profile_photo" :src="localPreviews.profile_photo" class="h-full w-full object-cover transition-transform group-hover/thumb:scale-110" />
                                        <img v-else-if="athlete.athlete_profile?.profile_photo_path" :src="getThumbnailUrl(athlete.id, 'profile_photo')" class="h-full w-full object-cover transition-transform group-hover/thumb:scale-110" />
                                        <div v-else class="h-full w-full flex items-center justify-center text-muted-foreground/30">
                                            <UserCircle class="h-12 w-12" />
                                        </div>
                                        
                                        <!-- Hover Overlay for Preview -->
                                        <button v-if="localPreviews.profile_photo || athlete.athlete_profile?.profile_photo_path" 
                                            @click.prevent="showPreview(localPreviews.profile_photo || getThumbnailUrl(athlete.id, 'profile_photo'))" 
                                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/thumb:opacity-100 transition-all bg-black/40 backdrop-blur-sm">
                                            <Eye class="h-8 w-8 text-white" />
                                        </button>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <input type="file" ref="profilePhotoInput" @change="handleFileSelect('profile_photo', $event)" class="hidden" accept="image/*" />
                                        <button @click.prevent="($refs.profilePhotoInput as HTMLInputElement).click()" class="w-full py-3 rounded-xl border border-accent/20 bg-accent/5 text-[10px] font-black text-accent uppercase hover:bg-accent hover:text-white transition-all">
                                            {{ personalDocForm.profile_photo ? personalDocForm.profile_photo.name : 'Ubah Foto Profil' }}
                                        </button>
                                        <p class="text-[9px] text-muted-foreground font-medium italic text-center">Sama dengan foto profil di pengaturan.</p>
                                    </div>
                                </div>

                                <!-- Birth Certificate -->
                                <div class="flex flex-col gap-4 p-6 rounded-3xl border border-border bg-muted/20 relative overflow-hidden group">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-xl bg-blue-500/10 text-blue-500">
                                                <FileImage class="h-5 w-5" />
                                            </div>
                                            <span class="text-xs font-black uppercase">Akte Kelahiran</span>
                                        </div>
                                        <CheckCircle2 v-if="athlete.athlete_profile?.birth_certificate_path || personalDocForm.birth_certificate" class="h-5 w-5 text-emerald-500" />
                                    </div>

                                    <div class="relative aspect-video w-full overflow-hidden rounded-2xl border-2 border-card shadow-md bg-secondary/50 group/thumb">
                                        <img v-if="localPreviews.birth_certificate" :src="localPreviews.birth_certificate" class="h-full w-full object-cover opacity-80 group-hover/thumb:opacity-100 transition-all" />
                                        <img v-else-if="athlete.athlete_profile?.birth_certificate_path" :src="getThumbnailUrl(athlete.id, 'birth_certificate')" class="h-full w-full object-cover opacity-80 group-hover/thumb:opacity-100 transition-all" />
                                        <div v-else class="h-full w-full flex items-center justify-center text-muted-foreground/30">
                                            <FileImage class="h-10 w-10" />
                                        </div>
                                        
                                        <!-- Hover Overlay -->
                                        <button v-if="localPreviews.birth_certificate || athlete.athlete_profile?.birth_certificate_path" 
                                            @click.prevent="showPreview(localPreviews.birth_certificate || getThumbnailUrl(athlete.id, 'birth_certificate'))" 
                                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/thumb:opacity-100 transition-all bg-black/40 backdrop-blur-sm">
                                            <Eye class="h-8 w-8 text-white" />
                                        </button>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <input type="file" ref="birthCertificateInput" @change="handleFileSelect('birth_certificate', $event)" class="hidden" />
                                        <button @click.prevent="($refs.birthCertificateInput as HTMLInputElement).click()" class="w-full py-3 rounded-xl border border-blue-500/20 bg-blue-500/5 text-[10px] font-black text-blue-500 uppercase hover:bg-blue-500 hover:text-white transition-all">
                                            {{ personalDocForm.birth_certificate ? personalDocForm.birth_certificate.name : 'Ubah Akte Kelahiran' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Family Card -->
                                <div class="flex flex-col gap-4 p-6 rounded-3xl border border-border bg-muted/20 relative overflow-hidden group">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-xl bg-orange-500/10 text-orange-500">
                                                <Users class="h-5 w-5" />
                                            </div>
                                            <span class="text-xs font-black uppercase">Kartu Keluarga</span>
                                        </div>
                                        <CheckCircle2 v-if="athlete.athlete_profile?.family_card_path || personalDocForm.family_card" class="h-5 w-5 text-emerald-500" />
                                    </div>

                                    <div class="relative aspect-video w-full overflow-hidden rounded-2xl border-2 border-card shadow-md bg-secondary/50 group/thumb">
                                        <img v-if="localPreviews.family_card" :src="localPreviews.family_card" class="h-full w-full object-cover opacity-80 group-hover/thumb:opacity-100 transition-all" />
                                        <img v-else-if="athlete.athlete_profile?.family_card_path" :src="getThumbnailUrl(athlete.id, 'family_card')" class="h-full w-full object-cover opacity-80 group-hover/thumb:opacity-100 transition-all" />
                                        <div v-else class="h-full w-full flex items-center justify-center text-muted-foreground/30">
                                            <Users class="h-10 w-10" />
                                        </div>
                                        
                                        <!-- Hover Overlay -->
                                        <button v-if="localPreviews.family_card || athlete.athlete_profile?.family_card_path" 
                                            @click.prevent="showPreview(localPreviews.family_card || getThumbnailUrl(athlete.id, 'family_card'))" 
                                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/thumb:opacity-100 transition-all bg-black/40 backdrop-blur-sm">
                                            <Eye class="h-8 w-8 text-white" />
                                        </button>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <input type="file" ref="familyCardInput" @change="handleFileSelect('family_card', $event)" class="hidden" />
                                        <button @click.prevent="($refs.familyCardInput as HTMLInputElement).click()" class="w-full py-3 rounded-xl border border-orange-500/20 bg-orange-500/5 text-[10px] font-black text-orange-500 uppercase hover:bg-orange-500 hover:text-white transition-all">
                                            {{ personalDocForm.family_card ? personalDocForm.family_card.name : 'Ubah Kartu Keluarga' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- ID Card -->
                                <div class="flex flex-col gap-4 p-6 rounded-3xl border border-border bg-muted/20 relative overflow-hidden group">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-xl bg-purple-500/10 text-purple-500">
                                                <CreditCard class="h-5 w-5" />
                                            </div>
                                            <span class="text-xs font-black uppercase">KTP (Opsional)</span>
                                        </div>
                                        <CheckCircle2 v-if="athlete.athlete_profile?.id_card_path || personalDocForm.id_card" class="h-5 w-5 text-emerald-500" />
                                    </div>

                                    <div class="relative aspect-video w-full overflow-hidden rounded-2xl border-2 border-card shadow-md bg-secondary/50 group/thumb">
                                        <img v-if="localPreviews.id_card" :src="localPreviews.id_card" class="h-full w-full object-cover opacity-80 group-hover/thumb:opacity-100 transition-all" />
                                        <img v-else-if="athlete.athlete_profile?.id_card_path" :src="getThumbnailUrl(athlete.id, 'id_card')" class="h-full w-full object-cover opacity-80 group-hover/thumb:opacity-100 transition-all" />
                                        <div v-else class="h-full w-full flex items-center justify-center text-muted-foreground/30">
                                            <CreditCard class="h-10 w-10" />
                                        </div>
                                        
                                        <!-- Hover Overlay -->
                                        <button v-if="localPreviews.id_card || athlete.athlete_profile?.id_card_path" 
                                            @click.prevent="showPreview(localPreviews.id_card || getThumbnailUrl(athlete.id, 'id_card'))" 
                                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/thumb:opacity-100 transition-all bg-black/40 backdrop-blur-sm">
                                            <Eye class="h-8 w-8 text-white" />
                                        </button>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <input type="file" ref="idCardInput" @change="handleFileSelect('id_card', $event)" class="hidden" />
                                        <button @click.prevent="($refs.idCardInput as HTMLInputElement).click()" class="w-full py-3 rounded-xl border border-purple-500/20 bg-purple-500/5 text-[10px] font-black text-purple-500 uppercase hover:bg-purple-500 hover:text-white transition-all">
                                            {{ personalDocForm.id_card ? personalDocForm.id_card.name : 'Ubah KTP' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="button" @click="submitPersonalDocs" :disabled="personalDocForm.processing" class="flex items-center gap-3 rounded-2xl bg-accent px-10 py-4 text-xs font-black tracking-widest text-white uppercase shadow-xl shadow-accent/20 hover:bg-accent/90 disabled:opacity-50 transition-all">
                                    <Save class="h-5 w-5" />
                                    {{ personalDocForm.processing ? 'Menyimpan...' : 'Simpan Dokumen' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: License Status -->
                <div class="lg:col-span-4 space-y-8">
                    <div class="rounded-[2.5rem] border border-accent/20 bg-accent/5 p-8 shadow-2xl relative overflow-hidden">
                        <div class="absolute -top-4 -right-4 h-24 w-24 bg-accent/10 rounded-full blur-3xl"></div>
                        <h3 class="relative z-10 mb-8 flex items-center gap-3 text-lg font-black tracking-tight uppercase text-accent">
                            <Shield class="h-6 w-6" /> Status Lisensi UCI
                        </h3>

                        <div v-if="athlete.athlete_profile?.uci_id" class="relative z-10 space-y-6">
                            <div class="p-6 rounded-3xl bg-card shadow-inner border border-border/50">
                                <p class="text-[10px] font-black text-muted-foreground uppercase opacity-60 mb-1">UCI ID</p>
                                <p class="text-2xl font-black tracking-tighter italic text-foreground">{{ athlete.athlete_profile.uci_id }}</p>
                            </div>

                            <div class="p-6 rounded-3xl bg-card shadow-inner border border-border/50">
                                <p class="text-[10px] font-black text-muted-foreground uppercase opacity-60 mb-1">Berlaku Sampai</p>
                                <div class="flex items-center justify-between">
                                    <p class="text-lg font-black text-foreground">{{ formatDate(athlete.athlete_profile.license_valid_until) }}</p>
                                    <span :class="isLicenseValid(athlete.athlete_profile.license_valid_until) ? 'bg-emerald-500/10 text-emerald-500' : 'bg-destructive/10 text-destructive'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                        {{ isLicenseValid(athlete.athlete_profile.license_valid_until) ? 'Aktif' : 'Kedaluwarsa' }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="athlete.athlete_profile?.license_path" class="p-6 rounded-3xl bg-card shadow-inner border border-border/50 group">
                                <p class="text-[10px] font-black text-muted-foreground uppercase opacity-60 mb-4">Kartu Lisensi Digital</p>
                                <div class="relative aspect-video w-full overflow-hidden rounded-2xl bg-muted/30 flex items-center justify-center">
                                    <img :src="getThumbnailUrl(athlete.id, 'license')" class="h-full w-full object-cover opacity-60 group-hover:opacity-100 transition-opacity" />
                                    <button @click="showPreview(getThumbnailUrl(athlete.id, 'license'))" class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 group-hover:opacity-100 transition-all backdrop-blur-sm">
                                        <Eye class="h-8 w-8 text-white" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="relative z-10 p-10 text-center space-y-4">
                            <div class="w-16 h-16 bg-muted/20 rounded-full flex items-center justify-center mx-auto text-muted-foreground">
                                <Shield class="h-8 w-8 opacity-40" />
                            </div>
                            <p class="text-xs font-black text-muted-foreground uppercase opacity-50 italic">Lisensi belum diterbitkan oleh manajemen.</p>
                        </div>
                    </div>
                    
                    <div class="rounded-[2.5rem] border border-border bg-card p-8 shadow-xl">
                        <h4 class="text-[10px] font-black uppercase text-muted-foreground opacity-60 mb-4">Informasi Penting</h4>
                        <ul class="space-y-3">
                            <li class="flex gap-3 text-[11px] font-medium leading-relaxed">
                                <AlertCircle class="h-4 w-4 shrink-0 text-accent" />
                                <span>Pastikan dokumen yang diunggah terbaca dengan jelas.</span>
                            </li>
                            <li class="flex gap-3 text-[11px] font-medium leading-relaxed">
                                <AlertCircle class="h-4 w-4 shrink-0 text-accent" />
                                <span>Dokumen legal diperlukan untuk keperluan administrasi lomba UCI.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- VIEW FOR MANAJEMEN / PELATIH -->
            <div v-else-if="(role === 'Manajemen' || role === 'Pelatih') && athletes" class="space-y-8">
                <!-- GRID LIST VIEW -->
                <div v-if="!detailedAthlete" class="space-y-8">
                    <!-- Search & Filter -->
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between p-6 rounded-[2rem] border border-border bg-card shadow-xl">
                        <div class="relative w-full md:w-96">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input v-model="searchQuery" placeholder="Cari nama atlet..." class="pl-12 h-12 rounded-2xl border-border bg-background font-bold text-xs" />
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-[10px] font-black text-muted-foreground uppercase">Total Atlet: {{ filteredAthletes.length }}</span>
                        </div>
                    </div>

                    <!-- Athletes Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="a in filteredAthletes" :key="a.id" @click="viewAthleteDetail(a)" class="group relative rounded-[2.5rem] border border-border bg-card p-8 shadow-xl transition-all hover:border-accent/40 hover:shadow-accent/5 cursor-pointer">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="h-16 w-16 rounded-full border-2 border-accent/20 p-1">
                                    <div class="h-full w-full rounded-full overflow-hidden bg-secondary">
                                        <img v-if="a.athlete_profile?.profile_photo_path" :src="getThumbnailUrl(a.id, 'profile_photo')" class="h-full w-full object-cover" />
                                        <img v-else-if="a.avatar" :src="a.avatar" class="h-full w-full object-cover" />
                                        <div v-else class="h-full w-full flex items-center justify-center text-lg font-black text-accent uppercase">
                                            {{ a.name.charAt(0) }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black uppercase truncate max-w-[150px]">{{ a.name }}</h3>
                                    <p class="text-[10px] font-bold text-muted-foreground">{{ a.email }}</p>
                                    <span v-if="a.coach" class="text-[9px] font-black text-accent/80 uppercase mt-1 block">Pelatih: {{ a.coach.name }}</span>
                                </div>
                            </div>

                            <!-- Documents Summary -->
                            <div class="space-y-3 mb-8">
                                <div v-for="doc in [
                                    { label: 'Akte', path: 'birth_certificate' },
                                    { label: 'KK', path: 'family_card' },
                                    { label: 'KTP', path: 'id_card' }
                                ]" :key="doc.path" class="flex items-center justify-between p-3 rounded-2xl bg-muted/20 border border-border/50">
                                    <span class="text-[9px] font-black uppercase text-muted-foreground">{{ doc.label }}</span>
                                    <div v-if="a.athlete_profile?.[`${doc.path}_path` as keyof typeof a.athlete_profile]" class="flex items-center gap-2">
                                        <span class="text-[8px] font-black text-emerald-500 uppercase">Ada</span>
                                        <CheckCircle2 class="h-3 w-3 text-emerald-500" />
                                    </div>
                                    <span v-else class="text-[8px] font-black text-destructive/50 uppercase">Belum</span>
                                </div>
                            </div>

                            <!-- License Status -->
                            <div class="p-5 rounded-[2rem] border border-accent/10 bg-accent/5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] font-black text-accent uppercase">Lisensi UCI</span>
                                    <span v-if="a.athlete_profile?.uci_id" :class="isLicenseValid(a.athlete_profile.license_valid_until) ? 'text-emerald-500' : 'text-destructive'" class="text-[8px] font-black uppercase">
                                        {{ isLicenseValid(a.athlete_profile.license_valid_until) ? 'Aktif' : 'Kedaluwarsa' }}
                                    </span>
                                    <span v-else class="text-[8px] font-black text-muted-foreground uppercase opacity-40 italic">Belum Ada</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="filteredAthletes.length === 0" class="p-20 text-center rounded-[2.5rem] border border-dashed border-border bg-card">
                        <p class="text-xs font-black text-muted-foreground uppercase opacity-40 italic">Tidak ada atlet ditemukan.</p>
                    </div>
                </div>

                <!-- DETAILED VIEW (DRILL DOWN) -->
                <div v-else class="space-y-8">
                    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                        <button @click="detailedAthlete = null" class="w-full sm:w-auto flex items-center gap-2 px-6 py-3 rounded-xl bg-card border border-border text-[10px] font-black uppercase hover:bg-muted transition-all">
                            <X class="h-4 w-4" /> Kembali ke Daftar
                        </button>

                        <a v-if="role === 'Manajemen'" 
                           :href="`/lisensi-uci/download-all/${detailedAthlete.id}`" 
                           class="w-full sm:w-auto flex items-center justify-center gap-3 px-8 py-3 rounded-xl bg-emerald-600 text-white text-[10px] font-black uppercase shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition-all">
                            <Download class="h-4 w-4" /> Unduh Semua Dokumen (.zip)
                        </a>
                    </div>

                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                        <!-- Left: Document List (View Only) -->
                        <div class="lg:col-span-8 space-y-8">
                            <div class="rounded-[2.5rem] border border-border bg-card p-8 shadow-2xl">
                                <h3 class="mb-8 flex items-center gap-3 text-xl font-black tracking-tight uppercase text-muted-foreground">
                                    <FileText class="h-6 w-6" /> Dokumen Atlet: {{ detailedAthlete.name }}
                                </h3>
                                
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div v-for="doc in [
                                        { label: 'Foto Profil', path: 'profile_photo', icon: UserCircle, color: 'accent' },
                                        { label: 'Akte Kelahiran', path: 'birth_certificate', icon: FileImage, color: 'blue-500' },
                                        { label: 'Kartu Keluarga', path: 'family_card', icon: Users, color: 'orange-500' },
                                        { label: 'KTP (Opsional)', path: 'id_card', icon: CreditCard, color: 'purple-500' }
                                    ]" :key="doc.path" class="flex flex-col gap-4 p-6 rounded-3xl border border-border bg-muted/20 relative overflow-hidden group/card">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div :class="`p-2 rounded-xl bg-${doc.color}/10 text-${doc.color}`">
                                                    <component :is="doc.icon" class="h-5 w-5" />
                                                </div>
                                                <span class="text-xs font-black uppercase">{{ doc.label }}</span>
                                            </div>
                                            <CheckCircle2 v-if="detailedAthlete.athlete_profile?.[`${doc.path}_path` as keyof typeof detailedAthlete.athlete_profile]" class="h-5 w-5 text-emerald-500" />
                                        </div>

                                        <div class="relative aspect-video w-full overflow-hidden rounded-2xl border-2 border-card shadow-md bg-secondary/50 group/thumb">
                                            <img v-if="detailedAthlete.athlete_profile?.[`${doc.path}_path` as keyof typeof detailedAthlete.athlete_profile]" 
                                                :src="getThumbnailUrl(detailedAthlete.id, doc.path)" 
                                                class="h-full w-full object-cover opacity-80 group-hover/thumb:opacity-100 transition-all" />
                                            <div v-else class="h-full w-full flex items-center justify-center text-muted-foreground/30 italic text-[10px] font-bold uppercase">
                                                Belum Diunggah
                                            </div>
                                            
                                            <!-- Hover Overlay -->
                                            <button v-if="detailedAthlete.athlete_profile?.[`${doc.path}_path` as keyof typeof detailedAthlete.athlete_profile]" 
                                                @click.prevent="showPreview(getThumbnailUrl(detailedAthlete.id, doc.path))" 
                                                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/thumb:opacity-100 transition-all bg-black/40 backdrop-blur-sm">
                                                <Eye class="h-8 w-8 text-white" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: License Status (Editable for Manajemen) -->
                        <div class="lg:col-span-4 space-y-8">
                            <div class="rounded-[2.5rem] border border-accent/20 bg-accent/5 p-8 shadow-2xl relative overflow-hidden">
                                <h3 class="relative z-10 mb-8 flex items-center gap-3 text-lg font-black tracking-tight uppercase text-accent">
                                    <Shield class="h-6 w-6" /> Status Lisensi UCI
                                </h3>

                                <!-- FORM FOR MANAGEMENT -->
                                <div v-if="role === 'Manajemen'" class="space-y-6 relative z-10">
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-black uppercase opacity-60">UCI ID</Label>
                                        <Input v-model="licenseForm.uci_id" placeholder="Masukkan UCI ID..." class="h-12 rounded-xl border-border bg-card font-black" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-black uppercase opacity-60">Berlaku Sampai</Label>
                                        <Input v-model="licenseForm.license_valid_until" type="date" class="h-12 rounded-xl border-border bg-card font-black" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-black uppercase opacity-60">File Lisensi (PDF/Gambar)</Label>
                                        <div class="relative group/file">
                                            <div v-if="detailedAthlete.athlete_profile?.license_path" class="mb-2 relative aspect-video rounded-xl overflow-hidden border border-border">
                                                <img :src="getThumbnailUrl(detailedAthlete.id, 'license')" class="w-full h-full object-cover" />
                                                <button @click="showPreview(getThumbnailUrl(detailedAthlete.id, 'license'))" class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover/file:opacity-100 transition-all">
                                                    <Eye class="h-6 w-6 text-white" />
                                                </button>
                                            </div>
                                            <Input type="file" @change="licenseForm.license_file = ($event.target as HTMLInputElement).files?.[0] || null" class="text-[10px] font-bold h-auto py-2" />
                                        </div>
                                    </div>
                                    <button @click="submitLicense" :disabled="licenseForm.processing" class="w-full py-4 rounded-xl bg-accent text-white text-[10px] font-black uppercase shadow-lg shadow-accent/20 hover:bg-accent/90 transition-all disabled:opacity-50">
                                        {{ licenseForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                    </button>
                                </div>

                                <!-- VIEW FOR COACH -->
                                <div v-else class="space-y-6 relative z-10">
                                    <div v-if="detailedAthlete.athlete_profile?.uci_id" class="space-y-6">
                                        <div class="p-6 rounded-3xl bg-card shadow-inner border border-border/50">
                                            <p class="text-[10px] font-black text-muted-foreground uppercase opacity-60 mb-1">UCI ID</p>
                                            <p class="text-2xl font-black tracking-tighter italic text-foreground">{{ detailedAthlete.athlete_profile.uci_id }}</p>
                                        </div>

                                        <div class="p-6 rounded-3xl bg-card shadow-inner border border-border/50">
                                            <p class="text-[10px] font-black text-muted-foreground uppercase opacity-60 mb-1">Berlaku Sampai</p>
                                            <div class="flex items-center justify-between">
                                                <p class="text-lg font-black text-foreground">{{ formatDate(detailedAthlete.athlete_profile.license_valid_until) }}</p>
                                                <span :class="isLicenseValid(detailedAthlete.athlete_profile.license_valid_until) ? 'bg-emerald-500/10 text-emerald-500' : 'bg-destructive/10 text-destructive'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                                    {{ isLicenseValid(detailedAthlete.athlete_profile.license_valid_until) ? 'Aktif' : 'Kedaluwarsa' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div v-if="detailedAthlete.athlete_profile?.license_path" class="p-6 rounded-3xl bg-card shadow-inner border border-border/50 group">
                                            <p class="text-[10px] font-black text-muted-foreground uppercase opacity-60 mb-4">Kartu Lisensi Digital</p>
                                            <div class="relative aspect-video w-full overflow-hidden rounded-2xl bg-muted/30 flex items-center justify-center">
                                                <img :src="getThumbnailUrl(detailedAthlete.id, 'license')" class="h-full w-full object-cover opacity-60 group-hover:opacity-100 transition-opacity" />
                                                <button @click="showPreview(getThumbnailUrl(detailedAthlete.id, 'license'))" class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-all backdrop-blur-sm">
                                                    <Eye class="h-8 w-8 text-white" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="p-10 text-center space-y-4">
                                        <div class="w-16 h-16 bg-muted/20 rounded-full flex items-center justify-center mx-auto text-muted-foreground">
                                            <Shield class="h-8 w-8 opacity-40" />
                                        </div>
                                        <p class="text-xs font-black text-muted-foreground uppercase opacity-50 italic">Lisensi belum diterbitkan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL: MANAJEMEN UPDATE LICENSE -->
            <div v-if="selectedAthlete" class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-xl">
                <div class="w-full max-w-xl animate-in fade-in zoom-in duration-300 rounded-[3rem] border border-border bg-card shadow-[0_100px_150px_-50px_rgba(0,0,0,0.5)] overflow-hidden">
                    <div class="flex items-center justify-between border-b border-border p-10 bg-muted/20">
                        <div>
                            <p class="text-[10px] font-black text-accent uppercase tracking-widest mb-1">Manajemen Lisensi</p>
                            <h2 class="text-2xl font-black tracking-tight uppercase truncate max-w-[300px]">{{ selectedAthlete.name }}</h2>
                        </div>
                        <button @click="selectedAthlete = null" class="p-4 rounded-full bg-white/5 hover:bg-destructive/10 hover:text-destructive transition-all">
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="p-10 space-y-8">
                        <div v-if="Object.keys(licenseForm.errors).length > 0" class="p-4 rounded-2xl bg-destructive/10 border border-destructive/20 mb-6">
                            <div class="flex items-center gap-3 text-destructive mb-2">
                                <AlertCircle class="h-5 w-5" />
                                <span class="text-xs font-black uppercase">Terjadi Kesalahan</span>
                            </div>
                            <ul class="list-disc list-inside space-y-1">
                                <li v-for="(error, key) in licenseForm.errors" :key="key" class="text-[10px] font-bold text-destructive/80 uppercase">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase opacity-60">UCI ID</Label>
                            <Input v-model="licenseForm.uci_id" placeholder="Masukkan UCI ID..." class="h-14 rounded-2xl border-border bg-muted/30 px-6 font-black" required />
                        </div>

                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase opacity-60">Tanggal Berlaku</Label>
                            <Input v-model="licenseForm.license_valid_until" type="date" class="h-14 rounded-2xl border-border bg-muted/30 px-6 font-black" required />
                        </div>

                        <div class="space-y-3">
                            <Label class="text-[10px] font-black uppercase opacity-60">Dokumen Lisensi (PDF/Image)</Label>
                            <div class="p-6 rounded-2xl border border-dashed border-border bg-muted/10 flex flex-col items-center gap-4 text-center">
                                <Upload class="h-8 w-8 text-muted-foreground opacity-40" />
                                <div class="space-y-1">
                                    <input type="file" @input="licenseForm.license_file = ($event.target as HTMLInputElement).files?.[0] || null" class="text-[10px] font-black" />
                                    <p class="text-[9px] text-muted-foreground font-medium">Unggah salinan sertifikat lisensi UCI.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="selectedAthlete = null" class="flex-1 py-5 rounded-2xl bg-muted/40 text-[10px] font-black uppercase hover:bg-muted/60">Batal</button>
                            <button type="button" @click="submitLicense" :disabled="licenseForm.processing" class="flex-[2] py-5 rounded-2xl bg-accent text-[10px] font-black text-white uppercase shadow-xl shadow-accent/30 hover:bg-accent/90">
                                {{ licenseForm.processing ? 'Menyimpan...' : 'Simpan Lisensi' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL: DOCUMENT PREVIEW -->
            <div v-if="previewUrl" class="fixed inset-0 z-[100] flex items-center justify-center bg-background/95 p-4 backdrop-blur-2xl" @click="closePreview">
                <div class="relative w-full max-w-6xl h-[90vh] bg-card rounded-[3rem] border border-border shadow-2xl flex flex-col overflow-hidden" @click.stop>
                    <div class="flex items-center justify-between p-6 border-b border-border bg-muted/20">
                        <h3 class="text-sm font-black uppercase tracking-widest text-muted-foreground">Pratinjau Dokumen</h3>
                        <button @click="closePreview" class="px-6 py-2 rounded-full bg-destructive/10 text-destructive text-xs font-black uppercase hover:bg-destructive hover:text-white transition-all">Tutup</button>
                    </div>
                    <div class="flex-1 bg-muted/30 p-8 flex items-center justify-center overflow-hidden">
                        <iframe v-if="previewUrl.toLowerCase().endsWith('.pdf')" :src="previewUrl" class="w-full h-full rounded-2xl border border-border shadow-xl bg-white"></iframe>
                        <img v-else :src="previewUrl" class="max-w-full max-h-full object-contain rounded-2xl border border-border shadow-xl bg-white" />
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
