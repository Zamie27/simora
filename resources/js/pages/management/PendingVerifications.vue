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
                        Verifikasi User Baru
                    </h1>
                    <p class="mt-2 font-medium text-muted-foreground">
                        Setujui akun atlet baru dan tentukan pelatihnya.
                    </p>
                </div>
            </div>

            <div
                v-if="pendingUsers.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card p-20"
            >
                <div class="mb-4 rounded-full bg-muted p-4">
                    <svg
                        class="h-12 w-12 text-muted-foreground"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        ></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-foreground italic">
                    Semua Beres!
                </h2>
                <p class="mt-2 text-muted-foreground">
                    Tidak ada pendaftaran baru yang menunggu verifikasi.
                </p>
            </div>

            <div
                v-else
                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="user in pendingUsers"
                    :key="user.id"
                    class="group rounded-xl border border-border bg-card p-6 shadow-lg transition-all hover:shadow-accent/5"
                >
                    <div class="mb-6 flex items-start justify-between">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full border border-accent/20 bg-accent/10 text-xs font-black text-accent"
                        >
                            {{
                                user.name
                                    .split(' ')
                                    .map((n) => n[0])
                                    .slice(0, 2)
                                    .join('')
                            }}
                        </div>
                        <span
                            class="rounded bg-muted px-2.5 py-1 text-[10px] font-black tracking-tighter text-muted-foreground uppercase"
                        >
                            Awaiting verification
                        </span>
                    </div>

                    <h3
                        class="truncate text-lg font-black tracking-tight text-foreground uppercase"
                    >
                        {{ user.name }}
                    </h3>
                    <p class="mb-4 text-xs font-semibold text-muted-foreground">
                        {{ user.email }}
                    </p>

                    <div
                        class="mb-6 flex items-center gap-2 text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                    >
                        <svg
                            class="h-3 w-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            ></path>
                        </svg>
                        Terdaftar: {{ formatDate(user.created_at) }}
                    </div>

                    <button
                        @click="openVerifyModal(user)"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-accent py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-lg shadow-accent/20 transition-transform hover:bg-accent/90 active:scale-95"
                    >
                        Verifikasi & Assign Coach
                    </button>
                </div>
            </div>

            <!-- Verification Modal -->
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
                            Verifikasi Atlet
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

                    <div class="p-6">
                        <div
                            class="mb-6 rounded-xl border border-accent/10 bg-accent/5 p-4"
                        >
                            <p
                                class="mb-1 text-[10px] font-black tracking-widest text-accent uppercase"
                            >
                                Mendaftarkan Sebagai
                            </p>
                            <p class="text-lg font-bold text-foreground">
                                {{ selectedUser?.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ selectedUser?.email }}
                            </p>
                        </div>

                        <form
                            @submit.prevent="verifyUser"
                            class="flex flex-col gap-5"
                        >
                            <div class="flex flex-col gap-2 text-foreground">
                                <Label
                                    for="coach"
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                    >Pilih Pelatih (Coach)</Label
                                >
                                <select
                                    id="coach"
                                    v-model="form.coach_id"
                                    class="w-full appearance-none rounded-xl border border-muted bg-background px-3 py-3 text-sm font-bold text-foreground focus:ring-accent focus:outline-none"
                                >
                                    <option
                                        v-for="coach in coaches"
                                        :key="coach.id"
                                        :value="coach.id"
                                    >
                                        {{ coach.name }}
                                    </option>
                                </select>
                                <p
                                    class="mt-1 text-[10px] font-semibold text-muted-foreground italic"
                                >
                                    Atlet ini akan secara otomatis dilatih oleh
                                    pelatih yang dipilih.
                                </p>
                                <p
                                    v-if="form.errors.coach_id"
                                    class="text-[10px] font-bold text-destructive"
                                >
                                    {{ form.errors.coach_id }}
                                </p>
                            </div>

                            <div class="mt-4 flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="showModal = false"
                                    class="font-inter rounded-xl border border-border px-6 py-2.5 text-[10px] font-black tracking-widest text-muted-foreground uppercase transition-all hover:bg-muted/50"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="font-inter rounded-xl bg-accent px-6 py-2.5 text-[10px] font-black tracking-widest text-white uppercase shadow-lg shadow-accent/20 transition-all hover:bg-accent/90"
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
