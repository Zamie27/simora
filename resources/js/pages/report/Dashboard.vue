<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { CheckCircle2, Clock, PlayCircle, Eye, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';

interface BugReport {
    id: number;
    title: string;
    description: string;
    image_path: string[] | string | null;
    reporter_name: string;
    reporter_contact: string;
    user_id: number | null;
    status: string;
    url: string | null;
    created_at: string;
}

const getImages = (imagePath: string[] | string | null): string[] => {
    if (!imagePath) {
        return [];
    }

    if (Array.isArray(imagePath)) {
        return imagePath;
    }

    try {
        // Handle potential stringified JSON
        const parsed = JSON.parse(imagePath);

        return Array.isArray(parsed) ? parsed : [imagePath];
    } catch {
        return [imagePath];
    }
};

defineProps<{
    bugReports: BugReport[];
    stats: {
        total: number;
        pending: number;
        in_progress: number;
        resolved: number;
    };
}>();

const breadcrumbs = [
    {
        title: 'Report Dashboard',
        href: '/report/dashboard',
        disabled: true,
    },
];

const selectedReport = ref<BugReport | null>(null);

const form = useForm({
    status: '',
});

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'pending':
            return 'destructive';
        case 'sedang dikerjakan':
            return 'default';
        case 'tuntas diperbaiki':
            return 'outline';
        default:
            return 'secondary';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'pending':
            return Clock;
        case 'sedang dikerjakan':
            return PlayCircle;
        case 'tuntas diperbaiki':
            return CheckCircle2;
        default:
            return Clock;
    }
};

