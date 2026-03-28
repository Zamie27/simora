<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    role_id: number;
    role: Role;
}

const props = defineProps<{
    users: User[];
    roles: Role[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Managemen User', href: '/management/users' },
];

const isEditing = ref(false);
const editingUserId = ref<number | null>(null);
const showModal = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role_id: props.roles[0]?.id || 0,
});

const openCreateModal = () => {
    isEditing.value = false;
    editingUserId.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (user: User) => {
    isEditing.value = true;
    editingUserId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.role_id = user.role_id;
    form.password = '';
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value && editingUserId.value) {
        form.patch(route('management.users.update', editingUserId.value), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('management.users.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteUser = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        router.delete(route('management.users.destroy', id));
    }
};

// Helper for route name (for TS)
const route = (name: string, params?: any) => {
    if (name === 'management.users.store') {
        return '/management/users';
    }

    if (name === 'management.users.update') {
        return `/management/users/${params}`;
    }

    if (name === 'management.users.destroy') {
        return `/management/users/${params}`;
    }

    return '';
};
</script>

<template>
    <Head title="Managemen User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-10 p-6 md:p-10 max-w-7xl mx-auto w-full min-h-screen bg-background text-foreground">
            
            <div class="flex flex-col md:flex-row items-baseline justify-between gap-4 border-b border-border pb-6">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">Managemen User</h1>
                    <p class="mt-2 text-muted-foreground font-medium">Kelola akses atlet, pelatih, dan tim manajemen.</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="bg-accent hover:bg-accent/90 text-accent-foreground px-6 py-3 rounded-xl shadow-lg shadow-accent/20 font-black uppercase tracking-widest text-xs transition-transform active:scale-95 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah User Baru
                </button>
            </div>

            <!-- Users Table Section -->
            <div class="bg-card border border-border rounded-xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-muted/50 border-b border-border">
                            <tr class="text-[10px] uppercase font-black tracking-widest text-muted-foreground">
                                <th class="px-6 py-4">Nama & Email</th>
                                <th class="px-6 py-4">Peran</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-muted/20 transition-colors group">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-secondary flex items-center justify-center text-secondary-foreground font-black text-xs border border-secondary-foreground/10 shadow-inner">
                                            {{ user.name.split(' ').map(n => n[0]).join('') }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm text-foreground">{{ user.name }}</p>
                                            <p class="text-[11px] text-muted-foreground font-semibold">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-xs">
                                    <span class="px-2.5 py-1 rounded-md bg-accent/10 text-accent font-black uppercase tracking-tighter border border-accent/20">
                                        {{ user.role.name }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button 
                                            @click="openEditModal(user)"
                                            class="p-2 rounded-lg bg-muted text-muted-foreground hover:bg-accent hover:text-white transition-all border border-border"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button 
                                            @click="deleteUser(user.id)"
                                            class="p-2 rounded-lg bg-muted text-muted-foreground hover:bg-destructive hover:text-white transition-all border border-border"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Simple Modal (Custom Implementation) -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm">
                <div class="bg-card w-full max-w-md rounded-2xl border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                    <div class="p-6 border-b border-border flex justify-between items-center bg-muted/30">
                        <h2 class="text-xl font-black text-foreground uppercase">{{ isEditing ? 'Edit User' : 'Tambah User' }}</h2>
                        <button @click="showModal = false" class="text-muted-foreground hover:text-foreground">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submit" class="p-6 flex flex-col gap-5">
                        <div class="flex flex-col gap-2">
                            <Label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Nama Lengkap</Label>
                            <Input id="name" v-model="form.name" placeholder="Indra Wijaya" required class="rounded-xl border-muted bg-background focus:ring-accent" />
                            <p v-if="form.errors.name" class="text-destructive text-[10px] font-bold">{{ form.errors.name }}</p>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <Label for="email" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Email</Label>
                            <Input id="email" type="email" v-model="form.email" placeholder="indra@simora.fit" required class="rounded-xl border-muted bg-background focus:ring-accent" />
                            <p v-if="form.errors.email" class="text-destructive text-[10px] font-bold">{{ form.errors.email }}</p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="role" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Peran</Label>
                            <select id="role" v-model="form.role_id" class="w-full rounded-xl border border-muted bg-background px-3 py-2 text-sm focus:ring-accent focus:outline-none text-foreground font-medium">
                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                            </select>
                            <p v-if="form.errors.role_id" class="text-destructive text-[10px] font-bold">{{ form.errors.role_id }}</p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="password" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                                {{ isEditing ? 'Password Baru (Kosongkan jika tidak ubah)' : 'Password' }}
                            </Label>
                            <Input id="password" type="password" v-model="form.password" :required="!isEditing" placeholder="••••••••" class="rounded-xl border-muted bg-background focus:ring-accent" />
                            <p v-if="form.errors.password" class="text-destructive text-[10px] font-bold">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex justify-end gap-3 mt-4">
                            <button 
                                type="button"
                                @click="showModal = false"
                                class="px-5 py-2.5 rounded-xl border border-border text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:bg-muted/50 transition-all"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 rounded-xl bg-accent text-accent-foreground text-[10px] font-black uppercase tracking-widest hover:bg-accent/90 transition-all"
                            >
                                {{ isEditing ? 'Simpan Perubahan' : 'Proses Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
