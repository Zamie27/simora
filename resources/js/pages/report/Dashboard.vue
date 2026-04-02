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
    image_path: string | null;
    reporter_name: string;
    reporter_contact: string;
    user_id: number | null;
    status: string;
    created_at: string;
}

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
        case 'pending': return 'destructive';
        case 'sedang dikerjakan': return 'default';
        case 'tuntas diperbaiki': return 'outline';
        default: return 'secondary';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'pending': return Clock;
        case 'sedang dikerjakan': return PlayCircle;
        case 'tuntas diperbaiki': return CheckCircle2;
        default: return Clock;
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
        }
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
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Laporan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending</CardTitle>
                        <Clock class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-500">{{ stats.pending }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Sedang Dikerjakan</CardTitle>
                        <PlayCircle class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-500">{{ stats.in_progress }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Tuntas Diperbaiki</CardTitle>
                        <CheckCircle2 class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-500">{{ stats.resolved }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Table -->
            <Card class="flex-1">
                <CardHeader>
                    <CardTitle>Daftar Laporan Bug</CardTitle>
                    <CardDescription>Monitor dan update status laporan yang masuk dari pengguna.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Tanggal</th>
                                    <th class="px-4 py-3 font-medium">Pelapor</th>
                                    <th class="px-4 py-3 font-medium">Judul</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="report in bugReports" :key="report.id" class="hover:bg-muted/50 transition-colors">
                                    <td class="px-4 py-3 font-medium">
                                        {{ new Date(report.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ report.reporter_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ report.reporter_contact }}</div>
                                    </td>
                                    <td class="px-4 py-3 max-w-[300px] truncate">
                                        {{ report.title }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge :variant="getStatusBadgeVariant(report.status)" class="capitalize">
                                            <component :is="getStatusIcon(report.status)" class="w-3 h-3 mr-1"/>
                                            {{ report.status }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <Button variant="ghost" size="sm" @click="selectedReport = report">
                                            <Eye class="w-4 h-4 mr-2" /> Detail
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="bugReports.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
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
        <div v-if="selectedReport" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <Card class="w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <CardHeader class="flex flex-row justify-between items-start sticky top-0 bg-background z-10 border-b">
                    <div>
                        <CardTitle class="text-xl">{{ selectedReport.title }}</CardTitle>
                        <CardDescription>Dilaporkan pada {{ new Date(selectedReport.created_at).toLocaleString('id-ID') }}</CardDescription>
                    </div>
                    <Button variant="ghost" size="icon" @click="selectedReport = null">
                        <X class="w-4 h-4" />
                    </Button>
                </CardHeader>
                <CardContent class="grid gap-6 pt-6">
                    <div>
                        <h4 class="text-sm font-semibold mb-2">Status Saat Ini</h4>
                        <div class="flex items-center gap-2">
                            <Badge :variant="getStatusBadgeVariant(selectedReport.status)" class="text-sm px-3 py-1 capitalize">
                                <component :is="getStatusIcon(selectedReport.status)" class="w-4 h-4 mr-2"/>
                                {{ selectedReport.status }}
                            </Badge>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold mb-2">Update Status</h4>
                        <div class="flex gap-2 flex-wrap">
                            <Button 
                                :disabled="form.processing"
                                :variant="selectedReport.status === 'pending' ? 'default' : 'outline'" 
                                size="sm" 
                                @click="updateStatus(selectedReport.id, 'pending')"
                            >
                                <Clock class="w-4 h-4 mr-2" /> Pending
                            </Button>
                            <Button 
                                :disabled="form.processing"
                                :variant="selectedReport.status === 'sedang dikerjakan' ? 'default' : 'outline'" 
                                size="sm" 
                                @click="updateStatus(selectedReport.id, 'sedang dikerjakan')"
                            >
                                <PlayCircle class="w-4 h-4 mr-2" /> Sedang Dikerjakan
                            </Button>
                            <Button 
                                :disabled="form.processing"
                                :variant="selectedReport.status === 'tuntas diperbaiki' ? 'default' : 'outline'" 
                                size="sm" 
                                @click="updateStatus(selectedReport.id, 'tuntas diperbaiki')"
                            >
                                <CheckCircle2 class="w-4 h-4 mr-2" /> Tuntas Diperbaiki
                            </Button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-muted p-4 rounded-lg">
                            <div class="text-xs font-semibold text-muted-foreground uppercase mb-1">Nama Pelapor</div>
                            <div>{{ selectedReport.reporter_name }}</div>
                        </div>
                        <div class="bg-muted p-4 rounded-lg">
                            <div class="text-xs font-semibold text-muted-foreground uppercase mb-1">Kontak</div>
                            <div>{{ selectedReport.reporter_contact }}</div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold mb-2">Keterangan Bug</h4>
                        <div class="bg-muted p-4 rounded-lg text-sm whitespace-pre-wrap leading-relaxed">
                            {{ selectedReport.description }}
                        </div>
                    </div>

                    <div v-if="selectedReport.image_path">
                        <h4 class="text-sm font-semibold mb-2">Screenshot Terlampir</h4>
                        <div class="rounded-lg overflow-hidden border">
                            <img :src="'/storage/' + selectedReport.image_path" alt="Bug Screenshot" class="w-full h-auto" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppSidebarLayout>
</template>
