<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Verifikasi Email"
        description="Terima kasih telah mendaftar! Silakan verifikasi alamat email Anda dengan mengklik tombol di bawah ini untuk mengirimkan tautan verifikasi ke email Anda."
    >
        <Head title="Email verification" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda
            berikan saat pendaftaran.
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button :disabled="processing" variant="secondary">
                <Spinner v-if="processing" />
                {{ status === 'verification-link-sent' ? 'Kirim Ulang Email Verifikasi' : 'Kirim Email Verifikasi' }}
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                Keluar
            </TextLink>
        </Form>
    </AuthLayout>
</template>
