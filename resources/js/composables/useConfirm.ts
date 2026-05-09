import { ref } from 'vue';

const isOpen = ref(false);
const title = ref('');
const message = ref('');
let resolveCallback: ((value: boolean) => void) | null = null;

export function useConfirm() {
    const ask = (newTitle: string, newMessage: string): Promise<boolean> => {
        title.value = newTitle;
        message.value = newMessage;
        isOpen.value = true;

        return new Promise((resolve) => {
            resolveCallback = resolve;
        });
    };

    const confirm = () => {
        isOpen.value = false;

        if (resolveCallback) {
resolveCallback(true);
}
    };

    const cancel = () => {
        isOpen.value = false;

        if (resolveCallback) {
resolveCallback(false);
}
    };

    return {
        isOpen,
        title,
        message,
        ask,
        confirm,
        cancel,
    };
}
