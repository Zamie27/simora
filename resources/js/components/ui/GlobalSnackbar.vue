<script setup lang="ts">
import { useSnackbar } from '@/composables/useSnackbar';
import { CheckCircle2, AlertCircle, Info, AlertTriangle, X } from 'lucide-vue-next';

const snackbar = useSnackbar();

const getIcon = (color: string) => {
    switch (color) {
        case 'success':
            return CheckCircle2;
        case 'error':
            return AlertCircle;
        case 'warning':
            return AlertTriangle;
        case 'info':
            return Info;
        default:
            return Info;
    }
};
</script>

<template>
    <v-snackbar
        v-model="snackbar.isOpen.value"
        :color="snackbar.color.value"
        :timeout="snackbar.timeout.value"
        location="top center"
        rounded="xl"
        elevation="24"
    >
        <div class="flex items-center gap-3">
            <component :is="getIcon(snackbar.color.value)" class="h-5 w-5" />
            <span class="font-bold text-sm">{{ snackbar.message.value }}</span>
        </div>
        <template v-slot:actions>
            <button
                @click="snackbar.isOpen.value = false"
                class="rounded-full p-1 transition-colors hover:bg-white/20 ml-2"
            >
                <X class="h-4 w-4" />
            </button>
        </template>
    </v-snackbar>
</template>
