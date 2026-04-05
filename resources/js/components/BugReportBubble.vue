<script setup lang="ts">
import { Bug, Send, X, Camera, Loader2, CheckCircle2 } from 'lucide-vue-next';
import { ref, reactive } from 'vue';

const isOpen = ref(false);
const processing = ref(false);
const recentlySuccessful = ref(false);
const errors = reactive<Record<string, string>>({});

const form = reactive({
    title: '',
    description: '',
    reporter_name: '',
    reporter_contact: '',
    images: [] as File[],
});

const resetForm = () => {
    form.title = '';
    form.description = '';
    form.reporter_name = '';
    form.reporter_contact = '';
    form.images = [];
    Object.keys(errors).forEach((key) => delete errors[key]);
};

const submit = async () => {
    processing.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);

    const formData = new FormData();
    formData.append('title', form.title);
    formData.append('description', form.description);
    formData.append('reporter_name', form.reporter_name);
    formData.append('reporter_contact', form.reporter_contact);
    formData.append('url', window.location.href);

    form.images.forEach((image, index) => {
        formData.append(`images[${index}]`, image);
    });

    try {
        // Get CSRF token from the meta tag or cookie
        const csrfToken =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') ??
            document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1];

        const response = await fetch('/bug-reports', {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': csrfToken ? decodeURIComponent(csrfToken) : '',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: formData,
        });

        if (response.ok || response.status === 302) {
            recentlySuccessful.value = true;
            resetForm();
            setTimeout(() => {
                recentlySuccessful.value = false;
                isOpen.value = false;
            }, 3000);
        } else if (response.status === 422) {
            const data = await response.json();

            if (data.errors) {
                Object.assign(
                    errors,
                    Object.fromEntries(
                        Object.entries(data.errors).map(([key, val]) => [
                            key,
                            (val as string[])[0],
                        ]),
                    ),
                );
            }
        }
    } catch {
        // Silently handle network errors
    } finally {
        processing.value = false;
    }
};

const handleImage = (e: Event) => {
    const target = e.target as HTMLInputElement;

    if (target.files?.length) {
        const newFiles = Array.from(target.files);

        if (form.images.length + newFiles.length > 5) {
            errors.images = 'Maksimal 5 gambar diperbolehkan.';

            return;
        }

        form.images = [...form.images, ...newFiles];
        delete errors.images;
    }
};

const removeImage = (index: number) => {
    form.images.splice(index, 1);
};
</script>

