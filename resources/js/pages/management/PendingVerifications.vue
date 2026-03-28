<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: { name: string };
    created_at: string;
}

interface Coach {
    id: number;
    name: string;
}

const props = defineProps<{
    pendingUsers: User[];
    coaches: Coach[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Verifikasi User', href: '/management/pending' },
];

const selectedUser = ref<User | null>(null);
const showModal = ref(false);

const form = useForm({
    coach_id: '',
});

const openVerifyModal = (user: User) => {
    selectedUser.value = user;
    form.coach_id = props.coaches[0]?.id.toString() || '';
    showModal.value = true;
};

const verifyUser = () => {
    if (selectedUser.value) {
        form.post(`/management/users/${selectedUser.value.id}/verify`, {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Verifikasi Pending" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-10 p-6 md:p-10 max-w-7xl mx-auto w-full min-h-screen bg-background text-foreground">
            
            <div class="flex flex-col md:flex-row items-baseline justify-between gap-4 border-b border-border pb-6">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">Verifikasi User Baru</h1>
                    <p class="mt-2 text-muted-foreground font-medium">Setujui akun atlet baru dan tentukan pelatihnya.</p>
                </div>
            </div>

            <div v-if="pendingUsers.length === 0" class="flex flex-col items-center justify-center p-20 bg-card border border-dashed border-border rounded-2xl">
                <div class="p-4 rounded-full bg-muted mb-4">
                    <svg class="w-12 h-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-foreground italic">Semua Beres!</h2>
                <p class="text-muted-foreground mt-2">Tidak ada pendaftaran baru yang menunggu verifikasi.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="user in pendingUsers" :key="user.id" class="bg-card border border-border rounded-xl p-6 shadow-lg hover:shadow-accent/5 transition-all group">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center text-accent font-black text-xs border border-accent/20">
                            {{ user.name.split(' ').map(n => n[0]).slice(0, 2).join('') }}
                        </div>
                        <span class="px-2.5 py-1 rounded text-[10px] bg-muted text-muted-foreground font-black uppercase tracking-tighter">
                            Awaiting verification
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-black text-foreground uppercase tracking-tight truncate">{{ user.name }}</h3>
                    <p class="text-xs text-muted-foreground font-semibold mb-4">{{ user.email }}</p>
                    
                    <div class="flex items-center gap-2 mb-6 text-[10px] text-muted-foreground font-black uppercase tracking-widest">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Terdaftar: {{ formatDate(user.created_at) }}
                    </div>

                    <button 
                        @click="openVerifyModal(user)"
                        class="w-full bg-accent hover:bg-accent/90 text-white font-black uppercase tracking-widest text-[10px] py-3 rounded-lg shadow-lg shadow-accent/20 transition-transform active:scale-95 flex items-center justify-center gap-2"
                    >
                        Verifikasi & Assign Coach
                    </button>
                </div>
            </div>

            <!-- Verification Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
                <div class="bg-card w-full max-w-md rounded-2xl border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                    <div class="p-6 border-b border-border flex justify-between items-center bg-muted/30">
                        <h2 class="text-xl font-black text-foreground uppercase">Verifikasi Atlet</h2>
                        <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <div class="mb-6 p-4 rounded-xl bg-accent/5 border border-accent/10">
                            <p class="text-[10px] font-black uppercase tracking-widest text-accent mb-1">Mendaftarkan Sebagai</p>
                            <p class="text-lg font-bold text-foreground">{{ selectedUser?.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ selectedUser?.email }}</p>
                        </div>

                        <form @submit.prevent="verifyUser" class="flex flex-col gap-5">
                            <div class="flex flex-col gap-2 text-foreground">
                                <Label for="coach" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Pilih Pelatih (Coach)</Label>
                                <select id="coach" v-model="form.coach_id" class="w-full rounded-xl border border-muted bg-background px-3 py-3 text-sm focus:ring-accent focus:outline-none text-foreground font-bold appearance-none">
                                    <option v-for="coach in coaches" :key="coach.id" :value="coach.id">{{ coach.name }}</option>
                                </select>
                                <p class="text-[10px] text-muted-foreground italic font-semibold mt-1">Atlet ini akan secara otomatis dilatih oleh pelatih yang dipilih.</p>
                                <p v-if="form.errors.coach_id" class="text-destructive text-[10px] font-bold">{{ form.errors.coach_id }}</p>
                            </div>

                            <div class="flex justify-end gap-3 mt-4">
                                <button 
                                    type="button"
                                    @click="showModal = false"
                                    class="px-6 py-2.5 rounded-xl border border-border text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:bg-muted/50 transition-all font-inter"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2.5 rounded-xl bg-accent text-white text-[10px] font-black uppercase tracking-widest hover:bg-accent/90 transition-all font-inter shadow-lg shadow-accent/20"
                                >
                                    Setujui Pendaftaran
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
