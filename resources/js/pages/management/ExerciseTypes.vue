<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Activity, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface ExerciseType {
    id: number;
    name: string;
    description: string;
}

defineProps<{
    exerciseTypes: ExerciseType[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Jenis Latihan', href: '/management/exercise-types' },
];

const showModal = ref(false);
const editingType = ref<ExerciseType | null>(null);

const form = useForm({
    name: '',
    description: '',
});

const openCreate = () => {
    editingType.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (type: ExerciseType) => {
    editingType.value = type;
    form.name = type.name;
    form.description = type.description || '';
    showModal.value = true;
};

const submit = () => {
    if (editingType.value) {
        form.put(`/management/exercise-types/${editingType.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/management/exercise-types', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteType = (type: ExerciseType) => {
    if (
        confirm(
            `Apakah Anda yakin ingin menghapus jenis latihan "${type.name}"?`,
        )
    ) {
        form.delete(`/management/exercise-types/${type.id}`);
    }
};

const closeModal = () => {
    showModal.value = false;
    editingType.value = null;
    form.reset();
};
</script>

<template>
    <Head title="Managemen Jenis Latihan" />

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
                        Jenis Latihan
                    </h1>
                    <p
                        class="mt-2 text-[10px] font-medium tracking-widest text-muted-foreground uppercase italic opacity-80"
                    >
                        Kelola jenis aktivitas latihan seperti Endurance, Interval, Hill Climb, dll.
                    </p>
                </div>
                <button
                    @click="openCreate"
                    class="flex items-center gap-2 rounded-xl bg-accent px-8 py-4 text-xs font-black tracking-widest text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90"
                >
                    <Plus class="h-4 w-4" /> Tambah Jenis Latihan
                </button>
            </div>

            <div
                v-if="exerciseTypes.length === 0"
                class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-border bg-muted/10 p-20"
            >
                <Activity
                    class="mb-4 h-12 w-12 text-muted-foreground opacity-20"
                />
                <p
                    class="text-xs font-black tracking-widest text-muted-foreground uppercase italic"
                >
                    Belum ada jenis latihan yang ditambahkan.
                </p>
            </div>

            <div
                v-else
                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="type in exerciseTypes"
                    :key="type.id"
                    class="group flex flex-col justify-between rounded-3xl border border-border bg-card p-10 shadow-xl shadow-black/5 transition-all hover:border-accent"
                >
                    <div>
                        <div
                            class="mb-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-secondary text-accent"
                        >
                            <Activity class="h-6 w-6" />
                        </div>
                        <h3
                            class="mb-2 text-lg font-black tracking-tight text-foreground uppercase"
                        >
                            {{ type.name }}
                        </h3>
                        <p
                            class="text-xs leading-relaxed font-medium text-muted-foreground opacity-70"
                        >
                            {{ type.description || 'Tidak ada deskripsi.' }}
                        </p>
                    </div>

                    <div class="mt-8 flex gap-2 border-t border-border pt-6">
                        <button
                            @click="openEdit(type)"
                            class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-muted py-3 text-[10px] font-black tracking-widest text-foreground uppercase transition-all hover:bg-muted/80"
                        >
                            <Pencil class="h-3 w-3" /> Edit
                        </button>
                        <button
                            @click="deleteType(type)"
                            class="flex items-center justify-center rounded-xl bg-destructive/10 px-4 text-destructive transition-all hover:bg-destructive hover:text-white"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-xl"
            >
                <div
                    class="w-full max-w-lg animate-in overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-2xl duration-300 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b border-border bg-muted/20 p-10"
                    >
                        <div>
                            <h2
                                class="text-2xl font-black tracking-tight text-foreground uppercase"
                            >
                                {{
                                    editingType
                                        ? 'Edit Jenis Latihan'
                                        : 'Tambah Jenis Latihan'
                                }}
                            </h2>
                            <p
                                class="mt-1 text-xs font-bold tracking-widest text-muted-foreground uppercase opacity-60"
                            >
                                Exercise Configuration
                            </p>
                        </div>
                        <button
                            @click="closeModal"
                            class="rounded-full p-3 text-muted-foreground transition-all hover:bg-muted/50 hover:text-foreground"
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

                    <div class="p-10">
                        <form
                            @submit.prevent="submit"
                            class="flex flex-col gap-8"
                        >
                            <div class="flex flex-col gap-2">
                                <Label
                                    for="name"
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                    >Nama Jenis Latihan</Label
                                >
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    class="h-14 rounded-2xl border-none bg-muted/30 px-6 text-lg font-black focus:ring-2 focus:ring-accent"
                                    placeholder="Contoh: Endurance, Interval, Recovery"
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
                                    for="description"
                                    class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-60"
                                    >Deskripsi (Opsional)</Label
                                >
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="min-h-[120px] w-full rounded-2xl border-none bg-muted/30 p-6 text-sm font-medium focus:ring-2 focus:ring-accent"
                                    placeholder="Penjelasan singkat tentang jenis latihan ini..."
                                ></textarea>
                                <p
                                    v-if="form.errors.description"
                                    class="text-[10px] font-bold text-destructive"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-2xl bg-accent py-5 text-[10px] font-black tracking-[0.2em] text-white uppercase shadow-xl shadow-accent/20 transition-all hover:bg-accent/90 active:scale-[0.98] disabled:opacity-50"
                            >
                                {{
                                    editingType
                                        ? 'Perbarui'
                                        : 'Simpan'
                                }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
