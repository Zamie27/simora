<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import PasswordRequirement from '@/components/PasswordRequirement.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { update } from '@/routes/password';

const props = defineProps<{
    token: string;
    email: string;
}>();

const form = useForm({
    token: props.token || '',
    email: props.email || '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(update().url, {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase
        title="Reset kata sandi"
        description="Silakan masukkan kata sandi baru Anda di bawah ini"
    >
        <Head title="Reset kata sandi" />

        <form @submit.prevent="submit" class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    v-model="form.email"
                    class="mt-1 block w-full"
                    readonly
                    autocomplete="email"
                />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Kata Sandi Baru</Label>
                <PasswordInput
                    id="password"
                    v-model="form.password"
                    autocomplete="new-password"
                    class="mt-1 block w-full"
                    autofocus
                    placeholder="Kata Sandi Baru"
                />
                <PasswordRequirement :password="form.password" />
                <InputError :message="form.errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Konfirmasi Kata Sandi</Label>
                <PasswordInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    autocomplete="new-password"
                    class="mt-1 block w-full"
                    placeholder="Konfirmasi Kata Sandi"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :disabled="form.processing"
                data-test="reset-password-button"
            >
                <Spinner v-if="form.processing" />
                Reset kata sandi
            </Button>
        </form>
    </AuthBase>
</template>
