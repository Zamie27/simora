<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance, updateAppearance } = useAppearance();
</script>

<template>
    <div
        class="inline-flex rounded-full border border-black/10 bg-black/5 p-1 dark:border-white/10 dark:bg-white/5"
    >
        <!-- Light Button -->
        <button
            @click="updateAppearance('light')"
            :class="[
                'flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-bold transition-all duration-150',
                resolvedAppearance === 'light'
                    ? 'scale-100 bg-accent text-white shadow-md'
                    : 'scale-95 text-foreground/60 opacity-80 hover:bg-black/5 hover:text-foreground dark:hover:bg-white/5',
            ]"
        >
            <Sun class="h-4 w-4" />
            Light
        </button>

        <!-- Dark Button -->
        <button
            @click="updateAppearance('dark')"
            :class="[
                'flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-bold transition-all duration-150',
                resolvedAppearance === 'dark'
                    ? 'scale-100 bg-accent text-white shadow-md'
                    : 'scale-95 text-foreground/60 opacity-80 hover:bg-black/5 hover:text-foreground dark:hover:bg-white/5',
            ]"
        >
            <Moon class="h-4 w-4" />
            Dark
        </button>
    </div>
</template>