const updateStatus = (reportId: number, newStatus: string) => {
    form.status = newStatus;
    form.patch(`/report/bug-reports/${reportId}/status`, {
        preserveScroll: true,
        onSuccess: () => {
            if (selectedReport.value?.id === reportId) {
                selectedReport.value.status = newStatus;
            }
        },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Report Dashboard" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 pt-0">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Laporan</CardTitle
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Pending</CardTitle
                        >
                        <Clock class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-500">
                            {{ stats.pending }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Sedang Dikerjakan</CardTitle
                        >
                        <PlayCircle class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-500">
                            {{ stats.in_progress }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Tuntas Diperbaiki</CardTitle
                        >
                        <CheckCircle2 class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-500">
                            {{ stats.resolved }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Table -->
            <Card class="flex-1">
                <CardHeader>
                    <CardTitle>Daftar Laporan Bug</CardTitle>
                    <CardDescription
                        >Monitor dan update status laporan yang masuk dari
                        pengguna.</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left text-sm">
                            <thead
                                class="border-b bg-muted/50 text-xs text-muted-foreground uppercase"
                            >
                                <tr>
                                    <th class="px-4 py-3 font-medium">
                                        Tanggal
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        Pelapor
                                    </th>
                                    <th class="px-4 py-3 font-medium">Judul</th>
                                    <th class="px-4 py-3 font-medium">
                                        Status
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="report in bugReports"
                                    :key="report.id"
                                    class="transition-colors hover:bg-muted/50"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{
                                            new Date(
                                                report.created_at,
                                            ).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'short',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })
                                        }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">
                                            {{ report.reporter_name }}
                                        </div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ report.reporter_contact }}
                                        </div>
                                    </td>
                                    <td
                                        class="max-w-[300px] truncate px-4 py-3"
                                    >
                                        {{ report.title }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge
                                            :variant="
                                                getStatusBadgeVariant(
                                                    report.status,
                                                )
                                            "
                                            class="capitalize"
                                        >
                                            <component
                                                :is="
                                                    getStatusIcon(report.status)
                                                "
                                                class="mr-1 h-3 w-3"
                                            />
                                            {{ report.status }}
                                        </Badge>
                                    </td>
                                    <td class="space-x-2 px-4 py-3 text-right">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="selectedReport = report"
                                        >
                                            <Eye class="mr-2 h-4 w-4" /> Detail
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="bugReports.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        Belum ada laporan bug.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Detail Modal -->
        <div
            v-if="selectedReport"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        >
            <Card class="max-h-[90vh] w-full max-w-2xl overflow-y-auto">
                <CardHeader
                    class="sticky top-0 z-10 flex flex-row items-start justify-between border-b bg-background"
                >
                    <div>
                        <CardTitle class="text-xl">{{
                            selectedReport.title
                        }}</CardTitle>
                        <CardDescription
                            >Dilaporkan pada
                            {{
                                new Date(
                                    selectedReport.created_at,
                                ).toLocaleString('id-ID')
                            }}</CardDescription
                        >
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="selectedReport = null"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </CardHeader>
                <CardContent class="grid gap-6 pt-6">
                    <div>
                        <h4 class="mb-2 text-sm font-semibold">
                            Status Saat Ini
                        </h4>
                        <div class="flex items-center gap-2">
                            <Badge
                                :variant="
                                    getStatusBadgeVariant(selectedReport.status)
                                "
                                class="px-3 py-1 text-sm capitalize"
                            >
                                <component
                                    :is="getStatusIcon(selectedReport.status)"
                                    class="mr-2 h-4 w-4"
                                />
                                {{ selectedReport.status }}
                            </Badge>
                        </div>
                    </div>

                    <div>
                        <h4 class="mb-2 text-sm font-semibold">
                            Update Status
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                :disabled="form.processing"
                                :variant="
                                    selectedReport.status === 'pending'
                                        ? 'default'
                                        : 'outline'
                                "
                                size="sm"
                                @click="
                                    updateStatus(selectedReport.id, 'pending')
                                "
                            >
                                <Clock class="mr-2 h-4 w-4" /> Pending
                            </Button>
                            <Button
                                :disabled="form.processing"
                                :variant="
                                    selectedReport.status ===
                                    'sedang dikerjakan'
                                        ? 'default'
                                        : 'outline'
                                "
                                size="sm"
                                @click="
                                    updateStatus(
                                        selectedReport.id,
                                        'sedang dikerjakan',
                                    )
                                "
                            >
                                <PlayCircle class="mr-2 h-4 w-4" /> Sedang
                                Dikerjakan
                            </Button>
                            <Button
                                :disabled="form.processing"
                                :variant="
                                    selectedReport.status ===
                                    'tuntas diperbaiki'
                                        ? 'default'
                                        : 'outline'
                                "
                                size="sm"
                                @click="
                                    updateStatus(
                                        selectedReport.id,
                                        'tuntas diperbaiki',
                                    )
                                "
                            >
                                <CheckCircle2 class="mr-2 h-4 w-4" /> Tuntas
                                Diperbaiki
                            </Button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-lg bg-muted p-4">
                            <div
                                class="mb-1 text-xs font-semibold text-muted-foreground uppercase"
                            >
                                Nama Pelapor
                            </div>
                            <div>{{ selectedReport.reporter_name }}</div>
                        </div>
                        <div class="rounded-lg bg-muted p-4">
                            <div
                                class="mb-1 text-xs font-semibold text-muted-foreground uppercase"
                            >
                                Kontak
                            </div>
                            <div>{{ selectedReport.reporter_contact }}</div>
                        </div>
                    </div>

                    <div v-if="selectedReport.url">
                        <div
                            class="rounded-lg border border-accent/20 bg-accent/5 p-4"
                        >
                            <div
                                class="mb-1 text-xs font-semibold text-accent uppercase"
                            >
                                Halaman Saat Pelaporan (Capture)
                            </div>
                            <a
                                :href="selectedReport.url"
                                target="_blank"
                                class="text-sm font-medium break-all text-blue-500 hover:underline"
                            >
                                {{ selectedReport.url }}
                            </a>
                        </div>
                    </div>

                    <div>
                        <h4 class="mb-2 text-sm font-semibold">
                            Keterangan Bug
                        </h4>
                        <div
                            class="rounded-lg bg-muted p-4 text-sm leading-relaxed whitespace-pre-wrap"
                        >
                            {{ selectedReport.description }}
                        </div>
                    </div>

                    <div v-if="getImages(selectedReport.image_path).length > 0">
                        <h4 class="mb-2 text-sm font-semibold">
                            Screenshot Terlampir ({{
                                getImages(selectedReport.image_path).length
                            }})
                        </h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div
                                v-for="(img, idx) in getImages(
                                    selectedReport.image_path,
                                )"
                                :key="idx"
                                class="group relative overflow-hidden rounded-lg border"
                            >
                                <img
                                    :src="'/storage/' + img"
                                    alt="Bug Screenshot"
                                    class="h-auto w-full cursor-zoom-in transition-transform duration-300 group-hover:scale-105"
                                />
                                <a
                                    :href="'/storage/' + img"
                                    target="_blank"
                                    class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <span class="text-xs font-bold text-white"
                                        >Lihat Ukuran Penuh</span
                                    >
                                </a>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppSidebarLayout>
</template>
