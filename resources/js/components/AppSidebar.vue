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
} from '@/components/ui/sidebar';
import type { NavItem, SharedData } from '@/types';
import { dashboard } from '@/routes';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);
const roleName = computed(() => user.value?.role?.name);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (roleName.value === 'Manajemen') {
        items.push(
            {
                title: 'Managemen User',
                href: '/manajemen/users',
                icon: Users,
            },
            {
                title: 'Aktivasi User',
                href: '/manajemen/pending',
                icon: ShieldCheck,
            },
            {
                title: 'Daftar Atlet',
                href: '/manajemen/athletes',
                icon: UserCheck,
            },
            {
                title: 'Managemen Kategori',
                href: '/manajemen/categories',
                icon: BookOpen,
            },
            {
                title: 'Jenis Latihan',
                href: '/manajemen/exercise-types',
                icon: Activity,
            },
            {
                title: 'Laporan Performa',
                href: '/manajemen/reports',
                icon: FileText,
            },
            {
                title: 'Setting Event',
                href: '/manajemen/event-settings',
                icon: Trophy,
            },
        );
    }

    if (roleName.value === 'Pelatih') {
        items.push(
            {
                title: 'Atlet Saya',
                href: '/pelatih/athletes',
                icon: Users,
            },
            {
                title: 'Jadwal Latihan',
                href: '/pelatih/training-sessions',
                icon: ClipboardList,
            },
            {
                title: 'Kalkulator Gear',
                href: '/tools/gear-calculator',
                icon: Calculator,
            },
            {
                title: 'Perbandingan Performa',
                href: '/pelatih/performance-comparison',
                icon: BarChart3,
            },
            {
                title: 'Laporan',
                href: '/pelatih/reports',
                icon: FileText,
            },
            {
                title: 'Target & Event',
                href: '/pelatih/events',
                icon: Trophy,
            },
        );
    }

    if (roleName.value === 'Atlet') {
        items.push(
            {
                title: 'Update Fisik',
                href: '/atlet/physical',
                icon: Activity,
            },
            {
                title: 'Latihan Saya',
                href: '/atlet/training',
                icon: Bike,
            },
            {
                title: 'Kalkulator Gear',
                href: '/tools/gear-calculator',
                icon: Calculator,
            },
            {
                title: 'Agenda Event',
                href: '/atlet/events',
                icon: Trophy,
            },
            {
                title: 'Dokumen Pribadi',
                href: '/atlet/documents',
                icon: FileText,
            },
        );
    }

    if (roleName.value === 'Report') {
        // No additional items needed since 'Dashboard' now points to bug reports
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

    return `${hours}/${minutes}/${seconds}/, ${day}/${month}/${year}`;
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
                        <Link :href="dashboard()">
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
