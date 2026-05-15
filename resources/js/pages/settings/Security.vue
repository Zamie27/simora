<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import PasswordRequirement from '@/components/PasswordRequirement.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import type { BreadcrumbItem } from '@/types';
import { edit } from '@/routes/security';
import { disable, enable } from '@/routes/two-factor';
import { update } from '@/routes/user-password';

type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pengaturan Keamanan',
        href: edit(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);
const processing2FA = ref<boolean>(false);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.put(update.url(), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
            }

            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
            }
        },
    });
};

const enableTwoFactor = () => {
    processing2FA.value = true;
    router.post(
        enable.url(),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showSetupModal.value = true;
                processing2FA.value = false;
            },
            onFinish: () => (processing2FA.value = false),
        },
    );
};

const disableTwoFactor = () => {
    processing2FA.value = true;
    router.delete(disable.url(), {
        preserveScroll: true,
        onSuccess: () => (processing2FA.value = false),
        onFinish: () => (processing2FA.value = false),
    });
};

onUnmounted(() => clearTwoFactorAuthData());
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Pengaturan Keamanan" />

        <h1 class="sr-only">Pengaturan Keamanan</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Perbarui kata sandi"
                    description="Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman"
                />

                <form @submit.prevent="submitPassword" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="current_password"
                            >Kata sandi saat ini</Label
                        >
                        <PasswordInput
                            id="current_password"
                            v-model="passwordForm.current_password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            placeholder="Kata sandi saat ini"
                        />
                        <InputError
                            :message="passwordForm.errors.current_password"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Kata sandi baru</Label>
                        <PasswordInput
                            id="password"
                            v-model="passwordForm.password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Kata sandi baru"
                        />
                        <PasswordRequirement
                            :password="passwordForm.password"
                        />
                        <InputError :message="passwordForm.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Konfirmasi kata sandi</Label
                        >
                        <PasswordInput
                            id="password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Konfirmasi kata sandi"
                        />
                        <InputError
                            :message="passwordForm.errors.password_confirmation"
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="passwordForm.processing"
                            data-test="update-password-button"
                        >
                            Simpan kata sandi
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="passwordForm.recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Tersimpan.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <div v-if="canManageTwoFactor" class="space-y-6">
                <Heading
                    variant="small"
                    title="Autentikasi dua faktor"
                    description="Kelola pengaturan autentikasi dua faktor Anda"
                />

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <p class="text-sm text-muted-foreground">
                        Saat Anda mengaktifkan autentikasi dua faktor, Anda akan
                        diminta memasukkan PIN aman saat login. PIN ini dapat
                        diperoleh dari aplikasi pendukung TOTP di ponsel Anda.
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck />Lanjutkan pengaturan
                        </Button>
                        <Button
                            v-else
                            @click="enableTwoFactor"
                            :disabled="processing2FA"
                        >
                            Aktifkan 2FA
                        </Button>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <p class="text-sm text-muted-foreground">
                        Anda akan diminta memasukkan PIN acak yang aman saat
                        login, yang dapat Anda peroleh dari aplikasi pendukung
                        TOTP di ponsel Anda.
                    </p>

                    <div class="relative inline">
                        <Button
                            variant="destructive"
                            @click="disableTwoFactor"
                            :disabled="processing2FA"
                        >
                            Matikan 2FA
                        </Button>
                    </div>

                    <TwoFactorRecoveryCodes />
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
