<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import CustomSelect from '@/components/ui/CustomSelect.vue';
import DatePicker from '@/components/ui/DatePicker.vue';
import { Input } from '@/components/ui/input';
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
        title: 'Profile settings',
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

const submit = () => {
    form.post('/settings/profile', {
        preserveScroll: true,
        onSuccess: () => {
            avatarPreview.value = null;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <h1 class="sr-only">Profile settings</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Profile information"
                    description="Update your name, email and birth date"
                />

                <form
                    @submit.prevent="submit"
                    class="space-y-6"
                >
                    <div class="flex flex-col items-center gap-4 border-b border-border pb-6">
                        <div class="relative h-24 w-24 overflow-hidden rounded-full border border-border shadow-inner bg-muted/20 flex items-center justify-center">
                            <img v-if="avatarPreview || user.avatar" :src="avatarPreview || user.avatar" class="h-full w-full object-cover" />
                            <div v-else class="text-2xl font-black text-muted-foreground uppercase opacity-40">
                                {{ user.name.substring(0, 2) }}
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-2">
                            <Label for="avatar" class="cursor-pointer rounded-xl border border-accent/20 bg-accent/10 px-4 py-2 text-[10px] font-black tracking-widest text-accent uppercase transition-all hover:bg-accent/20">
                                Ganti Foto Profil
                            </Label>
                            <input
                                id="avatar"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="handleAvatarChange"
                            />
                            <p class="text-[9px] font-medium text-muted-foreground uppercase opacity-60">Maksimal 2MB (JPG, PNG)</p>
                            <InputError class="mt-2" :message="form.errors.avatar" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
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
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
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
                        <InputError class="mt-2" :message="form.errors.gender" />
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
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="form.processing"
                            data-test="update-profile-button"
                            >Save</Button
                        >

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="form.recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