<template>
    <!-- Floating Bubble -->
    <div
        class="fixed right-6 bottom-6 z-[9999]"
        style="position: fixed !important"
    >
        <button
            @click="isOpen = !isOpen"
            class="flex h-14 w-14 items-center justify-center rounded-full shadow-2xl transition-all hover:scale-110 active:scale-95"
            :style="
                isOpen
                    ? 'background-color: #1e293b; color: white; transform: rotate(90deg);'
                    : 'background-color: #ff6120; color: white; box-shadow: 0 10px 40px rgba(255,97,32,0.4);'
            "
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
                class="bug-report-panel absolute right-0 bottom-20 w-[350px] overflow-hidden rounded-[2rem] border border-white/10 p-6 shadow-2xl md:w-[400px]"
            >
                <div class="mb-6 flex items-center gap-3">
                    <div
                        style="background-color: rgba(255, 97, 32, 0.2)"
                        class="flex h-10 w-10 items-center justify-center rounded-xl"
                    >
                        <Bug class="h-5 w-5" style="color: #ff6120" />
                    </div>
                    <div>
                        <h3
                            style="color: #ffffff"
                            class="text-lg font-black tracking-tight uppercase"
                        >
                            Lapor Bug
                        </h3>
                        <p
                            style="color: #94a3b8"
                            class="text-[10px] font-bold tracking-widest uppercase"
                        >
                            Bantu kami memperbaiki SIMORA
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1.5">
                        <label
                            style="color: #94a3b8"
                            class="text-[9px] font-black tracking-widest uppercase"
                            >Judul Bug</label
                        >
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="Contoh: Grafik tidak muncul"
                            class="bug-report-input"
                            required
                        />
                        <p
                            v-if="errors.title"
                            class="text-[10px] font-bold text-red-500"
                        >
                            {{ errors.title }}
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            style="color: #94a3b8"
                            class="text-[9px] font-black tracking-widest uppercase"
                            >Keterangan Bug</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Jelaskan apa yang terjadi..."
                            class="bug-report-input"
                            required
                        ></textarea>
                        <p
                            v-if="errors.description"
                            class="text-[10px] font-bold text-red-500"
                        >
                            {{ errors.description }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label
                                style="color: #94a3b8"
                                class="text-[9px] font-black tracking-widest uppercase"
                                >Nama Anda</label
                            >
                            <input
                                v-model="form.reporter_name"
                                type="text"
                                placeholder="Nama"
                                class="bug-report-input"
                                required
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                style="color: #94a3b8"
                                class="text-[9px] font-black tracking-widest uppercase"
                                >Email/No. HP</label
                            >
                            <input
                                v-model="form.reporter_contact"
                                type="text"
                                placeholder="Untuk hadiah"
                                class="bug-report-input"
                                required
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            style="color: #94a3b8"
                            class="text-[9px] font-black tracking-widest uppercase"
                            >Screenshot Bug (Opsional, Max 5)</label
                        >
                        <div class="relative">
                            <input
                                type="file"
                                @change="handleImage"
                                accept="image/*"
                                multiple
                                class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                                :disabled="form.images.length >= 5"
                            />
                            <div
                                style="
                                    border-color: rgba(255, 255, 255, 0.1);
                                    background-color: rgba(255, 255, 255, 0.05);
                                    color: #94a3b8;
                                "
                                class="flex items-center gap-3 rounded-xl border border-dashed px-4 py-3 text-xs font-bold"
                            >
                                <Camera
                                    class="h-4 w-4"
                                    style="color: #ff6120"
                                />
                                <span>{{
                                    form.images.length >= 5
                                        ? 'Maksimal tercapai'
                                        : 'Klik untuk upload (bisa banyak)'
                                }}</span>
                            </div>
                        </div>

                        <!-- Image List View -->
                        <div
                            v-if="form.images.length > 0"
                            class="mt-2 flex flex-wrap gap-2"
                        >
                            <div
                                v-for="(img, idx) in form.images"
                                :key="idx"
                                class="flex items-center gap-2 rounded-lg bg-white/5 px-2 py-1 text-[10px]"
                            >
                                <span
                                    class="max-w-[100px] truncate text-white/70"
                                    >{{ img.name }}</span
                                >
                                <button
                                    type="button"
                                    @click="removeImage(idx)"
                                    class="text-red-400"
                                >
                                    <X class="h-3 w-3" />
                                </button>
                            </div>
                        </div>

                        <p
                            v-if="errors.images"
                            class="text-[10px] font-bold text-red-500"
                        >
                            {{ errors.images }}
                        </p>
                    </div>

                    <div class="pt-2">
                        <div
                            v-if="recentlySuccessful"
                            class="mb-4 flex items-center gap-2 rounded-xl p-3 text-xs font-bold"
                            style="
                                background-color: rgba(16, 185, 129, 0.1);
                                color: #10b981;
                            "
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            Laporan terkirim! Terima kasih.
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            style="background-color: #ff6120; color: white"
                            class="flex w-full items-center justify-center gap-2 rounded-xl py-4 text-sm font-black tracking-widest uppercase transition-all hover:opacity-90 active:scale-95 disabled:opacity-50"
                        >
                            <Loader2
                                v-if="processing"
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
.bug-report-panel {
    background-color: #0f1414 !important;
    color: #ffffff !important;
}

.bug-report-input {
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid rgba(255, 255, 255, 0.05);
    background-color: rgba(255, 255, 255, 0.05) !important;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: #ffffff !important;
    outline: none;
}

.bug-report-input:focus {
    border-color: #ff6120;
    box-shadow: 0 0 0 2px rgba(255, 97, 32, 0.2);
}

.bug-report-input::placeholder {
    color: #64748b;
}
</style>
