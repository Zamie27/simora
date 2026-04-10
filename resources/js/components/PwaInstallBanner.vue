<script setup lang="ts">
import { Download, X } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

const deferredPrompt = ref<any>(null);
const showBanner = ref(false);

const handleBeforeInstallPrompt = (e: Event) => {
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt.value = e;
    // Update UI notify the user they can install the PWA
    showBanner.value = true;
};

const installPwa = async () => {
    if (!deferredPrompt.value) {
        return;
    }
    
    // Show the install prompt
    deferredPrompt.value.prompt();
    
    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.value.userChoice;
    
    if (outcome === 'accepted') {
        showBanner.value = false;
    }
    
    // We've used the prompt, and can't use it again, throw it away
    deferredPrompt.value = null;
};

const dismissBanner = () => {
    showBanner.value = false;
};

onMounted(() => {
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
});

onUnmounted(() => {
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
});
</script>

<template>
    <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform -translate-y-4 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="showBanner" class="fixed bottom-4 left-4 right-4 z-50 md:bottom-auto md:top-4 md:left-1/2 md:right-auto md:-translate-x-1/2 md:w-96">
            <div class="flex items-center gap-4 rounded-2xl border border-border bg-card p-4 shadow-2xl">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent text-white">
                    <Download class="h-5 w-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-black tracking-wide text-foreground uppercase">
                        Install Aplikasi
                    </p>
                    <p class="text-[10px] font-medium text-muted-foreground uppercase opacity-80">
                        Tambahkan SIMORA ke perangkat Anda untuk akses lebih cepat
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        @click="installPwa"
                        class="rounded-lg bg-accent px-3 py-1.5 text-[10px] font-black text-white hover:bg-accent-foreground uppercase transition-colors"
                    >
                        Install
                    </button>
                    <button 
                        @click="dismissBanner"
                        class="rounded-lg p-1.5 text-muted-foreground hover:bg-muted"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>
