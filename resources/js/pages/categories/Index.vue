<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Edit,
    Plus,
    Search,
    Trash2,
    AlertTriangle,
    Tags,
    ArrowUpCircle,
    ArrowDownCircle,
    X,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import { ref, watch, reactive } from 'vue';
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

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    amount: string | number | null;
    description: string | null;
    is_default: boolean;
    is_saving: boolean;
    saving_name: string | null;
    order_no: number;
    created_at: string;
}

interface Props {
    categories: {
        data: Category[];
        links: any[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
    filters: {
        search?: string;
        type?: string;
        per_page?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Data Master', href: '#' },
    { title: 'Manajemen Kategori', href: '/categories' },
];

const filterState = reactive({
    search: props.filters.search || '',
    type: props.filters.type || 'all',
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

    if (filterState.type && filterState.type !== 'all') {
        query.type = filterState.type;
    }

    router.get('/categories', query, { preserveState: true, replace: true });
}, 300);

watch(
    () => ({ ...filterState }),
    () => {
        debouncedSearch();
    },
);

const clearSearch = () => {
    filterState.search = '';
};

const formatCurrency = (amount: string | number | null): string => {
    if (amount === null || amount === undefined || amount === '') {
        return '-';
    }

    const numericValue = Number(amount);
    if (Number.isNaN(numericValue)) {
        return '-';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(numericValue);
};

// Delete confirmation handling
const isDeleteDialogOpen = ref(false);
const categoryToDelete = ref<Category | null>(null);

const confirmDelete = (category: Category) => {
    categoryToDelete.value = category;
    isDeleteDialogOpen.value = true;
};

const executeDelete = () => {
    if (categoryToDelete.value) {
        router.delete(`/categories/${categoryToDelete.value.id}`, {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                categoryToDelete.value = null;
            },
        });
    }
};

const perPageOptions = ['10', '20', '30', '40', '50', '100'];
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head title="Manajemen Kategori" />

        <!-- Breadcrumbs in Content -->
        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <h1
                        class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                    >
                        Manajemen Kategori
                    </h1>
                </div>
                <p class="text-sm text-muted-foreground">
                    Kelola kategori pemasukan dan pengeluaran beserta nominal
                    default dan deskripsinya.
                </p>
            </div>

            <Button as-child class="shrink-0">
                <Link href="/categories/create">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Kategori
                </Link>
            </Button>
        </div>

        <!-- DataTable Card Container -->
        <div class="rounded-xl border bg-card shadow-xs">
            <!-- DataTable Top Controls -->
            <div
                class="flex flex-col gap-4 border-b p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex flex-wrap items-center gap-3">
                    <div
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
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

                    <div
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <span>Tipe:</span>
                        <Select v-model="filterState.type">
                            <SelectTrigger class="h-8 w-[150px] text-xs">
                                <SelectValue placeholder="Semua Tipe" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Tipe</SelectItem>
                                <SelectItem value="income">Pemasukan</SelectItem>
                                <SelectItem value="expense">Pengeluaran</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="relative w-full max-w-xs sm:w-64">
                    <Search
                        class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                    />
                    <Input
                        v-model="filterState.search"
                        type="search"
                        placeholder="Cari kategori..."
                        class="h-8 pr-8 pl-8 text-xs"
                    />
                    <button
                        v-if="filterState.search"
                        type="button"
                        class="absolute top-2 right-2 text-muted-foreground hover:text-foreground"
                        @click="clearSearch"
                    >
                        <X class="h-4 w-4" />
                    </button>
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
                                class="h-10 w-24 px-4 text-center align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Order No
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Nama Kategori
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Tipe
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Nominal Default
                            </th>
                            <th
                                class="h-10 px-4 text-center align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Default Budget Plan
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Deskripsi
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Tanggal Dibuat
                            </th>
                            <th
                                class="h-10 px-4 text-right align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-border [&_tr:last-child]:border-0"
                    >
                        <tr
                            v-for="(category, index) in categories.data"
                            :key="category.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td
                                class="p-3.5 text-center align-middle text-xs font-medium text-muted-foreground"
                            >
                                {{ (categories.from || 1) + index }}
                            </td>
                            <td
                                class="p-3.5 text-center align-middle font-mono text-xs font-semibold text-foreground"
                            >
                                {{ category.order_no }}
                            </td>
                            <td class="p-3.5 align-middle">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg"
                                        :class="
                                            category.type === 'income'
                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                : 'bg-rose-500/10 text-rose-600 dark:text-rose-400'
                                        "
                                    >
                                        <ArrowUpCircle
                                            v-if="category.type === 'income'"
                                            class="h-4 w-4"
                                        />
                                        <ArrowDownCircle
                                            v-else
                                            class="h-4 w-4"
                                        />
                                    </div>
                                    <span class="font-medium text-foreground">{{
                                        category.name
                                    }}</span>
                                </div>
                            </td>
                            <td class="p-3.5 align-middle">
                                <Badge
                                    v-if="category.type === 'income'"
                                    variant="default"
                                    class="bg-emerald-600 text-[11px] font-medium hover:bg-emerald-700"
                                >
                                    Pemasukan
                                </Badge>
                                <Badge
                                    v-else
                                    variant="destructive"
                                    class="text-[11px] font-medium"
                                >
                                    Pengeluaran
                                </Badge>
                                <Badge
                                    v-if="category.is_saving"
                                    variant="secondary"
                                    class="ml-1 text-[11px] font-medium"
                                >
                                    Tabungan: {{ category.saving_name }}
                                </Badge>
                            </td>
                            <td
                                class="p-3.5 align-middle font-mono text-xs font-medium text-foreground"
                            >
                                {{ formatCurrency(category.amount) }}
                            </td>
                            <td class="p-3.5 text-center align-middle">
                                <Badge
                                    v-if="category.is_default"
                                    variant="secondary"
                                    class="text-[11px] font-medium"
                                >
                                    Default
                                </Badge>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                >
                                    -
                                </span>
                            </td>
                            <td
                                class="max-w-xs truncate p-3.5 align-middle text-xs text-muted-foreground"
                                :title="category.description || ''"
                            >
                                {{ category.description || '-' }}
                            </td>
                            <td
                                class="p-3.5 align-middle text-xs text-muted-foreground"
                            >
                                {{
                                    new Date(
                                        category.created_at,
                                    ).toLocaleDateString('id-ID', {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric',
                                    })
                                }}
                            </td>
                            <td class="p-3.5 text-right align-middle">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-muted-foreground hover:text-foreground"
                                        as-child
                                        title="Edit Kategori"
                                    >
                                        <Link
                                            :href="`/categories/${category.id}/edit`"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-muted-foreground hover:text-destructive"
                                        title="Hapus Kategori"
                                        @click="confirmDelete(category)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categories.data.length === 0">
                            <td colspan="9" class="p-10 text-center">
                                <div
                                    class="flex flex-col items-center justify-center gap-2 text-muted-foreground"
                                >
                                    <div class="rounded-full bg-muted p-3">
                                        <Tags class="h-6 w-6" />
                                    </div>
                                    <p class="font-medium text-foreground">
                                        Data kategori tidak ditemukan
                                    </p>
                                    <p class="text-xs">
                                        {{
                                            filterState.search ||
                                            filterState.type !== 'all'
                                                ? 'Coba ubah filter atau kata kunci pencarian Anda.'
                                                : 'Belum ada data kategori yang terdaftar.'
                                        }}
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
                    Menampilkan {{ categories.from || 0 }} sampai
                    {{ categories.to || 0 }} dari {{ categories.total }} data
                </div>

                <!-- Numbered Pagination -->
                <div class="flex flex-wrap items-center gap-1">
                    <template v-for="(link, i) in categories.links" :key="i">
                        <!-- Previous button -->
                        <Button
                            v-if="i === 0"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            :disabled="!link.url"
                            @click="
                                link.url &&
                                router.get(
                                    link.url,
                                    { ...filterState },
                                    { preserveState: true },
                                )
                            "
                            aria-label="Halaman sebelumnya"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <!-- Next button -->
                        <Button
                            v-else-if="i === categories.links.length - 1"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            :disabled="!link.url"
                            @click="
                                link.url &&
                                router.get(
                                    link.url,
                                    { ...filterState },
                                    { preserveState: true },
                                )
                            "
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
                            @click="
                                link.url &&
                                router.get(
                                    link.url,
                                    { ...filterState },
                                    { preserveState: true },
                                )
                            "
                        >
                            {{ link.label }}
                        </Button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Dialog
            :open="isDeleteDialogOpen"
            @update:open="isDeleteDialogOpen = $event"
        >
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <div class="mb-2 flex items-center gap-2 text-destructive">
                        <AlertTriangle class="h-5 w-5" />
                        <DialogTitle>Konfirmasi Hapus</DialogTitle>
                    </div>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus kategori
                        <strong>{{ categoryToDelete?.name }}</strong
                        >? Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-3 sm:gap-3">
                    <Button
                        variant="outline"
                        @click="isDeleteDialogOpen = false"
                    >
                        Batal
                    </Button>
                    <Button variant="destructive" @click="executeDelete">
                        Hapus Kategori
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

