<script setup lang="ts">
import { computed } from 'vue';
import { Check, X } from 'lucide-vue-next';

const props = defineProps<{
    password: string | null | undefined;
}>();

const requirements = computed(() => {
    const p = props.password || '';
    return [
        {
            label: 'Minimal 6 karakter',
            met: p.length >= 6,
        },
        {
            label: 'Mengandung huruf kapital',
            met: /[A-Z]/.test(p),
        },
        {
            label: 'Mengandung angka',
            met: /[0-9]/.test(p),
        },
        {
            label: 'Mengandung simbol',
            met: /[^A-Za-z0-9]/.test(p),
        },
    ];
});
</script>

<template>
    <div class="mt-2 space-y-1.5">
        <div
            v-for="(req, index) in requirements"
            :key="index"
            class="flex items-center gap-2 text-xs transition-colors duration-200"
            :class="req.met ? 'text-green-600 dark:text-green-400' : 'text-muted-foreground'"
        >
            <div
                class="flex size-4 items-center justify-center rounded-full border transition-all duration-300"
                :class="
                    req.met
                        ? 'border-green-600 bg-green-600 text-white dark:border-green-400 dark:bg-green-400'
                        : 'border-muted-foreground/30'
                "
            >
                <Check v-if="req.met" class="size-2.5 stroke-[3]" />
                <div v-else class="size-1 rounded-full bg-muted-foreground/30" />
            </div>
            <span :class="{ 'font-medium': req.met }">{{ req.label }}</span>
        </div>
    </div>
</template>
