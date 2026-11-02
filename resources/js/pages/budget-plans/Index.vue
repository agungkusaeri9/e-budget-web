<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Eye,
    Edit,
    Plus,
    Trash2,
    AlertTriangle,
    WalletCards,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import { ref, watch, reactive } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
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
import type { BreadcrumbItem } from '@/types';

interface BudgetPlan {
    id: number;
    month: number;
    year: number;
    status: 'draft' | 'active' | 'closed';
    notes: string | null;
    total_income: string | number;
    total_overtime: string | number;
    total_expense: string | number;
    balance: string | number;
    items_count: number;
    created_at: string;
}

interface Props {
    budgetPlans: {
        data: BudgetPlan[];
        links: any[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
    filters: {
        month?: string;
        year?: string;
        status?: string;
        per_page?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Budget Plan', href: '/budget-plans' },
];

const months = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' },
];

const currentYear = new Date().getFullYear();
const yearOptions = Array.from({ length: 7 }, (_, i) =>
    String(currentYear - 2 + i),
);

const getMonthName = (month: number): string => {
    return months.find((m) => m.value === Number(month))?.label || '-';
};

const formatCurrency = (amount: string | number | null): string => {
    const numericValue = Number(amount || 0);
    if (Number.isNaN(numericValue)) {
        return 'Rp 0';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(numericValue);
};

const filterState = reactive({
    month: props.filters.month || 'all',
    year: props.filters.year || 'all',
    status: props.filters.status || 'all',
    per_page: props.filters.per_page || '10',
});

watch(
    () => ({ ...filterState }),
    () => {
        const query: Record<string, string> = {
            per_page: filterState.per_page,
        };

        if (filterState.month !== 'all') {
            query.month = filterState.month;
        }
        if (filterState.year !== 'all') {
            query.year = filterState.year;
        }
        if (filterState.status !== 'all') {
            query.status = filterState.status;
        }

        router.get('/budget-plans', query, {
            preserveState: true,
            replace: true,
        });
    },
);

// Delete confirmation handling
const isDeleteDialogOpen = ref(false);
const planToDelete = ref<BudgetPlan | null>(null);

const confirmDelete = (plan: BudgetPlan) => {
    planToDelete.value = plan;
    isDeleteDialogOpen.value = true;
};

const executeDelete = () => {
    if (planToDelete.value) {
        router.delete(`/budget-plans/${planToDelete.value.id}`, {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                planToDelete.value = null;
            },
        });
    }
};

const perPageOptions = ['10', '20', '30', '50'];
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head title="Budget Plan" />

        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-1">
                <h1
                    class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                >
                    Budget Plan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Kelola rencana anggaran pemasukan bulanan dan alokasi
                    pengeluaran untuk bulan berikutnya.
                </p>
            </div>

            <Button as-child class="shrink-0">
                <Link href="/budget-plans/create">
                    <Plus class="mr-2 h-4 w-4" />
                    Buat Budget Plan
                </Link>
            </Button>
        </div>

        <!-- DataTable Card Container -->
        <div class="rounded-xl border bg-card shadow-xs">
            <!-- Top Filters -->
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
                    </div>

                    <Select v-model="filterState.month">
                        <SelectTrigger class="h-8 w-[140px] text-xs">
                            <SelectValue placeholder="Semua Bulan" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Bulan</SelectItem>
                            <SelectItem
                                v-for="m in months"
                                :key="m.value"
                                :value="String(m.value)"
                            >
                                {{ m.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="filterState.year">
                        <SelectTrigger class="h-8 w-[120px] text-xs">
                            <SelectValue placeholder="Semua Tahun" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Tahun</SelectItem>
                            <SelectItem
                                v-for="y in yearOptions"
                                :key="y"
                                :value="y"
                            >
                                {{ y }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="filterState.status">
                        <SelectTrigger class="h-8 w-[130px] text-xs">
                            <SelectValue placeholder="Semua Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Status</SelectItem>
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="active">Aktif</SelectItem>
                            <SelectItem value="closed">Selesai</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Table -->
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="bg-muted/50 [&_tr]:border-b">
                        <tr class="border-b transition-colors">
                            <th
                                class="h-10 w-14 px-4 text-center align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                No
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Periode
                            </th>
                            <th
                                class="h-10 px-4 text-right align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Total Pemasukan
                            </th>
                            <th
                                class="h-10 px-4 text-right align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Total Pengeluaran
                            </th>
                            <th
                                class="h-10 px-4 text-right align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Sisa
                            </th>
                            <th
                                class="h-10 px-4 text-center align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Status
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
                            v-for="(plan, index) in budgetPlans.data"
                            :key="plan.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td
                                class="p-3.5 text-center align-middle text-xs text-muted-foreground"
                            >
                                {{ (budgetPlans.from || 1) + index }}
                            </td>
                            <td class="p-3.5 align-middle text-foreground">
                                {{ getMonthName(plan.month) }} {{ plan.year }}
                            </td>
                            <td
                                class="p-3.5 text-right align-middle font-mono text-xs text-emerald-600 dark:text-emerald-400"
                            >
                                {{ formatCurrency(plan.total_income) }}
                            </td>
                            <td
                                class="p-3.5 text-right align-middle font-mono text-xs text-rose-600 dark:text-rose-400"
                            >
                                {{ formatCurrency(plan.total_expense) }}
                            </td>
                            <td
                                class="p-3.5 text-right align-middle font-mono text-xs"
                                :class="
                                    Number(plan.balance) >= 0
                                        ? 'text-foreground'
                                        : 'text-destructive'
                                "
                            >
                                {{ formatCurrency(plan.balance) }}
                            </td>
                            <td class="p-3.5 text-center align-middle">
                                <Badge
                                    v-if="plan.status === 'active'"
                                    variant="default"
                                    class="bg-emerald-600 text-[11px] font-normal hover:bg-emerald-700"
                                >
                                    Aktif
                                </Badge>
                                <Badge
                                    v-else-if="plan.status === 'closed'"
                                    variant="secondary"
                                    class="text-[11px] font-normal"
                                >
                                    Selesai
                                </Badge>
                                <Badge
                                    v-else
                                    variant="outline"
                                    class="text-[11px] font-normal"
                                >
                                    Draft
                                </Badge>
                            </td>
                            <td class="p-3.5 text-right align-middle">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-muted-foreground hover:text-foreground"
                                        as-child
                                        title="Detail Budget Plan"
                                    >
                                        <Link
                                            :href="`/budget-plans/${plan.id}`"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-muted-foreground hover:text-foreground"
                                        as-child
                                        title="Edit Budget Plan"
                                    >
                                        <Link
                                            :href="`/budget-plans/${plan.id}/edit`"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-muted-foreground hover:text-destructive"
                                        title="Hapus Budget Plan"
                                        @click="confirmDelete(plan)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="budgetPlans.data.length === 0">
                            <td colspan="7" class="p-10 text-center">
                                <div
                                    class="flex flex-col items-center justify-center gap-2 text-muted-foreground"
                                >
                                    <div class="rounded-full bg-muted p-3">
                                        <WalletCards class="h-6 w-6" />
                                    </div>
                                    <p class="font-medium text-foreground">
                                        Data Budget Plan belum ada
                                    </p>
                                    <p class="text-xs">
                                        Klik tombol "Buat Budget Plan" untuk
                                        merencanakan pemasukan dan pengeluaran
                                        bulanan.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer / Pagination -->
            <div
                class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-xs text-muted-foreground">
                    Menampilkan {{ budgetPlans.from || 0 }} sampai
                    {{ budgetPlans.to || 0 }} dari {{ budgetPlans.total }} data
                </div>

                <div class="flex flex-wrap items-center gap-1">
                    <template v-for="(link, i) in budgetPlans.links" :key="i">
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
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <Button
                            v-else-if="i === budgetPlans.links.length - 1"
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
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                        <span
                            v-else-if="link.label === '...'"
                            class="px-2 text-xs text-muted-foreground"
                        >
                            ...
                        </span>
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
                        Apakah Anda yakin ingin menghapus Budget Plan periode
                        <strong>
                            {{
                                planToDelete
                                    ? `${getMonthName(planToDelete.month)} ${planToDelete.year}`
                                    : ''
                            }} </strong
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
                        Hapus Budget Plan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

