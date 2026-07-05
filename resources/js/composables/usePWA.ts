import { ref, onMounted, onUnmounted } from 'vue';

export function usePWA() {
    const isInstallable = ref(false);
    const isStandalone = ref(false);

    const handleInstallPrompt = (e: any) => {
        e.preventDefault();
        (window as any)._deferredPrompt = e;
        isInstallable.value = true;
    };

    const handleAppInstalled = () => {
        isInstallable.value = false;
        (window as any)._deferredPrompt = null;
        isStandalone.value = true;
    };

    const installApp = async () => {
        const promptEvent = (window as any)._deferredPrompt;

        if (!promptEvent) {
            alert(
                'Fitur Install App saat ini belum tersedia. Pastikan:\n1. Menggunakan browser Chrome / Safari terbaru\n2. Mengakses lewat jalur aman (HTTPS)\n3. Aplikasi SIMORA belum pernah diinstal sebelumnya.',
            );

            return;
        }

        promptEvent.prompt();

        const { outcome } = await promptEvent.userChoice;

        if (outcome === 'accepted') {
            isInstallable.value = false;
        }

        (window as any)._deferredPrompt = null;
    };

    onMounted(() => {
        if (
            window.matchMedia('(display-mode: standalone)').matches ||
            (navigator as any).standalone
        ) {
            isStandalone.value = true;
        }

        if ((window as any)._deferredPrompt) {
            isInstallable.value = true;
        }

        window.addEventListener('beforeinstallprompt', handleInstallPrompt);
        window.addEventListener('appinstalled', handleAppInstalled);
    });

    onUnmounted(() => {
        window.removeEventListener('beforeinstallprompt', handleInstallPrompt);
        window.removeEventListener('appinstalled', handleAppInstalled);
    });

    return {
        isInstallable,
        isStandalone,
        installApp,
    };
}
