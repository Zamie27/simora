<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import PwaInstallBanner from '@/components/PwaInstallBanner.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="relative !overflow-visible">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="h-16 w-full shrink-0 group-has-data-[collapsible=icon]/sidebar-wrapper:h-12" aria-hidden="true"></div>
            <PwaInstallBanner />
            <slot />
        </AppContent>
    </AppShell>
</template>
