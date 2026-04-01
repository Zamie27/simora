<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Bug, Send, X, Camera, Loader2, AlertCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const isOpen = ref(false);

const form = useForm({
    title: '',
    description: '',
    reporter_name: '',
    reporter_contact: '',
    image: null as File | null,
});

const submit = () => {
    form.post(route('bug-reports.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
        },
    });
};

const handleImage = (e: Event) => {
    const target = e.target as HTMLInputElement;

    if (target.files?.length) {
        form.image = target.files[0];
    }
};
</script>

<template>
    <!-- Floating Bubble -->
    <div class="fixed right-6 bottom-6 z-[100]">
        <button
            @click="isOpen = !isOpen"
            class="flex h-14 w-14 items-center justify-center rounded-full bg-accent text-white shadow-2xl shadow-accent/40 transition-all hover:scale-110 active:scale-95"
            :class="{ 'rotate-90 bg-slate-800': isOpen }"
        >
            <X v-if="isOpen" class="h-6 w-6" />
            <Bug v-else class="h-7 w-7 animate-pulse" />
        </button>

        <!-- Form Overlay -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-10 scale-95 opacity-0"
            enter-to-class="translate-y-0 scale-100 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 scale-100 opacity-100"
            leave-to-class="translate-y-10 scale-95 opacity-0"
        >
            <div
                v-if="isOpen"
                class="bg-surface absolute right-0 bottom-20 w-[350px] overflow-hidden rounded-[2rem] border border-white/10 p-6 shadow-2xl backdrop-blur-xl md:w-[400px]"
            >
                <div class="mb-6 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/20"
                    >
                        <Bug class="h-5 w-5 text-accent" />
                    </div>
                    <div>
                        <h3
                            class="text-lg font-black tracking-tight text-white uppercase"
                        >
                            Lapor Bug
                        </h3>
                        <p
                            class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase"
                        >
                            Bantu kami memperbaiki SIMORA
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1.5">
                        <label
                            class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                            >Judul Bug</label
                        >
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="Contoh: Grafik tidak muncul"
                            class="w-full rounded-xl border-white/5 bg-white/5 px-4 py-3 text-sm text-white focus:border-accent focus:ring-accent"
                            required
                        />
                        <p
                            v-if="form.errors.title"
                            class="text-[10px] font-bold text-red-500"
                        >
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                            >Keterangan Bug</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Jelaskan apa yang terjadi..."
                            class="w-full rounded-xl border-white/5 bg-white/5 px-4 py-3 text-sm text-white focus:border-accent focus:ring-accent"
                            required
                        ></textarea>
                        <p
                            v-if="form.errors.description"
                            class="text-[10px] font-bold text-red-500"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label
                                class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                >Nama Anda</label
                            >
                            <input
                                v-model="form.reporter_name"
                                type="text"
                                placeholder="Nama"
                                class="w-full rounded-xl border-white/5 bg-white/5 px-4 py-3 text-sm text-white focus:border-accent focus:ring-accent"
                                required
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                                >Email/No. HP</label
                            >
                            <input
                                v-model="form.reporter_contact"
                                type="text"
                                placeholder="Untuk hadiah"
                                class="w-full rounded-xl border-white/5 bg-white/5 px-4 py-3 text-sm text-white focus:border-accent focus:ring-accent"
                                required
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            class="text-[9px] font-black tracking-widest text-muted-foreground uppercase"
                            >Screenshot Bug (Opsional)</label
                        >
                        <div class="relative">
                            <input
                                type="file"
                                @change="handleImage"
                                accept="image/*"
                                class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                            />
                            <div
                                class="flex items-center gap-3 rounded-xl border border-dashed border-white/10 bg-white/5 px-4 py-3 text-xs font-bold text-muted-foreground"
                            >
                                <Camera class="h-4 w-4 text-accent" />
                                <span
                                    v-if="form.image"
                                    class="truncate text-white"
                                    >{{ form.image.name }}</span
                                >
                                <span v-else>Klik untuk upload gambar</span>
                            </div>
                        </div>
                        <p
                            v-if="form.errors.image"
                            class="text-[10px] font-bold text-red-500"
                        >
                            {{ form.errors.image }}
                        </p>
                    </div>

                    <div class="pt-2">
                        <div
                            v-if="form.recentlySuccessful"
                            class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-500/10 p-3 text-xs font-bold text-emerald-500"
                        >
                            <AlertCircle class="h-4 w-4" />
                            Laporan terkirim! Terima kasih.
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-accent py-4 text-sm font-black tracking-widest text-white uppercase transition-all hover:bg-accent/90 active:scale-95 disabled:opacity-50"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            <Send v-else class="h-4 w-4" />
                            Kirim Laporan
                        </button>
                    </div>
                </form>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.bg-surface {
    background-color: #0f1414;
}
.text-accent {
    color: #ff6120;
}
.bg-accent {
    background-color: #ff6120;
}
</style>
