<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

// Mock data for the UI
const users = [
    {
        name: 'Marcus Thorne',
        email: 'm.thorne@simora.fit',
        role: 'Athlete',
        status: 'Online',
        progress: 85,
    },
    {
        name: 'Sarah Jenkins',
        email: 's.jenkins@simora.fit',
        role: 'Lead Coach',
        status: 'In Session',
        progress: 98,
    },
    {
        name: 'Lia Volpe',
        email: 'l.volpe@simora.fit',
        role: 'Athlete',
        status: 'Offline',
        progress: 42,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="min-h-screen flex-1 space-y-8 bg-background p-4 font-sans text-foreground lg:p-8"
        >
            <!-- Top Dashboard Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-12">
                <!-- Large Stats Card: Squad Power -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-border bg-card p-8 shadow-lg md:col-span-6 lg:col-span-12 xl:col-span-6"
                >
                    <div
                        class="relative z-10 flex h-full flex-col justify-between"
                    >
                        <div>
                            <h3
                                class="mb-4 text-xs font-bold tracking-widest text-muted-foreground uppercase opacity-70"
                            >
                                Total Squad Avg. Power
                            </h3>
                            <div class="mb-4 flex items-baseline gap-3">
                                <span
                                    class="text-6xl font-black tracking-tighter text-foreground"
                                    >312.4</span
                                >
                                <span
                                    class="text-xl font-black text-accent italic"
                                    >WATTS</span
                                >
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex items-center gap-1 rounded-full border border-[#4ade80]/20 bg-[#4ade80]/10 px-2.5 py-1 text-sm font-bold text-[#4ade80]"
                                >
                                    <svg
                                        class="h-3 w-3"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                            clip-rule="evenodd"
                                        ></path>
                                    </svg>
                                    12%
                                </span>
                                <span
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase opacity-60"
                                    >vs. previous period</span
                                >
                            </div>
                        </div>

                        <!-- Mini Chart Simulation -->
                        <div class="mt-8 flex h-12 items-end gap-2">
                            <div
                                class="h-1/3 w-full rounded-t-sm bg-muted/30"
                            ></div>
                            <div
                                class="h-2/3 w-full rounded-t-sm bg-muted/30"
                            ></div>
                            <div
                                class="h-1/2 w-full rounded-t-sm bg-muted/30"
                            ></div>
                            <div
                                class="h-full w-full rounded-t-sm bg-gradient-to-t from-accent/40 to-accent shadow-[0_-4px_10px_rgba(255,97,32,0.3)]"
                            ></div>
                            <div
                                class="h-3/4 w-full rounded-t-sm bg-muted/30"
                            ></div>
                            <div
                                class="h-2/5 w-full rounded-t-sm bg-muted/30"
                            ></div>
                        </div>
                    </div>
                    <!-- Background Decoration -->
                    <div
                        class="pointer-events-none absolute -right-8 -bottom-8 h-48 w-48 text-muted/5"
                    >
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.06-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.73,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.06,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.49-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Small Stats Card 1: Athletes -->
                <div
                    class="relative flex flex-col justify-between rounded-xl border border-b-4 border-border border-b-accent bg-card p-8 shadow-lg md:col-span-3 lg:col-span-6 xl:col-span-3"
                >
                    <div class="flex items-start justify-between">
                        <h3
                            class="text-xs font-bold tracking-widest text-muted-foreground uppercase opacity-70"
                        >
                            Total Athletes
                        </h3>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/10 text-accent"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"
                                ></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-6xl font-black text-foreground"
                            >42</span
                        >
                        <p
                            class="mt-2 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-50"
                        >
                            3 New registered this week
                        </p>
                    </div>
                </div>

                <!-- Small Stats Card 2: Active Sessions -->
                <div
                    class="relative flex flex-col justify-between rounded-xl border border-b-4 border-border border-b-secondary bg-card p-8 shadow-lg md:col-span-3 lg:col-span-6 xl:col-span-3"
                >
                    <div class="flex items-start justify-between">
                        <h3
                            class="text-xs font-bold tracking-widest text-muted-foreground uppercase opacity-70"
                        >
                            Active Sessions
                        </h3>
                        <div
                            class="flex items-center gap-1.5 rounded-full border border-secondary/20 bg-secondary/10 px-2.5 py-1 text-[10px] font-black text-secondary uppercase"
                        >
                            <span
                                class="h-1.5 w-1.5 animate-pulse rounded-full bg-secondary"
                            ></span>
                            Live
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-6xl font-black text-foreground"
                            >08</span
                        >
                        <p
                            class="mt-2 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-50"
                        >
                            Real-time telemetry active
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content Area: User Management & Reports -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- User Management Section -->
                <div class="flex flex-col gap-6 lg:col-span-8">
                    <div class="flex items-center justify-between">
                        <h2
                            class="text-2xl font-black tracking-tight text-foreground uppercase"
                        >
                            User Management
                        </h2>
                        <div class="flex gap-2">
                            <button
                                class="rounded-lg border border-border bg-muted p-2 text-muted-foreground hover:bg-muted/80"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                                    ></path>
                                </svg>
                            </button>
                            <button
                                class="rounded-lg border border-border bg-muted p-2 text-muted-foreground hover:bg-muted/80"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-xl border border-border bg-card shadow-xl"
                    >
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead
                                    class="border-b border-border bg-muted/50"
                                >
                                    <tr
                                        class="text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                                    >
                                        <th class="px-6 py-4">User</th>
                                        <th class="px-6 py-4">Role</th>
                                        <th class="px-6 py-4">
                                            Phys. Status & Energy
                                        </th>
                                        <th class="px-6 py-4">Activity</th>
                                        <th class="px-6 py-4 text-center">
                                            Settings
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr
                                        v-for="u in users"
                                        :key="u.email"
                                        class="group transition-colors hover:bg-muted/20"
                                    >
                                        <td class="px-6 py-5">
                                            <div
                                                class="flex items-center gap-4"
                                            >
                                                <div
                                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-secondary-foreground/10 bg-secondary text-xs font-black text-secondary-foreground shadow-inner"
                                                >
                                                    {{
                                                        u.name
                                                            .split(' ')
                                                            .map((n) => n[0])
                                                            .join('')
                                                    }}
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm font-bold text-foreground"
                                                    >
                                                        {{ u.name }}
                                                    </p>
                                                    <p
                                                        class="text-[11px] font-semibold text-muted-foreground"
                                                    >
                                                        {{ u.email }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-xs">
                                            <span
                                                class="rounded-md border border-accent/20 bg-accent/10 px-2.5 py-1 font-black tracking-tighter text-accent uppercase"
                                            >
                                                {{ u.role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div
                                                class="flex w-full flex-col gap-1.5"
                                            >
                                                <div
                                                    class="h-1.5 w-32 overflow-hidden rounded-full bg-muted"
                                                >
                                                    <div
                                                        class="h-full bg-accent transition-all duration-700"
                                                        :style="{
                                                            width:
                                                                u.progress +
                                                                '%',
                                                        }"
                                                    ></div>
                                                </div>
                                                <div
                                                    class="flex w-32 items-center justify-between"
                                                >
                                                    <span
                                                        class="text-[9px] font-black tracking-widest text-accent uppercase"
                                                        >{{ u.progress }}%</span
                                                    >
                                                    <span
                                                        class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-50"
                                                        >Optm.</span
                                                    >
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-xs">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    :class="{
                                                        'bg-[#4ade80]':
                                                            u.status ===
                                                            'Online',
                                                        'bg-accent':
                                                            u.status ===
                                                            'In Session',
                                                        'bg-muted-foreground/30':
                                                            u.status ===
                                                            'Offline',
                                                    }"
                                                    class="h-1.5 w-1.5 rounded-full"
                                                ></span>
                                                <span
                                                    class="font-bold text-muted-foreground opacity-80"
                                                    >{{ u.status }}</span
                                                >
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <button
                                                class="text-muted-foreground transition-colors hover:text-accent"
                                            >
                                                <svg
                                                    class="mx-auto h-5 w-5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                                    ></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Upcoming Events Section -->
                    <div class="mt-4 flex flex-col gap-6">
                        <h2
                            class="text-2xl font-black tracking-tight text-foreground uppercase"
                        >
                            Upcoming Events
                        </h2>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div
                                class="group flex items-center gap-5 rounded-xl border border-border bg-card p-5 shadow-lg transition-colors hover:border-accent/50"
                            >
                                <div
                                    class="flex h-14 w-14 flex-col items-center justify-center rounded-xl bg-accent font-black text-accent-foreground"
                                >
                                    <span class="text-xs uppercase opacity-80"
                                        >OCT</span
                                    >
                                    <span class="text-xl">18</span>
                                </div>
                                <div>
                                    <h4
                                        class="font-bold text-foreground transition-colors group-hover:text-accent"
                                    >
                                        European Grand Prix
                                    </h4>
                                    <p
                                        class="mt-0.5 text-xs font-semibold text-muted-foreground"
                                    >
                                        Varese, Italy • Professional UCI
                                    </p>
                                </div>
                            </div>
                            <div
                                class="group flex items-center gap-5 rounded-xl border border-border bg-card p-5 shadow-lg transition-colors hover:border-accent/50"
                            >
                                <div
                                    class="flex h-14 w-14 flex-col items-center justify-center rounded-xl bg-muted font-black text-muted-foreground"
                                >
                                    <span class="text-xs uppercase opacity-80"
                                        >OCT</span
                                    >
                                    <span class="text-xl">22</span>
                                </div>
                                <div>
                                    <h4
                                        class="font-bold text-foreground transition-colors group-hover:text-accent"
                                    >
                                        Precision Time Trial
                                    </h4>
                                    <p
                                        class="mt-0.5 text-xs font-semibold text-muted-foreground"
                                    >
                                        Local Velodrome • Practice
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Column: Reports & Insights -->
                <div class="flex flex-col gap-8 lg:col-span-4">
                    <!-- Recent Reports Card -->
                    <div
                        class="relative flex flex-col gap-6 overflow-hidden rounded-xl border border-border bg-card p-6 shadow-xl"
                    >
                        <div
                            class="absolute top-0 left-0 h-full w-1 bg-accent"
                        ></div>
                        <h2
                            class="text-lg font-black tracking-tight text-foreground uppercase"
                        >
                            Recent Reports
                        </h2>
                        <div class="flex flex-col gap-4">
                            <div
                                class="group flex cursor-pointer items-center justify-between rounded-lg border border-border/50 bg-muted/30 p-4 transition-all hover:bg-muted/50"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-md bg-white/5 text-muted-foreground group-hover:text-accent"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-black tracking-tight text-foreground uppercase"
                                        >
                                            Weekly_Squad_Performance.pdf
                                        </p>
                                        <p
                                            class="mt-0.5 text-[10px] font-bold text-muted-foreground"
                                        >
                                            Generated Yesterday • 2.4 MB
                                        </p>
                                    </div>
                                </div>
                                <svg
                                    class="h-4 w-4 text-muted-foreground hover:text-accent"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                    ></path>
                                </svg>
                            </div>
                            <div
                                class="group flex cursor-pointer items-center justify-between rounded-lg border border-border/50 bg-muted/30 p-4 transition-all hover:bg-muted/50"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-md bg-white/5 text-muted-foreground group-hover:text-accent"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-black tracking-tight text-foreground uppercase"
                                        >
                                            Athlete_KPI_Summary.xlsx
                                        </p>
                                        <p
                                            class="mt-0.5 text-[10px] font-bold text-muted-foreground"
                                        >
                                            Generated Oct 01 • 1.1 MB
                                        </p>
                                    </div>
                                </div>
                                <svg
                                    class="h-4 w-4 text-muted-foreground hover:text-accent"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                    ></path>
                                </svg>
                            </div>
                            <div
                                class="group flex cursor-pointer items-center justify-between rounded-lg border border-border/50 bg-muted/30 p-4 transition-all hover:bg-muted/50"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-md bg-white/5 text-muted-foreground group-hover:text-accent"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-black tracking-tight text-foreground uppercase"
                                        >
                                            Recovery_Plan_Unit_X.pdf
                                        </p>
                                        <p
                                            class="mt-0.5 text-[10px] font-bold text-muted-foreground"
                                        >
                                            Generated Oct 12 • 524 KB
                                        </p>
                                    </div>
                                </div>
                                <svg
                                    class="h-4 w-4 text-muted-foreground hover:text-accent"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <button
                            class="mt-2 w-full rounded-lg border border-border bg-muted/10 py-3 text-[10px] font-black tracking-widest text-muted-foreground uppercase transition-all hover:border-accent/50 hover:text-accent"
                        >
                            Generate Custom Report
                        </button>
                    </div>

                    <!-- Promo/News Feature Card -->
                    <div
                        class="group relative aspect-[4/3] cursor-pointer overflow-hidden rounded-xl border border-border bg-card shadow-xl"
                    >
                        <img
                            src="https://images.unsplash.com/photo-1517649763962-0c6234978a0b?q=80&w=1000&auto=format&fit=crop"
                            class="h-full w-full object-cover brightness-50 grayscale transition-all duration-700 group-hover:scale-105 group-hover:grayscale-0"
                            alt="Training"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-background via-background/40 to-transparent"
                        ></div>
                        <div
                            class="absolute bottom-0 left-0 flex flex-col gap-2 p-6"
                        >
                            <span
                                class="w-fit rounded bg-accent px-2 py-1 text-[9px] font-black text-accent-foreground uppercase"
                                >Editorial</span
                            >
                            <h3
                                class="text-xl leading-tight font-black tracking-tight text-foreground uppercase"
                            >
                                The Aerodynamics of Team Efficiency
                            </h3>
                            <div class="mt-1 flex items-center gap-3">
                                <span
                                    class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase"
                                    >Oct 2026 Edition</span
                                >
                                <div
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-accent text-accent-foreground"
                                >
                                    <svg
                                        class="h-3 w-3"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        ></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Floating Action Simulation -->
            <div
                class="fixed bottom-8 left-8 md:relative md:bottom-0 md:left-0"
            >
                <button
                    class="group flex items-center gap-3 rounded-xl bg-accent px-6 py-4 text-xs font-black tracking-widest text-accent-foreground uppercase shadow-2xl shadow-accent/40 transition-transform hover:bg-accent/90 active:scale-95"
                >
                    <div
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 transition-transform group-hover:rotate-90"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            ></path>
                        </svg>
                    </div>
                    New Session
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom glow for the power card bar */
.bg-accent {
    box-shadow: 0 0 15px rgba(255, 97, 32, 0.2);
}
</style>
