<script setup lang="ts">
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="bg-background/95 fixed top-0 right-0 z-50 flex h-16 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 backdrop-blur-xl transition-[left,height] duration-200 ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4 left-0 md:left-(--sidebar-width) group-data-[state=collapsed]/sidebar-wrapper:md:left-(--sidebar-width-icon)"
    >
        <div class="flex flex-1 items-center gap-2 overflow-hidden">
            <SidebarTrigger class="-ml-1" />
            <div class="overflow-hidden">
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>
        </div>
        <div class="flex shrink-0 items-center justify-end pl-2">
            <AppearanceTabs />
        </div>
    </header>
</template>
