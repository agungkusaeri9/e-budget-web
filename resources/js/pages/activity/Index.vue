<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Search,
    Clock,
    User as UserIcon,
    Monitor,
    MapPin,
    Filter,
    X,
    ChevronLeft,
    ChevronRight,
    History,
} from 'lucide-vue-next';
import { ref, watch, reactive } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { useDebounceFn } from '@vueuse/core';
import type { BreadcrumbItem } from '@/types';

interface ActivityLog {
    id: number;
    activity: string;
    description: string;
    data: any;
    ip_address: string;
    user_agent: string;
    created_at: string;
    user?: {
        name: string;
    };
}

interface UserOption {
    id: number;
    name: string;
}

interface Props {
    logs: {
        data: ActivityLog[];
        links: any[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
    filters: {
        search?: string;
        per_page?: string;
        user_id?: string;
        start_date?: string;
        end_date?: string;
    };
    users: UserOption[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Log Aktivitas', href: '/activity' },
];

const filterState = reactive({
    search: props.filters.search || '',
    per_page: props.filters.per_page || '10',
    user_id: props.filters.user_id || 'all',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const isFilterVisible = ref(false);

const buildParams = () => {
    const params: any = { ...filterState };
    if (params.user_id === 'all') delete params.user_id;
    return params;
};

const applyFilters = useDebounceFn(() => {
    router.get('/activity', buildParams(), {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(
    () => ({ ...filterState }),
    () => {
        applyFilters();
    },
);

const resetFilters = () => {
    filterState.search = '';
    filterState.user_id = 'all';
    filterState.start_date = '';
    filterState.end_date = '';
    filterState.per_page = '10';
};

const formatData = (data: any) => {
    if (!data) return null;
    return JSON.stringify(data, null, 2);
};

const perPageOptions = ['10', '20', '30', '40', '50', '100'];
</script>

<template>
    <Head title="Log Aktivitas" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <!-- Breadcrumbs in Content -->
        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <!-- Page Header -->
        <div class="space-y-1">
            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                Log Aktivitas
            </h1>
            <p class="text-sm text-muted-foreground">
                Pantau dan audit seluruh rekam jejak aktivitas pengguna di dalam sistem.
            </p>
        </div>

        <!-- DataTable Card Container -->
        <div class="rounded-xl border bg-card shadow-xs">
            <!-- DataTable Top Controls -->
            <div
                class="flex flex-col gap-4 border-b p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex flex-1 items-center gap-2">
                    <div class="relative w-full max-w-sm">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="filterState.search"
                            type="search"
                            placeholder="Cari aktivitas atau deskripsi..."
                            class="h-8 pr-8 pl-8 text-xs"
                        />
                        <button
                            v-if="filterState.search"
                            type="button"
                            class="absolute top-2 right-2 text-muted-foreground hover:text-foreground"
                            @click="filterState.search = ''"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="isFilterVisible = !isFilterVisible"
                        :class="{ 'bg-muted': isFilterVisible }"
                        class="h-8 text-xs gap-1.5"
                    >
                        <Filter class="h-3.5 w-3.5" />
                        <span>Filter</span>
                    </Button>
                    <Button
                        v-if="
                            Object.values(props.filters).some(
                                (v) => v && v !== 'all' && v !== '10',
                            )
                        "
                        variant="ghost"
                        size="sm"
                        @click="resetFilters"
                        class="h-8 text-xs text-muted-foreground hover:text-foreground"
                    >
                        <X class="mr-1 h-3 w-3" /> Reset
                    </Button>
                </div>

                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <span>Tampilkan</span>
                    <Select v-model="filterState.per_page">
                        <SelectTrigger class="h-8 w-[72px] text-xs">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in perPageOptions"
                                :key="opt"
                                :value="opt"
                            >
                                {{ opt }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <span>data</span>
                </div>
            </div>

            <!-- Expanded Filters Panel -->
            <div
                v-if="isFilterVisible"
                class="grid grid-cols-1 gap-4 border-b bg-muted/30 p-4 md:grid-cols-3"
            >
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">Filter Pengguna</Label>
                    <Select v-model="filterState.user_id">
                        <SelectTrigger class="h-8 text-xs">
                            <SelectValue placeholder="Semua Pengguna" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Pengguna</SelectItem>
                            <SelectItem
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id.toString()"
                            >
                                {{ user.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">Tanggal Mulai</Label>
                    <Input
                        v-model="filterState.start_date"
                        type="date"
                        class="h-8 text-xs"
                    />
                </div>
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-muted-foreground">Tanggal Selesai</Label>
                    <Input
                        v-model="filterState.end_date"
                        type="date"
                        class="h-8 text-xs"
                    />
                </div>
            </div>

            <!-- Table -->
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="bg-muted/50 [&_tr]:border-b">
                        <tr class="border-b transition-colors">
                            <th
                                class="h-10 w-16 px-4 text-center align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                No
                            </th>
                            <th
                                class="h-10 w-[180px] px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Waktu
                            </th>
                            <th
                                class="h-10 w-[160px] px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Pengguna
                            </th>
                            <th
                                class="h-10 w-[150px] px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Aktivitas
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Deskripsi
                            </th>
                            <th
                                class="h-10 w-[160px] px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Info Perangkat
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border [&_tr:last-child]:border-0">
                        <tr
                            v-for="(log, index) in logs.data"
                            :key="log.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td class="p-3.5 text-center align-middle text-xs font-medium text-muted-foreground">
                                {{ (logs.from || 1) + index }}
                            </td>
                            <td class="p-3.5 align-middle whitespace-nowrap text-xs text-muted-foreground">
                                <div class="flex items-center gap-1.5">
                                    <Clock class="h-3.5 w-3.5 text-muted-foreground" />
                                    {{
                                        new Date(
                                            log.created_at,
                                        ).toLocaleString('id-ID', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })
                                    }}
                                </div>
                            </td>
                            <td class="p-3.5 align-middle">
                                <div class="flex items-center gap-2">
                                    <UserIcon class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span class="font-medium text-foreground text-xs">{{ log.user?.name || 'Sistem' }}</span>
                                </div>
                            </td>
                            <td class="p-3.5 align-middle">
                                <Badge variant="secondary" class="font-mono text-[11px] font-medium">
                                    {{ log.activity }}
                                </Badge>
                            </td>
                            <td class="p-3.5 align-middle text-xs">
                                <div class="font-medium text-foreground">{{ log.description }}</div>
                                <details v-if="log.data" class="mt-1">
                                    <summary
                                        class="cursor-pointer text-[11px] text-muted-foreground hover:underline"
                                    >
                                        Lihat Detail Data
                                    </summary>
                                    <pre
                                        class="mt-2 overflow-x-auto rounded bg-muted p-2 text-[10px] font-mono"
                                        >{{ formatData(log.data) }}</pre
                                    >
                                </details>
                            </td>
                            <td class="p-3.5 align-middle">
                                <div
                                    class="flex flex-col gap-1 text-[11px] text-muted-foreground"
                                >
                                    <div class="flex items-center gap-1">
                                        <MapPin class="h-3 w-3 shrink-0" />
                                        <span>{{ log.ip_address }}</span>
                                    </div>
                                    <div
                                        class="flex max-w-[140px] items-center gap-1 truncate"
                                        :title="log.user_agent"
                                    >
                                        <Monitor class="h-3 w-3 shrink-0" />
                                        <span class="truncate">{{ log.user_agent }}</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td
                                colspan="6"
                                class="p-10 text-center"
                            >
                                <div class="flex flex-col items-center justify-center gap-2 text-muted-foreground">
                                    <div class="rounded-full bg-muted p-3">
                                        <History class="h-6 w-6" />
                                    </div>
                                    <p class="font-medium text-foreground">Tidak ada log aktivitas ditemukan</p>
                                    <p class="text-xs">
                                        {{ filterState.search || filterState.start_date || filterState.user_id !== 'all' ? 'Coba sesuaikan filter atau kata kunci pencarian Anda.' : 'Belum ada log aktivitas yang tercatat.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- DataTable Footer / Pagination -->
            <div
                class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-xs text-muted-foreground">
                    Menampilkan {{ logs.from || 0 }} sampai {{ logs.to || 0 }} dari
                    {{ logs.total }} data
                </div>

                <!-- Numbered Pagination -->
                <div class="flex flex-wrap items-center gap-1">
                    <template v-for="(link, i) in logs.links" :key="i">
                        <!-- Previous button -->
                        <Button
                            v-if="i === 0"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url, buildParams(), { preserveState: true })"
                            aria-label="Halaman sebelumnya"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <!-- Next button -->
                        <Button
                            v-else-if="i === logs.links.length - 1"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url, buildParams(), { preserveState: true })"
                            aria-label="Halaman berikutnya"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                        <!-- Ellipsis -->
                        <span
                            v-else-if="link.label === '...'"
                            class="px-2 text-xs text-muted-foreground"
                        >
                            ...
                        </span>
                        <!-- Page Number buttons -->
                        <Button
                            v-else
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            class="h-8 min-w-8 px-2.5 text-xs font-medium"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url, buildParams(), { preserveState: true })"
                        >
                            {{ link.label }}
                        </Button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
