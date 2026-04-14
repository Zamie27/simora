<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, Trophy, Tag } from 'lucide-vue-next';
import { ref } from 'vue';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface User {
    id: number;
    name: string;
}

interface EventType {
    id: number;
    name: string;
    coach_id: number | null;
    coach?: User;
}

interface EventPoint {
    id: number;
    name: string;
    coach_id: number | null;
    coach?: User;
}

defineProps<{
    eventTypes: EventType[];
    eventPoints: EventPoint[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pengaturan Event', href: '/management/event-settings' },
];

const showTypeModal = ref(false);
const editingType = ref<EventType | null>(null);
const typeForm = useForm({
    name: '',
});

const showPointModal = ref(false);
const editingPoint = ref<EventPoint | null>(null);
const pointForm = useForm({
    name: '',
});

// Type Actions
const openTypeCreate = () => {
    editingType.value = null;
    typeForm.reset();
    showTypeModal.value = true;
};

const openTypeEdit = (type: EventType) => {
    editingType.value = type;
    typeForm.name = type.name;
    showTypeModal.value = true;
};

const submitType = () => {
    if (editingType.value) {
        typeForm.patch(`/management/event-types/${editingType.value.id}`, {
            onSuccess: () => closeTypeModal(),
        });
    } else {
        typeForm.post('/management/event-types', {
            onSuccess: () => closeTypeModal(),
        });
    }
};

const deleteType = (type: EventType) => {
    if (confirm(`Apakah Anda yakin ingin menghapus jenis event "${type.name}"?`)) {
        typeForm.delete(`/management/event-types/${type.id}`);
    }
};

const closeTypeModal = () => {
    showTypeModal.value = false;
    editingType.value = null;
    typeForm.reset();
};

// Point Actions
const openPointCreate = () => {
    editingPoint.value = null;
    pointForm.reset();
    showPointModal.value = true;
};

const openPointEdit = (point: EventPoint) => {
    editingPoint.value = point;
    pointForm.name = point.name;
    showPointModal.value = true;
};

const submitPoint = () => {
    if (editingPoint.value) {
        pointForm.patch(`/management/event-points/${editingPoint.value.id}`, {
            onSuccess: () => closePointModal(),
        });
    } else {
        pointForm.post('/management/event-points', {
            onSuccess: () => closePointModal(),
        });
    }
};

const deletePoint = (point: EventPoint) => {
    if (confirm(`Apakah Anda yakin ingin menghapus poin kejuaraan "${point.name}"?`)) {
        pointForm.delete(`/management/event-points/${point.id}`);
    }
};

const closePointModal = () => {
    showPointModal.value = false;
    editingPoint.value = null;
    pointForm.reset();
};
</script>

<template>
    <Head title="Pengaturan Event" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 bg-background p-6 text-foreground md:p-10">
            <div class="flex flex-col items-baseline justify-between gap-4 border-b border-border pb-6 md:flex-row">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">
                        Pengaturan Event
                    </h1>
                    <p class="mt-2 text-[10px] font-medium tracking-widest text-muted-foreground uppercase italic opacity-80">
                        Kelola jenis event dan kategori poin kejuaraan secara global.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                <!-- Event Types Section -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Tag class="h-6 w-6 text-accent" />
                            <h2 class="text-xl font-black tracking-tight text-foreground uppercase">Jenis Event</h2>
                        </div>
                        <button
                            @click="openTypeCreate"
                            class="flex items-center gap-2 rounded-xl bg-accent px-4 py-2 text-[10px] font-black tracking-widest text-white uppercase shadow-lg shadow-accent/20 transition-all hover:bg-accent/90"
                        >
                            <Plus class="h-3 w-3" /> Tambah Jenis
                        </button>
                    </div>

                    <div class="grid gap-4">
                        <div
                            v-for="type in eventTypes"
                            :key="type.id"
                            class="group flex items-center justify-between rounded-3xl border border-border bg-card p-6 transition-all hover:border-accent"
                        >
                            <div>
                                <h3 class="font-black text-foreground uppercase">{{ type.name }}</h3>
                                <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest opacity-60">
                                    {{ type.coach_id ? `Oleh Pelatih: ${type.coach?.name}` : 'SISTEM / GLOBAL' }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="openTypeEdit(type)"
                                    class="rounded-xl bg-muted p-2 text-muted-foreground transition-all hover:bg-accent/10 hover:text-accent"
                                >
                                    <Pencil class="h-4 w-4" />
                                </button>
                                <button
                                    @click="deleteType(type)"
                                    class="rounded-xl bg-destructive/10 p-2 text-destructive transition-all hover:bg-destructive hover:text-white"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div v-if="eventTypes.length === 0" class="rounded-3xl border border-dashed border-border p-12 text-center">
                            <p class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40 italic">Belum ada jenis event.</p>
                        </div>
                    </div>
                </div>

                <!-- Event Points Section -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Trophy class="h-6 w-6 text-accent" />
                            <h2 class="text-xl font-black tracking-tight text-foreground uppercase">Poin Kejuaraan</h2>
                        </div>
                        <button
                            @click="openPointCreate"
                            class="flex items-center gap-2 rounded-xl bg-accent px-4 py-2 text-[10px] font-black tracking-widest text-white uppercase shadow-lg shadow-accent/20 transition-all hover:bg-accent/90"
                        >
                            <Plus class="h-3 w-3" /> Tambah Kategori
                        </button>
                    </div>

                    <div class="grid gap-4">
                        <div
                            v-for="point in eventPoints"
                            :key="point.id"
                            class="group flex items-center justify-between rounded-3xl border border-border bg-card p-6 transition-all hover:border-accent"
                        >
                            <div>
                                <h3 class="font-black text-foreground uppercase">{{ point.name }}</h3>
                                <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest opacity-60">
                                    {{ point.coach_id ? `Oleh Pelatih: ${point.coach?.name}` : 'SISTEM / GLOBAL' }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="openPointEdit(point)"
                                    class="rounded-xl bg-muted p-2 text-muted-foreground transition-all hover:bg-accent/10 hover:text-accent"
                                >
                                    <Pencil class="h-4 w-4" />
                                </button>
                                <button
                                    @click="deletePoint(point)"
                                    class="rounded-xl bg-destructive/10 p-2 text-destructive transition-all hover:bg-destructive hover:text-white"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div v-if="eventPoints.length === 0" class="rounded-3xl border border-dashed border-border p-12 text-center">
                            <p class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40 italic">Belum ada kategori poin.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Type Modal -->
            <div v-if="showTypeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-xl">
                <div class="w-full max-w-lg animate-in overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-2xl duration-300 fade-in zoom-in">
                    <div class="flex items-center justify-between border-b border-border bg-muted/20 p-8">
                        <div>
                            <h2 class="text-2xl font-black tracking-tight text-foreground uppercase">
                                {{ editingType ? 'Edit Jenis Event' : 'Tambah Jenis Event' }}
                            </h2>
                            <p class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-60 mt-1">Global Configuration</p>
                        </div>
                        <button @click="closeTypeModal" class="rounded-full p-2 text-muted-foreground hover:bg-muted/50">
                            <Plus class="h-6 w-6 rotate-45" />
                        </button>
                    </div>
                    <form @submit.prevent="submitType" class="p-8 space-y-6">
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black tracking-widest text-muted-foreground uppercase">Nama Jenis Event</Label>
                            <Input v-model="typeForm.name" class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black" placeholder="Contoh: Kejurnas / Latihan Bersama" />
                            <p v-if="typeForm.errors.name" class="text-[10px] font-bold text-destructive">{{ typeForm.errors.name }}</p>
                        </div>
                        <button type="submit" :disabled="typeForm.processing" class="w-full rounded-2xl bg-accent py-4 text-[10px] font-black tracking-widest text-white uppercase">
                            {{ editingType ? 'Perbarui' : 'Simpan' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Point Modal -->
            <div v-if="showPointModal" class="fixed inset-0 z-50 flex items-center justify-center bg-background/90 p-4 backdrop-blur-xl">
                <div class="w-full max-w-lg animate-in overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-2xl duration-300 fade-in zoom-in">
                    <div class="flex items-center justify-between border-b border-border bg-muted/20 p-8">
                        <div>
                            <h2 class="text-2xl font-black tracking-tight text-foreground uppercase">
                                {{ editingPoint ? 'Edit Poin Kejuaraan' : 'Tambah Poin Kejuaraan' }}
                            </h2>
                            <p class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-60 mt-1">Global Configuration</p>
                        </div>
                        <button @click="closePointModal" class="rounded-full p-2 text-muted-foreground hover:bg-muted/50">
                            <Plus class="h-6 w-6 rotate-45" />
                        </button>
                    </div>
                    <form @submit.prevent="submitPoint" class="p-8 space-y-6">
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black tracking-widest text-muted-foreground uppercase">Nama Kategori Poin</Label>
                            <Input v-model="pointForm.name" class="h-14 rounded-2xl border-none bg-muted/30 px-6 font-black" placeholder="Contoh: Juara 1 / Podium / Finisher" />
                            <p v-if="pointForm.errors.name" class="text-[10px] font-bold text-destructive">{{ pointForm.errors.name }}</p>
                        </div>
                        <button type="submit" :disabled="pointForm.processing" class="w-full rounded-2xl bg-accent py-4 text-[10px] font-black tracking-widest text-white uppercase">
                            {{ editingPoint ? 'Perbarui' : 'Simpan' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
