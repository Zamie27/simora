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
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10"
        >
            <div
                class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row"
            >
                <div>
                    <h1
                        class="text-3xl font-black tracking-tight text-foreground uppercase"
                    >
                        Managemen User
                    </h1>
                    <p class="mt-2 font-medium text-muted-foreground">
                        Kelola akses atlet, pelatih, dan tim manajemen.
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    class="flex items-center gap-2 rounded-xl bg-accent px-6 py-3 text-xs font-black tracking-widest text-accent-foreground uppercase shadow-lg shadow-accent/20 transition-transform hover:bg-accent/90 active:scale-95"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        ></path>
                    </svg>
                    Tambah User Baru
                </button>
            </div>

            <!-- Users Table Section -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-xl"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-border bg-muted/50">
                            <tr
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                            >
                                <th class="px-6 py-4">Nama & Email</th>
                                <th class="px-6 py-4">Peran</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="user in users"
                                :key="user.id"
                                class="group transition-colors hover:bg-muted/20"
                            >
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full border border-secondary-foreground/10 bg-secondary text-xs font-black text-secondary-foreground shadow-inner"
                                        >
                                            {{
                                                user.name
                                                    .split(' ')
                                                    .map((n) => n[0])
                                                    .join('')
                                            }}
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-bold text-foreground"
                                            >
                                                {{ user.name }}
                                            </p>
                                            <p
                                                class="text-[11px] font-semibold text-muted-foreground"
                                            >
                                                {{ user.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-xs">
                                    <span
                                        class="rounded-md border border-accent/20 bg-accent/10 px-2.5 py-1 font-black tracking-tighter text-accent uppercase"
                                    >
                                        {{ user.role.name }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            @click="openEditModal(user)"
                                            class="rounded-lg border border-border bg-muted p-2 text-muted-foreground transition-all hover:bg-accent hover:text-white"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                                ></path>
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteUser(user.id)"
                                            class="rounded-lg border border-border bg-muted p-2 text-muted-foreground transition-all hover:bg-destructive hover:text-white"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                ></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Simple Modal (Custom Implementation) -->
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-md animate-in overflow-hidden rounded-2xl border border-border bg-card shadow-2xl duration-200 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-border bg-muted/30 p-6"
                    >
                        <h2
                            class="text-xl font-black text-foreground uppercase"
                        >
                            {{ isEditing ? 'Edit User' : 'Tambah User' }}
                        </h2>
                        <button
                            @click="showModal = false"
                            class="text-muted-foreground hover:text-foreground"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                ></path>
                            </svg>
                        </button>
                    </div>
                    <form
                        @submit.prevent="submit"
                        class="flex flex-col gap-5 p-6"
                    >
                        <div class="flex flex-col gap-2">
                            <Label
                                for="name"
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                >Nama Lengkap</Label
                            >
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Indra Wijaya"
                                required
                                class="rounded-xl border-muted bg-background focus:ring-accent"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-[10px] font-bold text-destructive"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label
                                for="email"
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                >Email</Label
                            >
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                placeholder="indra@simora.fit"
                                required
                                class="rounded-xl border-muted bg-background focus:ring-accent"
                            />
                            <p
                                v-if="form.errors.email"
                                class="text-[10px] font-bold text-destructive"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label
                                for="role"
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                >Peran</Label
                            >
                            <select
                                id="role"
                                v-model="form.role_id"
                                class="w-full rounded-xl border border-muted bg-background px-3 py-2 text-sm font-medium text-foreground focus:ring-accent focus:outline-none"
                            >
                                <option
                                    v-for="role in roles"
                                    :key="role.id"
                                    :value="role.id"
                                >
                                    {{ role.name }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.role_id"
                                class="text-[10px] font-bold text-destructive"
                            >
                                {{ form.errors.role_id }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label
                                for="password"
                                class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                            >
                                {{
                                    isEditing
                                        ? 'Password Baru (Kosongkan jika tidak ubah)'
                                        : 'Password'
                                }}
                            </Label>
                            <Input
                                id="password"
                                type="password"
                                v-model="form.password"
                                :required="!isEditing"
                                placeholder="••••••••"
                                class="rounded-xl border-muted bg-background focus:ring-accent"
                            />
                            <p
                                v-if="form.errors.password"
                                class="text-[10px] font-bold text-destructive"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div class="mt-4 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showModal = false"
                                class="rounded-xl border border-border px-5 py-2.5 text-[10px] font-black tracking-widest text-muted-foreground uppercase transition-all hover:bg-muted/50"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-accent px-5 py-2.5 text-[10px] font-black tracking-widest text-accent-foreground uppercase transition-all hover:bg-accent/90"
                            >
                                {{
                                    isEditing
                                        ? 'Simpan Perubahan'
                                        : 'Proses Simpan'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
