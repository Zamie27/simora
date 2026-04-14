<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import CustomSelect from '@/components/ui/CustomSelect.vue';
import DatePicker from '@/components/ui/DatePicker.vue';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSeparator,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import type { BreadcrumbItem } from '@/types';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Pengaturan Profil',
        href: edit(),
    },
];

const page = usePage();
const user = computed(() => page.props.auth.user as any);
const genderOptions = [
    { value: 'male', label: 'Laki-laki' },
    { value: 'female', label: 'Perempuan' },
];

const form = useForm({
    _method: 'patch',
    name: user.value.name,
    email: user.value.email,
    date_of_birth: user.value.date_of_birth || '',
    gender: user.value.gender || '',
    avatar: null as File | null,
});

const avatarPreview = ref<string | null>(null);

const emailStep = ref<'initial' | 'otp_sent' | 'otp_verified'>('initial');
const otpForm = useForm({
    otp: '',
});

const handleAvatarChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];

    if (file) {
        form.avatar = file;

        const reader = new FileReader();
        reader.onload = (event) => {
            avatarPreview.value = event.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const sendOtp = () => {
    router.post(
        ProfileController.sendEmailOTP().url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                emailStep.value = 'otp_sent';
            },
        },
    );
};

const verifyOtp = () => {
    otpForm.post(ProfileController.verifyEmailOTP().url, {
        preserveScroll: true,
        onSuccess: () => {
            emailStep.value = 'otp_verified';
            otpForm.reset();
        },
    });
};

const submit = () => {
    form.post('/settings/profile', {
        preserveScroll: true,
        onSuccess: () => {
            avatarPreview.value = null;

            if (emailStep.value === 'otp_verified') {
                emailStep.value = 'initial';
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Pengaturan Profil" />

        <h1 class="sr-only">Pengaturan Profil</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Informasi Profil"
                    description="Perbarui nama, email, dan tanggal lahir Anda"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div
                        class="flex flex-col items-center gap-4 border-b border-border pb-6"
                    >
                        <div
                            class="relative flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border border-border bg-muted/20 shadow-inner"
                        >
                            <img
                                v-if="avatarPreview || user.avatar"
                                :src="avatarPreview || user.avatar"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="text-2xl font-black text-muted-foreground uppercase opacity-40"
                            >
                                {{ user.name.substring(0, 2) }}
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-2">
                            <Label
                                for="avatar"
                                class="cursor-pointer rounded-xl border border-accent/20 bg-accent/10 px-4 py-2 text-[10px] font-black tracking-widest text-accent uppercase transition-all hover:bg-accent/20"
                            >
                                Ganti Foto Profil
                            </Label>
                            <input
                                id="avatar"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="handleAvatarChange"
                            />
                            <p
                                class="text-[9px] font-medium text-muted-foreground uppercase opacity-60"
                            >
                                Maksimal 2MB (JPG, PNG)
                            </p>
                            <InputError
                                class="mt-2"
                                :message="form.errors.avatar"
                            />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="name">Nama Lengkap</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Alamat Email</Label>
                        <div class="flex gap-2">
                            <Input
                                id="email"
                                type="email"
                                class="block w-full"
                                :model-value="
                                    emailStep === 'otp_verified'
                                        ? form.email
                                        : user.email
                                "
                                @update:model-value="
                                    emailStep === 'otp_verified'
                                        ? (form.email = $event)
                                        : null
                                "
                                :disabled="emailStep !== 'otp_verified'"
                                required
                                autocomplete="username"
                            />
                            <Button
                                v-if="emailStep === 'initial'"
                                type="button"
                                variant="outline"
                                @click="sendOtp"
                                :disabled="form.processing"
                            >
                                Ubah Email
                            </Button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />

                        <!-- OTP Step -->
                        <div
                            v-if="emailStep === 'otp_sent'"
                            class="mt-4 space-y-4 rounded-lg border border-accent/20 bg-accent/5 p-4 transition-all"
                        >
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold tracking-wider text-accent uppercase"
                                    >Verifikasi OTP</Label
                                >
                                <p class="text-xs text-muted-foreground">
                                    OTP telah berhasil dikirim ke email,
                                    silahkan cek.
                                </p>
                                <div class="mt-2 flex items-end gap-3">
                                    <InputOTP
                                        v-model="otpForm.otp"
                                        :maxlength="6"
                                    >
                                        <InputOTPGroup>
                                            <InputOTPSlot :index="0" />
                                            <InputOTPSlot :index="1" />
                                            <InputOTPSlot :index="2" />
                                        </InputOTPGroup>
                                        <InputOTPSeparator />
                                        <InputOTPGroup>
                                            <InputOTPSlot :index="3" />
                                            <InputOTPSlot :index="4" />
                                            <InputOTPSlot :index="5" />
                                        </InputOTPGroup>
                                    </InputOTP>
                                    <Button
                                        type="button"
                                        size="sm"
                                        @click="verifyOtp"
                                        :disabled="
                                            otpForm.processing ||
                                            otpForm.otp.length < 6
                                        "
                                    >
                                        Verifikasi
                                    </Button>
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="otpForm.errors.otp"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="date_of_birth">Tanggal Lahir</Label>
                        <DatePicker v-model="form.date_of_birth" />
                        <InputError
                            class="mt-2"
                            :message="form.errors.date_of_birth"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="gender">Jenis Kelamin</Label>
                        <CustomSelect
                            v-model="form.gender"
                            :options="genderOptions"
                            placeholder="Pilih Jenis Kelamin"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.gender"
                        />
                    </div>

                    <div v-if="user.role?.name === 'Atlet'" class="grid gap-2">
                        <Label for="category"
                            >Kategori (Ditentukan Pelatih)</Label
                        >
                        <Input
                            id="category"
                            class="mt-1 block w-full bg-muted/50"
                            :default-value="
                                user.category?.name || '- Belum Ditentukan -'
                            "
                            disabled
                        />
                        <p
                            class="text-[10px] font-medium text-muted-foreground italic"
                        >
                            Kategori Anda dikelola oleh pelatih melalui
                            manajemen kategori.
                        </p>
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Alamat email Anda belum diverifikasi.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Klik di sini untuk mengirim ulang email verifikasi.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            Tautan verifikasi baru telah dikirim ke alamat email Anda.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="form.processing"
                            data-test="update-profile-button"
                            >Simpan</Button
                        >

                        <Transition
                            enter-active-class="transition duration-300 ease-out"
                            enter-from-class="transform -translate-x-4 opacity-0"
                            enter-to-class="transform translate-x-0 opacity-100"
                            leave-active-class="transition duration-200 ease-in"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div
                                v-show="form.recentlySuccessful"
                                class="flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-4 py-2 text-emerald-500"
                            >
                                <CheckCircle2 class="h-3 w-3" />
                                <span
                                    class="text-[10px] font-black tracking-widest uppercase"
                                    >Tersimpan</span
                                >
                            </div>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
