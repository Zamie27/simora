<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';

const confirm = useConfirm();
</script>

<template>
    <v-dialog v-model="confirm.isOpen.value" max-width="400" persistent>
        <v-card class="rounded-xl border border-border bg-card">
            <v-card-title class="pt-6 px-6 pb-2 text-xl font-black text-foreground">
                {{ confirm.title.value }}
            </v-card-title>
            <v-card-text class="px-6 pb-6 text-sm text-muted-foreground">
                {{ confirm.message.value }}
            </v-card-text>
            <v-card-actions class="px-6 pb-6 pt-0 flex justify-end gap-2">
                <button
                    @click="confirm.cancel"
                    class="rounded-lg border border-border px-4 py-2 text-xs font-bold text-muted-foreground transition-all hover:bg-muted"
                >
                    Batal
                </button>
                <button
                    @click="confirm.confirm"
                    class="rounded-lg bg-accent px-4 py-2 text-xs font-bold text-white transition-all hover:bg-accent/90 shadow-lg shadow-accent/20"
                >
                    Ya, Lanjutkan
                </button>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
