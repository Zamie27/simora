<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Activity,
    BarChart3,
    Bike,
    BookOpen,
    Calculator,
    ClipboardList,
    FileText,
    LayoutGrid,
    ShieldCheck,
    UserCheck,
    Users,
    Trophy,
    Info,
    Download,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroup,
    SidebarGroupContent,
} from '@/components/ui/sidebar';
import { usePWA } from '@/composables/usePWA';
import type { NavItem, SharedData } from '@/types';
import { dashboard } from '@/routes';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);
const roleName = computed(() => user.value?.role?.name);

const { isStandalone, installApp } = usePWA();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard().url,
            icon: LayoutGrid,
        },
    ];

    if (roleName.value === 'Manajemen') {
        items.push(
            {
                title: 'Manajemen User',
                href: '/manajemen/users',
                icon: Users,
            },
            {
                title: 'Verifikasi Pendaftaran',
                href: '/manajemen/pending',
                icon: ShieldCheck,
            },
            {
                title: 'Daftar Atlet',
                href: '/manajemen/atlet',
                icon: UserCheck,
            },
            {
                title: 'Kategori Atlet',
                href: '/manajemen/kategori',
                icon: BookOpen,
            },
            {
                title: 'Jenis Latihan',
                href: '/manajemen/jenis-latihan',
                icon: Activity,
            },
            {
                title: 'Laporan Performa',
                href: '/manajemen/laporan',
                icon: FileText,
            },
            {
                title: 'Bandingkan Performa',
                href: '/manajemen/komparasi-performa',
                icon: BarChart3,
            },
            {
                title: 'Dokumen Lisensi UCI',
                href: '/lisensi-uci',
                icon: ShieldCheck,
            },
            {
                title: 'Event & Target',
                href: '/manajemen/acara',
                icon: Trophy,
            },
        );
    }

    if (roleName.value === 'Pelatih') {
        items.push(
            {
                title: 'Atlet Saya',
                href: '/pelatih/atlet',
                icon: Users,
            },
            {
                title: 'Jadwal Latihan',
                href: '/pelatih/sesi-latihan',
                icon: ClipboardList,
            },
            {
                title: 'Kalkulator Gear',
                href: '/tools/gear-calculator',
                icon: Calculator,
            },
            {
                title: 'Bandingkan Performa',
                href: '/pelatih/komparasi-performa',
                icon: BarChart3,
            },
            {
                title: 'Laporan',
                href: '/pelatih/laporan',
                icon: FileText,
            },
            {
                title: 'Lisensi UCI',
                href: '/lisensi-uci',
                icon: ShieldCheck,
            },
            {
                title: 'Target & Event',
                href: '/pelatih/acara',
                icon: Trophy,
            },
        );
    }

    if (roleName.value === 'Atlet') {
        items.push(
            {
                title: 'Update Fisik',
                href: '/atlet/fisik',
                icon: Activity,
            },
            {
                title: 'Latihan Saya',
                href: '/atlet/latihan',
                icon: Bike,
            },
            {
                title: 'Kalkulator Gear',
                href: '/tools/gear-calculator',
                icon: Calculator,
            },
            {
                title: 'Agenda Event',
                href: '/atlet/acara',
                icon: Trophy,
            },
            {
                title: 'Lisensi UCI',
                href: '/lisensi-uci',
                icon: FileText,
            },
        );
    }

    return items;
});

const currentTime = ref(new Date());
let timer: any = null;

const formatClock = (date: Date) => {
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    return `${hours}:${minutes}:${seconds}, ${day}/${month}/${year}`;
};

const clockString = computed(() => formatClock(currentTime.value));

onMounted(() => {
    timer = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const footerNavItems: NavItem[] = [
    {
        title: 'About Kuukok',
        href: 'https://kuukok.my.id',
        icon: Info,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard().url">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <div
                class="mb-2 px-4 text-[10px] font-black tracking-widest text-muted-foreground opacity-60 group-data-[state=collapsed]:hidden"
            >
                {{ clockString }}
            </div>

            <SidebarGroup
                v-if="!isStandalone"
                class="group-data-[collapsible=icon]:p-0"
            >
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                @click="installApp"
                                class="cursor-pointer font-bold text-orange-500 hover:text-orange-600 dark:text-orange-400 dark:hover:text-orange-300"
                            >
                                <Download class="animate-bounce" />
                                <span>Install App</span>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>

            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
