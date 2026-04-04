<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance, updateAppearance } = useAppearance();
</script>

<template>
    <div
        class="inline-flex rounded-full bg-black/5 p-1 border border-black/10 dark:bg-white/5 dark:border-white/10"
    >
        <!-- Light Button -->
        <button
            @click="updateAppearance('light')"
            :class="[
                'flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-bold transition-all duration-150',
                resolvedAppearance === 'light'
                    ? 'bg-accent text-white shadow-md scale-100'
                    : 'text-foreground/60 scale-95 opacity-80 hover:bg-black/5 hover:text-foreground dark:hover:bg-white/5'
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
                    ? 'bg-accent text-white shadow-md scale-100'
                    : 'text-foreground/60 scale-95 opacity-80 hover:bg-black/5 hover:text-foreground dark:hover:bg-white/5'
            ]"
        >
            <Moon class="h-4 w-4" />
            Dark
        </button>
    </div>
</template>
