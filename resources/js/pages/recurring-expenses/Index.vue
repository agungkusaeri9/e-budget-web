<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Edit,
    Plus,
    Search,
    Trash2,
    AlertTriangle,
    CalendarClock,
    X,
    ChevronLeft,
    ChevronRight,
    CheckCircle2,
    XCircle,
    Calendar,
    CheckCheck,
    Wallet,
} from 'lucide-vue-next';
import { ref, watch, reactive, computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { useDebounceFn } from '@vueuse/core';
import type { BreadcrumbItem } from '@/types';

interface RecurringExpense {
    id: number;
    category_id: number;
    category_name?: string;
    name: string | null;
    amount: string | number;
    start_month: number;
    start_year: number;
    end_month: number | null;
    end_year: number | null;
    is_active: boolean;
    description: string | null;
    created_at: string;
    paid_count: number;
    total_duration: number | null;
    remaining_count: number | null;
    progress_percentage: number | null;
    is_completed: boolean;
    total_amount: number | null;
    paid_amount: number;
    remaining_amount: number | null;
}

interface CategoryOption {
    id: number;
    name: string;
}

interface Props {
    recurringExpenses: {
        data: RecurringExpense[];
        links: any[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
    categories: CategoryOption[];
    filters: {
        search?: string;
        is_active?: string;
        category_id?: string;
        per_page?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pengeluaran Rutin', href: '/recurring-expenses' },
];

const monthNames = [
    '',
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
];

const formatPeriod = (item: RecurringExpense) => {
    const start = `${monthNames[item.start_month] || ''} ${item.start_year}`;
    if (!item.end_month || !item.end_year) {
        return `${start} - Seterusnya`;
    }
    const end = `${monthNames[item.end_month] || ''} ${item.end_year}`;
    return `${start} - ${end}`;
};

const getDurationBadge = (item: RecurringExpense) => {
    if (!item.end_month || !item.end_year) {
        return 'Berkelanjutan';
    }
    const totalMonths =
        (item.end_year - item.start_year) * 12 +
        (item.end_month - item.start_month) +
        1;
    return `${totalMonths} Bulan`;
};

const summaryStats = computed(() => {
    const list = props.recurringExpenses.data || [];
    const totalItems = props.recurringExpenses.total || list.length;
    const activeCount = list.filter((i) => i.is_active).length;
    const completedCount = list.filter((i) => i.is_completed).length;
    const totalMonthlyAmount = list
        .filter((i) => i.is_active && !i.is_completed)
        .reduce((acc, curr) => acc + Number(curr.amount || 0), 0);

    return {
        totalItems,
        activeCount,
        completedCount,
        totalMonthlyAmount,
    };
});

const filterState = reactive({
    search: props.filters.search || '',
    is_active: props.filters.is_active || 'all',
    category_id: props.filters.category_id || 'all',
    per_page: props.filters.per_page || '10',
});

// Search & filter functionality
const debouncedSearch = useDebounceFn(() => {
    const query: Record<string, string> = {
        per_page: filterState.per_page,
    };

    if (filterState.search) {
        query.search = filterState.search;
    }

    if (filterState.is_active && filterState.is_active !== 'all') {
        query.is_active = filterState.is_active;
    }

    if (filterState.category_id && filterState.category_id !== 'all') {
        query.category_id = filterState.category_id;
    }

    router.get('/recurring-expenses', query, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(
    () => ({ ...filterState }),
    () => {
        debouncedSearch();
    },
);

const clearFilters = () => {
    filterState.search = '';
    filterState.is_active = 'all';
    filterState.category_id = 'all';
    debouncedSearch();
};

const hasActiveFilters = () => {
    return (
        filterState.search !== '' ||
        filterState.is_active !== 'all' ||
        filterState.category_id !== 'all'
    );
};

// Toggle active status
const togglingId = ref<number | null>(null);
const toggleStatus = (item: RecurringExpense) => {
    togglingId.value = item.id;
    router.patch(
        `/recurring-expenses/${item.id}/toggle`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                togglingId.value = null;
            },
        },
    );
};

// Delete dialog functionality
const deleteDialogOpen = ref(false);
const itemToDelete = ref<RecurringExpense | null>(null);
const isDeleting = ref(false);

const confirmDelete = (item: RecurringExpense) => {
    itemToDelete.value = item;
    deleteDialogOpen.value = true;
};

const handleDelete = () => {
    if (!itemToDelete.value) return;

    isDeleting.value = true;
    router.delete(`/recurring-expenses/${itemToDelete.value.id}`, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            itemToDelete.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const formatCurrency = (val: string | number | null | undefined): string => {
    const num = Number(val || 0);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number.isNaN(num) ? 0 : num);
};
</script>

<template>
    <Head title="Pengeluaran Rutin" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Breadcrumbs & Header -->
        <div class="flex flex-col gap-2">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Pengeluaran Rutin
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Kelola jadwal pos pengeluaran rutin atau komitmen berkala yang otomatis diterapkan saat membuat Budget Plan.
                    </p>
                </div>
                <div>
                    <Button as-child class="w-full sm:w-auto">
                        <Link href="/recurring-expenses/create">
                            <Plus class="mr-2 h-4 w-4" />
                            Tambah Pengeluaran Rutin
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Summary Stats Cards -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-xl border bg-card p-4 shadow-xs">
                <div class="flex items-center justify-between text-muted-foreground">
                    <span class="text-xs font-medium">Total Pos Rutin</span>
                    <CalendarClock class="h-4 w-4" />
                </div>
                <div class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                    {{ summaryStats.totalItems }}
                </div>
                <p class="mt-1 text-[11px] text-muted-foreground">
                    Pos pengeluaran terdaftar
                </p>
            </div>

            <div class="rounded-xl border bg-card p-4 shadow-xs">
                <div class="flex items-center justify-between text-muted-foreground">
                    <span class="text-xs font-medium">Komitmen Aktif</span>
                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                </div>
                <div class="mt-2 text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
                    {{ summaryStats.activeCount }}
                </div>
                <p class="mt-1 text-[11px] text-muted-foreground">
                    Sedang berjalan & aktif
                </p>
            </div>

            <div class="rounded-xl border bg-card p-4 shadow-xs">
                <div class="flex items-center justify-between text-muted-foreground">
                    <span class="text-xs font-medium">Beban Rutin / Bulan</span>
                    <Wallet class="h-4 w-4 text-rose-500" />
                </div>
                <div class="mt-2 text-lg font-bold tracking-tight text-rose-600 dark:text-rose-400 font-mono sm:text-xl">
                    {{ formatCurrency(summaryStats.totalMonthlyAmount) }}
                </div>
                <p class="mt-1 text-[11px] text-muted-foreground">
                    Total alokasi pos aktif
                </p>
            </div>

            <div class="rounded-xl border bg-card p-4 shadow-xs">
                <div class="flex items-center justify-between text-muted-foreground">
                    <span class="text-xs font-medium">Komitmen Lunas</span>
                    <CheckCheck class="h-4 w-4 text-blue-500" />
                </div>
                <div class="mt-2 text-2xl font-bold tracking-tight text-blue-600 dark:text-blue-400">
                    {{ summaryStats.completedCount }}
                </div>
                <p class="mt-1 text-[11px] text-muted-foreground">
                    Target pembayaran tuntas
                </p>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center md:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                <!-- Search Input -->
                <div class="relative w-full sm:max-w-xs">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="filterState.search"
                        placeholder="Cari nama atau kategori..."
                        class="pl-9 text-sm"
                    />
                </div>

                <!-- Status Filter -->
                <div class="w-full sm:w-40">
                    <Select v-model="filterState.is_active">
                        <SelectTrigger class="text-sm">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Status</SelectItem>
                            <SelectItem value="true">Aktif</SelectItem>
                            <SelectItem value="false">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Category Filter -->
                <div class="w-full sm:w-48">
                    <Select v-model="filterState.category_id">
                        <SelectTrigger class="text-sm">
                            <SelectValue placeholder="Kategori" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Kategori</SelectItem>
                            <SelectItem
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="String(cat.id)"
                            >
                                {{ cat.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Clear Filters Button -->
                <Button
                    v-if="hasActiveFilters()"
                    variant="ghost"
                    size="sm"
                    @click="clearFilters"
                    class="h-9 px-2 text-muted-foreground hover:text-foreground"
                >
                    <X class="mr-1 h-4 w-4" />
                    Reset
                </Button>
            </div>

            <!-- Per Page Selector -->
            <div class="flex items-center gap-2 self-end md:self-auto">
                <span class="text-xs text-muted-foreground">Tampilkan:</span>
                <Select v-model="filterState.per_page">
                    <SelectTrigger class="h-8 w-16 text-xs">
                        <SelectValue placeholder="10" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="10">10</SelectItem>
                        <SelectItem value="25">25</SelectItem>
                        <SelectItem value="50">50</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <!-- Table Data Section -->
        <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/40 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Kategori & Nama</th>
                            <th class="py-3 px-4">Periode Jadwal</th>
                            <th class="py-3 px-4">Nominal / Bulan</th>
                            <th class="py-3 px-4">Total Keseluruhan</th>
                            <th class="py-3 px-4">Realisasi Bayar</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Catatan</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="item in recurringExpenses.data"
                            :key="item.id"
                            class="hover:bg-muted/30 transition-colors"
                        >
                            <!-- 1. Kategori & Nama -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-foreground">
                                        {{ item.category_name || 'Kategori' }}
                                    </span>
                                    <span
                                        v-if="item.name"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ item.name }}
                                    </span>
                                </div>
                            </td>

                            <!-- 2. Periode Jadwal -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5 text-xs text-foreground">
                                        <Calendar class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                                        <span>{{ formatPeriod(item) }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <Badge variant="secondary" class="text-[10px] font-normal h-5 px-1.5">
                                            {{ getDurationBadge(item) }}
                                        </Badge>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Nominal / Bulan -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-rose-600 dark:text-rose-400">
                                        {{ formatCurrency(item.amount) }}
                                    </span>
                                    <span class="text-[11px] text-muted-foreground">
                                        per bulan
                                    </span>
                                </div>
                            </td>

                            <!-- 4. Total Keseluruhan (nominal * durasi) -->
                            <td class="py-3 px-4">
                                <div v-if="item.total_amount !== null" class="flex flex-col">
                                    <span class="font-mono font-bold text-foreground">
                                        {{ formatCurrency(item.total_amount) }}
                                    </span>
                                    <span class="text-[10px] text-muted-foreground">
                                        {{ item.total_duration }} bln × {{ formatCurrency(item.amount) }}
                                    </span>
                                </div>
                                <div v-else class="flex flex-col">
                                    <span class="font-mono font-bold text-muted-foreground">—</span>
                                    <span class="text-[11px] text-muted-foreground">Berkelanjutan</span>
                                </div>
                            </td>

                            <!-- 5. Realisasi Bayar -->
                            <td class="py-3 px-4">
                                <!-- Jika memiliki batas durasi/target periode -->
                                <div v-if="item.total_duration" class="flex flex-col gap-1.5 min-w-[170px]">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-foreground">
                                            {{ item.paid_count }} / {{ item.total_duration }} Kali
                                        </span>
                                        <span
                                            v-if="item.is_completed"
                                            class="inline-flex items-center gap-0.5 rounded-full bg-emerald-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400"
                                        >
                                            <CheckCircle2 class="h-3 w-3" /> Lunas
                                        </span>
                                        <span v-else class="text-[11px] text-muted-foreground">
                                            Sisa {{ item.remaining_count }}x
                                        </span>
                                    </div>
                                    <!-- Progress bar -->
                                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                        <div
                                            class="h-full rounded-full transition-all duration-300"
                                            :class="item.is_completed ? 'bg-emerald-500' : 'bg-primary'"
                                            :style="{ width: `${item.progress_percentage}%` }"
                                        />
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] text-muted-foreground">
                                        <span>Terbayar: <strong class="text-foreground font-mono font-normal">{{ formatCurrency(item.paid_amount) }}</strong></span>
                                        <span class="font-mono font-medium">{{ item.progress_percentage }}%</span>
                                    </div>
                                </div>
                                <!-- Jika berkelanjutan (tanpa batas akhir) -->
                                <div v-else class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-foreground text-xs">
                                        {{ item.paid_count }}x Terbayar
                                    </span>
                                    <span class="text-[11px] text-muted-foreground">
                                        Total: {{ formatCurrency(item.paid_amount) }}
                                    </span>
                                </div>
                            </td>

                            <!-- 6. Status Aktif Toggle -->
                            <td class="py-3 px-4">
                                <button
                                    type="button"
                                    @click="toggleStatus(item)"
                                    :disabled="togglingId === item.id"
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium transition-colors cursor-pointer disabled:opacity-50"
                                    :class="
                                        item.is_active
                                            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20'
                                            : 'bg-muted text-muted-foreground hover:bg-muted/80'
                                    "
                                    :title="item.is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan'"
                                >
                                    <CheckCircle2 v-if="item.is_active" class="h-3 w-3" />
                                    <XCircle v-else class="h-3 w-3" />
                                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>

                            <!-- 7. Catatan -->
                            <td class="py-3 px-4 text-xs text-muted-foreground max-w-[180px] truncate">
                                {{ item.description || '-' }}
                            </td>

                            <!-- 8. Aksi -->
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Button as-child variant="ghost" size="icon" class="h-8 w-8">
                                        <Link :href="`/recurring-expenses/${item.id}/edit`">
                                            <Edit class="h-3.5 w-3.5" />
                                            <span class="sr-only">Edit</span>
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                        @click="confirmDelete(item)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                        <span class="sr-only">Hapus</span>
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="recurringExpenses.data.length === 0">
                            <td colspan="8" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="rounded-full bg-muted p-3 text-muted-foreground">
                                        <CalendarClock class="h-6 w-6" />
                                    </div>
                                    <p class="font-medium text-foreground">
                                        Tidak ada data pengeluaran rutin
                                    </p>
                                    <p class="text-xs text-muted-foreground max-w-sm">
                                        Belum ada jadwal pengeluaran rutin yang tersimpan atau tidak ditemukan dengan filter yang dipilih.
                                    </p>
                                    <Button as-child size="sm" class="mt-2">
                                        <Link href="/recurring-expenses/create">
                                            <Plus class="mr-1.5 h-3.5 w-3.5" />
                                            Tambah Sekarang
                                        </Link>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div
                v-if="recurringExpenses.total > 0"
                class="flex flex-col gap-4 border-t px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-xs text-muted-foreground">
                    Menampilkan
                    <span class="font-medium text-foreground">{{ recurringExpenses.from }}</span>
                    sampai
                    <span class="font-medium text-foreground">{{ recurringExpenses.to }}</span>
                    dari
                    <span class="font-medium text-foreground">{{ recurringExpenses.total }}</span>
                    data
                </div>

                <div class="flex items-center gap-1 self-end sm:self-auto">
                    <template v-for="(link, i) in recurringExpenses.links" :key="i">
                        <Button
                            v-if="link.url"
                            as-child
                            size="sm"
                            :variant="link.active ? 'default' : 'outline'"
                            class="h-8 min-w-8 px-2 text-xs"
                        >
                            <Link :href="link.url" preserve-scroll>
                                <span v-if="link.label.includes('Previous')" class="flex items-center">
                                    <ChevronLeft class="h-3.5 w-3.5" />
                                </span>
                                <span v-else-if="link.label.includes('Next')" class="flex items-center">
                                    <ChevronRight class="h-3.5 w-3.5" />
                                </span>
                                <span v-else v-html="link.label"></span>
                            </Link>
                        </Button>
                        <Button
                            v-else
                            disabled
                            size="sm"
                            variant="outline"
                            class="h-8 min-w-8 px-2 text-xs opacity-50"
                        >
                            <span v-if="link.label.includes('Previous')">
                                <ChevronLeft class="h-3.5 w-3.5" />
                            </span>
                            <span v-else-if="link.label.includes('Next')">
                                <ChevronRight class="h-3.5 w-3.5" />
                            </span>
                            <span v-else v-html="link.label"></span>
                        </Button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <div class="flex items-center gap-2 text-destructive">
                        <AlertTriangle class="h-5 w-5" />
                        <DialogTitle>Konfirmasi Hapus</DialogTitle>
                    </div>
                    <DialogDescription class="pt-2 text-sm">
                        Apakah Anda yakin ingin menghapus jadwal pengeluaran rutin
                        <span class="font-semibold text-foreground">
                            {{ itemToDelete?.name || itemToDelete?.category_name }}
                        </span>
                        senilai
                        <span class="font-semibold text-rose-600 dark:text-rose-400">
                            {{ formatCurrency(itemToDelete?.amount) }}
                        </span>?
                        Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-4 gap-2 sm:gap-0">
                    <Button
                        type="button"
                        variant="outline"
                        @click="deleteDialogOpen = false"
                        :disabled="isDeleting"
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        @click="handleDelete"
                        :disabled="isDeleting"
                    >
                        {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

