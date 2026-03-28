<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { BookOpen, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface Category {
    id: number;
    name: string;
    description: string;
}

defineProps<{
    categories: Category[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Managemen Kategori', href: '/management/categories' },
];

const showModal = ref(false);
const editingCategory = ref<Category | null>(null);

const form = useForm({
    name: '',
    description: '',
});

const openCreate = () => {
    editingCategory.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (category: Category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.description = category.description || '';
    showModal.value = true;
};

const submit = () => {
    if (editingCategory.value) {
        form.put(`/management/categories/${editingCategory.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/management/categories', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCategory = (category: Category) => {
    if (confirm(`Apakah Anda yakin ingin menghapus kategori "${category.name}"?`)) {
        form.delete(`/management/categories/${category.id}`);
    }
};

const closeModal = () => {
    showModal.value = false;
    editingCategory.value = null;
    form.reset();
};
</script>

<template>
    <Head title="Managemen Kategori" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-10 p-6 md:p-10 max-w-7xl mx-auto w-full min-h-screen bg-background text-foreground">
            
            <div class="flex flex-col md:flex-row items-baseline justify-between gap-4 border-b border-border pb-6">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">Managemen Kategori</h1>
                    <p class="mt-2 text-muted-foreground font-medium italic opacity-80 uppercase tracking-widest text-[10px]">Atur kategori atlet agar seragam dan mudah dianalisis.</p>
                </div>
                <button 
                    @click="openCreate"
                    class="bg-accent hover:bg-accent/90 text-white font-black uppercase tracking-widest text-xs px-8 py-4 rounded-xl shadow-xl shadow-accent/20 transition-all flex items-center gap-2"
                >
                    <Plus class="w-4 h-4" /> Tambah Kategori
                </button>
            </div>

            <div v-if="categories.length === 0" class="flex flex-col items-center justify-center p-20 bg-muted/10 border border-dashed border-border rounded-3xl">
                 <BookOpen class="w-12 h-12 text-muted-foreground opacity-20 mb-4" />
                <p class="text-muted-foreground font-black uppercase tracking-widest text-xs italic">Belum ada kategori yang ditambahkan.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="category in categories" :key="category.id" class="bg-card border border-border p-8 rounded-3xl flex flex-col justify-between hover:border-accent group transition-all shadow-xl shadow-black/5">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-secondary flex items-center justify-center text-accent mb-6">
                            <BookOpen class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-black text-foreground uppercase tracking-tight mb-2">{{ category.name }}</h3>
                        <p class="text-xs text-muted-foreground font-medium leading-relaxed opacity-70">{{ category.description || 'Tidak ada deskripsi.' }}</p>
                    </div>

                    <div class="flex gap-2 mt-8 pt-6 border-t border-border">
                        <button @click="openEdit(category)" class="flex-1 flex items-center justify-center gap-2 py-3 bg-muted hover:bg-muted/80 text-foreground rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                            <Pencil class="w-3 h-3" /> Edit
                        </button>
                        <button @click="deleteCategory(category)" class="px-4 flex items-center justify-center bg-destructive/10 hover:bg-destructive text-destructive hover:text-white rounded-xl transition-all">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/90 backdrop-blur-xl">
                 <div class="bg-card w-full max-w-lg rounded-[2.5rem] border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
                    <div class="p-10 border-b border-border flex justify-between items-center bg-muted/20">
                         <div>
                            <h2 class="text-2xl font-black text-foreground uppercase tracking-tight">{{ editingCategory ? 'Edit Kategori' : 'Tambah Kategori Baru' }}</h2>
                            <p class="text-xs text-muted-foreground font-bold mt-1 uppercase tracking-widest opacity-60">System Configuration</p>
                         </div>
                        <button @click="closeModal" class="p-3 rounded-full hover:bg-muted/50 text-muted-foreground hover:text-foreground transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="p-10">
                        <form @submit.prevent="submit" class="flex flex-col gap-8">
                            <div class="flex flex-col gap-2">
                                <Label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-60">Nama Kategori</Label>
                                <Input id="name" v-model="form.name" class="h-14 bg-muted/30 border-none rounded-2xl px-6 text-lg font-black focus:ring-2 focus:ring-accent" placeholder="Contoh: ELITE / MTB / ROAD" />
                                <p v-if="form.errors.name" class="text-destructive text-[10px] font-bold">{{ form.errors.name }}</p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <Label for="description" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground opacity-60">Deskripsi (Opsional)</Label>
                                <textarea id="description" v-model="form.description" class="w-full bg-muted/30 border-none rounded-2xl p-6 text-sm font-medium focus:ring-2 focus:ring-accent min-h-[120px]" placeholder="Penjelasan singkat tentang kategori ini..."></textarea>
                                <p v-if="form.errors.description" class="text-destructive text-[10px] font-bold">{{ form.errors.description }}</p>
                            </div>

                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="py-5 bg-accent text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-accent/20 hover:bg-accent/90 transition-all active:scale-[0.98] disabled:opacity-50"
                            >
                                {{ editingCategory ? 'Perbarui Kategori' : 'Simpan Kategori' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
