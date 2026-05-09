import { ref } from 'vue';

const isOpen = ref(false);
const message = ref('');
const color = ref('success');
const timeout = ref(3000);

export function useSnackbar() {
    const show = (
        newMessage: string,
        newColor: string = 'success',
        newTimeout: number = 3000,
    ) => {
        message.value = newMessage;
        color.value = newColor;
        timeout.value = newTimeout;
        isOpen.value = true;
    };

    const success = (newMessage: string, newTimeout: number = 3000) =>
        show(newMessage, 'success', newTimeout);
    const error = (newMessage: string, newTimeout: number = 4000) =>
        show(newMessage, 'error', newTimeout);
    const warning = (newMessage: string, newTimeout: number = 3000) =>
        show(newMessage, 'warning', newTimeout);
    const info = (newMessage: string, newTimeout: number = 3000) =>
        show(newMessage, 'info', newTimeout);

    return {
        isOpen,
        message,
        color,
        timeout,
        show,
        success,
        error,
        warning,
        info,
    };
}
