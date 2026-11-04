<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Trash2,
    Plus,
    RotateCcw,
    ArrowUpCircle,
    ArrowDownCircle,
    Calculator,
    ArrowRight,
    ListFilter,
    CalendarClock,
    Search,
    Check,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import CurrencyInput from '@/components/CurrencyInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

interface CategoryOption {
    id: number;
    name: string;
    type: 'income' | 'expense';
    amount: string | number | null;
    description: string | null;
    is_default: boolean;
    order_no: number;
}

interface PlanRow {
    category_id: number | null;
    name: string;
    type: 'income' | 'expense';
    amount: number;
    description: string | null;
    is_manual?: boolean;
    is_recurring?: boolean;
    recurring_name?: string | null;
    recurring_info?: string | null;
}

interface RecurringExpenseOption {
    id: number;
    category_id: number;
    name: string | null;
    amount: number;
    start_month: number;
    start_year: number;
    end_month: number | null;
    end_year: number | null;
    is_active: boolean;
    description: string | null;
    paid_count?: number;
    total_duration?: number | null;
    remaining_count?: number | null;
    progress_percentage?: number | null;
    is_completed?: boolean;
}

const props = defineProps<{
    incomeCategories: CategoryOption[];
    expenseCategories: CategoryOption[];
    recurringExpenses?: RecurringExpenseOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Budget Plan', href: '/budget-plans' },
    { title: 'Buat Budget Plan', href: '/budget-plans/create' },
];

const months = [
    { value: '1', label: 'Januari' },
    { value: '2', label: 'Februari' },
    { value: '3', label: 'Maret' },
    { value: '4', label: 'April' },
    { value: '5', label: 'Mei' },
    { value: '6', label: 'Juni' },
    { value: '7', label: 'Juli' },
    { value: '8', label: 'Agustus' },
    { value: '9', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' },
];

const currentYear = new Date().getFullYear();
const yearOptions = Array.from({ length: 7 }, (_, i) =>
    String(currentYear - 2 + i),
);

const form = useForm({
    month: String(new Date().getMonth() + 1),
    year: String(currentYear),
    status: 'draft' as 'draft' | 'active' | 'closed',
    notes: '',
    items: [] as PlanRow[],
});

const getRecurringExpenseForCategory = (
    categoryId: number,
    month: number,
    year: number,
): RecurringExpenseOption | undefined => {
    if (!props.recurringExpenses) return undefined;

    const targetPeriod = year * 12 + month;

    return props.recurringExpenses.find((re) => {
        if (!re.is_active) return false;
        if (re.category_id !== categoryId) return false;

        const startPeriod = re.start_year * 12 + re.start_month;
        if (targetPeriod < startPeriod) return false;

        if (re.end_year && re.end_month) {
            const endPeriod = re.end_year * 12 + re.end_month;
            if (targetPeriod > endPeriod) return false;
        }

        return true;
    });
};

const buildDefaultRows = (categories: CategoryOption[]): PlanRow[] => {
    return categories
        .filter((cat) => cat.is_default)
        .map((cat) => ({
            category_id: cat.id,
            name: cat.name,
            type: cat.type,
            amount:
                cat.amount !== null && cat.amount !== undefined
                    ? Number(cat.amount)
                    : 0,
            description: cat.description,
            is_manual: false,
            is_recurring: false,
        }));
};

const buildExpenseRowsForPeriod = (month: number, year: number): PlanRow[] => {
    const rows: PlanRow[] = [];
    const usedCategoryIds = new Set<number>();

    props.expenseCategories.forEach((cat) => {
        const recurring = getRecurringExpenseForCategory(cat.id, month, year);
        const hasRecurring = !!recurring;
        const recurringInfo = hasRecurring && recurring.total_duration
            ? `Ke-${(recurring.paid_count ?? 0) + 1}/${recurring.total_duration}`
            : null;

        if (cat.is_default || hasRecurring) {
            rows.push({
                category_id: cat.id,
                name: cat.name,
                type: cat.type,
                amount: hasRecurring
                    ? Number(recurring.amount)
                    : (cat.amount !== null && cat.amount !== undefined ? Number(cat.amount) : 0),
                description: hasRecurring && recurring.name
                    ? recurring.name
                    : cat.description,
                is_manual: false,
                is_recurring: hasRecurring,
                recurring_name: recurring?.name,
                recurring_info: recurringInfo,
            });
            usedCategoryIds.add(cat.id);
        }
    });

    return rows;
};

const incomeRows = ref<PlanRow[]>(buildDefaultRows(props.incomeCategories));
const expenseRows = ref<PlanRow[]>(
    buildExpenseRowsForPeriod(Number(form.month), Number(form.year)),
);

watch(
    () => [form.month, form.year],
    ([newMonth, newYear]) => {
        const m = Number(newMonth);
        const y = Number(newYear);

        expenseRows.value.forEach((row) => {
            if (row.category_id && !row.is_manual) {
                const recurring = getRecurringExpenseForCategory(row.category_id, m, y);
                if (recurring) {
                    row.amount = Number(recurring.amount);
                    row.is_recurring = true;
                    row.recurring_name = recurring.name;
                    row.recurring_info = recurring.total_duration
                        ? `Ke-${(recurring.paid_count ?? 0) + 1}/${recurring.total_duration}`
                        : null;
                } else if (row.is_recurring) {
                    const cat = props.expenseCategories.find((c) => c.id === row.category_id);
                    row.amount = cat?.amount !== null && cat?.amount !== undefined ? Number(cat.amount) : 0;
                    row.is_recurring = false;
                    row.recurring_name = undefined;
                    row.recurring_info = null;
                }
            }
        });

        props.expenseCategories.forEach((cat) => {
            const recurring = getRecurringExpenseForCategory(cat.id, m, y);
            if (recurring) {
                const exists = expenseRows.value.some((r) => r.category_id === cat.id);
                if (!exists) {
                    expenseRows.value.push({
                        category_id: cat.id,
                        name: cat.name,
                        type: cat.type,
                        amount: Number(recurring.amount),
                        description: recurring.name || cat.description,
                        is_manual: false,
                        is_recurring: true,
                        recurring_name: recurring.name,
                        recurring_info: recurring.total_duration
                            ? `Ke-${(recurring.paid_count ?? 0) + 1}/${recurring.total_duration}`
                            : null,
                    });
                }
            }
        });
    },
);

const getAvailableIncomeCategories = (_currentRowCategoryId: number | null) => {
    return props.incomeCategories;
};

const getAvailableExpenseCategories = (currentRowCategoryId: number | null) => {
    const usedIds = new Set(
        expenseRows.value
            .map((r) => r.category_id)
            .filter((id): id is number => id !== null && id !== currentRowCategoryId),
    );
    return props.expenseCategories.filter((cat) => !usedIds.has(cat.id));
};

const handleSelectIncomeCategory = (row: PlanRow, value: string) => {
    if (value === '__manual__') {
        row.is_manual = true;
        row.category_id = null;
        row.name = '';
        row.amount = 0;
        return;
    }

    const selected = props.incomeCategories.find(
        (cat) => String(cat.id) === String(value),
    );
    if (selected) {
        row.is_manual = false;
        row.category_id = selected.id;
        row.name = selected.name;
        row.amount =
            selected.amount !== null && selected.amount !== undefined
                ? Number(selected.amount)
                : 0;
        row.description = selected.description;
    }
};

const handleSelectExpenseCategory = (row: PlanRow, value: string) => {
    if (value === '__manual__') {
        row.is_manual = true;
        row.category_id = null;
        row.name = '';
        row.amount = 0;
        row.is_recurring = false;
        row.recurring_name = undefined;
        row.recurring_info = null;
        return;
    }

    const selected = props.expenseCategories.find(
        (cat) => String(cat.id) === String(value),
    );
    if (selected) {
        row.is_manual = false;
        row.category_id = selected.id;
        row.name = selected.name;

        const recurring = getRecurringExpenseForCategory(
            selected.id,
            Number(form.month),
            Number(form.year),
        );

        if (recurring) {
            row.amount = Number(recurring.amount);
            row.is_recurring = true;
            row.recurring_name = recurring.name;
            row.recurring_info = recurring.total_duration
                ? `Ke-${(recurring.paid_count ?? 0) + 1}/${recurring.total_duration}`
                : null;
            row.description = recurring.name || selected.description;
        } else {
            row.amount =
                selected.amount !== null && selected.amount !== undefined
                    ? Number(selected.amount)
                    : 0;
            row.is_recurring = false;
            row.recurring_name = undefined;
            row.recurring_info = null;
            row.description = selected.description;
        }
    }
};

const switchToSelectMode = (row: PlanRow) => {
    row.is_manual = false;
    row.category_id = null;
    row.name = '';
    row.is_recurring = false;
    row.recurring_name = undefined;
    row.recurring_info = null;
};

const selectedMonthName = computed(() => {
    return (
        months.find((m) => m.value === String(form.month))?.label || 'Januari'
    );
});

const nextMonthPeriod = computed(() => {
    const m = Number(form.month);
    const y = Number(form.year);
    if (m === 12) {
        return {
            monthName: 'Januari',
            year: y + 1,
        };
    }
    const nextLabel =
        months.find((item) => Number(item.value) === m + 1)?.label ||
        'Februari';
    return {
        monthName: nextLabel,
        year: y,
    };
});

const removeIncomeRow = (index: number) => {
    incomeRows.value.splice(index, 1);
};

const removeExpenseRow = (index: number) => {
    expenseRows.value.splice(index, 1);
};

const activeTab = ref<'income' | 'expense'>('income');

const isAddModalOpen = ref(false);
const addModalType = ref<'income' | 'expense'>('income');
const addModalSearch = ref('');
const addModalSelectedId = ref<number | '__manual__' | null>(null);
const addModalManualName = ref('');
const addModalAmount = ref<number | string>(0);

const openAddModal = (type: 'income' | 'expense') => {
    addModalType.value = type;
    addModalSearch.value = '';
    addModalSelectedId.value = null;
    addModalManualName.value = '';
    addModalAmount.value = 0;
    isAddModalOpen.value = true;
};

const addIncomeRow = () => openAddModal('income');
const addExpenseRow = () => openAddModal('expense');

const filteredModalCategories = computed(() => {
    const list =
        addModalType.value === 'income'
            ? getAvailableIncomeCategories(null)
            : getAvailableExpenseCategories(null);
    const q = addModalSearch.value.trim().toLowerCase();
    if (!q) return list;
    return list.filter((c) => c.name.toLowerCase().includes(q));
});

const selectModalCategory = (cat: CategoryOption | '__manual__') => {
    if (cat === '__manual__') {
        addModalSelectedId.value = '__manual__';
        addModalManualName.value = addModalSearch.value.trim();
        addModalAmount.value = 0;
        return;
    }
    addModalSelectedId.value = cat.id;
    if (addModalType.value === 'expense') {
        const recurring = getRecurringExpenseForCategory(
            cat.id,
            Number(form.month),
            Number(form.year),
        );
        if (recurring) {
            addModalAmount.value = Number(recurring.amount);
            return;
        }
    }
    addModalAmount.value =
        cat.amount !== null && cat.amount !== undefined ? Number(cat.amount) : 0;
};

const canConfirmAdd = computed(() => {
    if (addModalSelectedId.value === '__manual__') {
        return addModalManualName.value.trim() !== '';
    }
    return addModalSelectedId.value !== null;
});

const confirmAddRow = () => {
    if (!canConfirmAdd.value) return;

    const amount = Number(addModalAmount.value) || 0;

    if (addModalSelectedId.value === '__manual__') {
        const row: PlanRow = {
            category_id: null,
            name: addModalManualName.value.trim(),
            type: addModalType.value,
            amount,
            description: null,
            is_manual: true,
        };
        if (addModalType.value === 'income') {
            incomeRows.value.push(row);
        } else {
            expenseRows.value.push(row);
        }
        isAddModalOpen.value = false;
        return;
    }

    const list =
        addModalType.value === 'income'
            ? props.incomeCategories
            : props.expenseCategories;
    const selected = list.find((c) => c.id === addModalSelectedId.value);
    if (!selected) return;

    if (addModalType.value === 'income') {
        incomeRows.value.push({
            category_id: selected.id,
            name: selected.name,
            type: 'income',
            amount,
            description: selected.description,
            is_manual: false,
        });
    } else {
        const recurring = getRecurringExpenseForCategory(
            selected.id,
            Number(form.month),
            Number(form.year),
        );
        expenseRows.value.push({
            category_id: selected.id,
            name: selected.name,
            type: 'expense',
            amount,
            description:
                recurring && recurring.name
                    ? recurring.name
                    : selected.description,
            is_manual: false,
            is_recurring: !!recurring,
            recurring_name: recurring?.name,
            recurring_info:
                recurring && recurring.total_duration
                    ? `Ke-${(recurring.paid_count ?? 0) + 1}/${recurring.total_duration}`
                    : null,
        });
    }

    isAddModalOpen.value = false;
};

const resetIncomeRows = () => {
    incomeRows.value = buildDefaultRows(props.incomeCategories);
};

const resetExpenseRows = () => {
    expenseRows.value = buildExpenseRowsForPeriod(
        Number(form.month),
        Number(form.year),
    );
};

const totalIncome = computed(() => {
    return incomeRows.value.reduce(
        (sum, item) => sum + (Number(item.amount) || 0),
        0,
    );
});

const totalOvertime = computed(() => {
    return incomeRows.value.reduce((sum, item) => {
        if (item.name.toLowerCase().includes('lembur')) {
            return sum + (Number(item.amount) || 0);
        }
        return sum;
    }, 0);
});

const totalExpense = computed(() => {
    return expenseRows.value.reduce(
        (sum, item) => sum + (Number(item.amount) || 0),
        0,
    );
});

const remainingBalance = computed(() => {
    return totalIncome.value - totalExpense.value;
});

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount || 0);
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        month: Number(data.month),
        year: Number(data.year),
        items: [
            ...incomeRows.value.filter((r) => r.name.trim() !== ''),
            ...expenseRows.value.filter((r) => r.name.trim() !== ''),
        ],
    })).post('/budget-plans');
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head title="Buat Budget Plan" />

        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <div
            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Buat Budget Plan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Pilih bulan &amp; tahun pemasukan, sesuaikan daftar
                    pemasukan dan pengeluaran untuk bulan berikutnya.
                </p>
            </div>

            <Button variant="outline" as-child>
                <Link href="/budget-plans">Kembali ke Daftar</Link>
            </Button>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <!-- KOLOM 1: Periode & Status + Ringkasan -->
            <div class="flex flex-col gap-4 lg:sticky lg:top-6 lg:col-span-5 xl:col-span-4">
                <!-- Card Periode, Status, Catatan -->
                <Card class="gap-3 py-4">
                    <CardHeader class="px-4 pb-0">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <CardTitle class="text-sm font-semibold">
                                Periode &amp; Status
                            </CardTitle>
                            <span class="flex items-center gap-1 text-xs text-muted-foreground">
                                <strong>{{ selectedMonthName }} {{ form.year }}</strong>
                                <ArrowRight class="h-3 w-3" />
                                <strong>{{ nextMonthPeriod.monthName }} {{ nextMonthPeriod.year }}</strong>
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-3 px-4">
                        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                            <div class="grid gap-1">
                                <Label for="month" class="text-xs">Bulan</Label>
                                <Select v-model="form.month">
                                    <SelectTrigger id="month" class="h-8 w-full text-xs">
                                        <SelectValue placeholder="Pilih bulan" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="m in months"
                                            :key="m.value"
                                            :value="m.value"
                                        >
                                            {{ m.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.month" class="text-xs text-destructive">
                                    {{ form.errors.month }}
                                </p>
                            </div>

                            <div class="grid gap-1">
                                <Label for="year" class="text-xs">Tahun</Label>
                                <Select v-model="form.year">
                                    <SelectTrigger id="year" class="h-8 w-full text-xs">
                                        <SelectValue placeholder="Pilih tahun" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="y in yearOptions"
                                            :key="y"
                                            :value="y"
                                        >
                                            {{ y }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.year" class="text-xs text-destructive">
                                    {{ form.errors.year }}
                                </p>
                            </div>

                            <div class="grid gap-1">
                                <Label for="status" class="text-xs">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger id="status" class="h-8 w-full text-xs">
                                        <SelectValue placeholder="Pilih status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="active">Aktif</SelectItem>
                                        <SelectItem value="closed">Selesai</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status" class="text-xs text-destructive">
                                    {{ form.errors.status }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <Label for="notes" class="text-xs">Catatan (Opsional)</Label>
                            <Input
                                id="notes"
                                v-model="form.notes"
                                placeholder="Tambahkan catatan singkat..."
                                class="h-8 text-xs"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Card Ringkasan Budget Plan (Compact) -->
                <Card class="gap-3 py-4">
                    <CardHeader class="px-4 pb-0">
                        <div class="flex items-center gap-2">
                            <Calculator class="h-4 w-4 text-primary" />
                            <CardTitle class="text-sm font-semibold">
                                Ringkasan Budget Plan
                            </CardTitle>
                        </div>
                    </CardHeader>

                    <CardContent class="grid gap-3 px-4">
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="rounded-lg border bg-emerald-500/5 p-2.5">
                                <p class="truncate text-[11px] text-muted-foreground">
                                    Pemasukan ({{ incomeRows.length }})
                                </p>
                                <p class="mt-0.5 font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ formatCurrency(totalIncome) }}
                                </p>
                            </div>

                            <div class="rounded-lg border bg-amber-500/5 p-2.5">
                                <p class="truncate text-[11px] text-muted-foreground">
                                    Total Lembur
                                </p>
                                <p class="mt-0.5 font-mono text-sm font-bold text-amber-600 dark:text-amber-400">
                                    {{ formatCurrency(totalOvertime) }}
                                </p>
                            </div>

                            <div class="rounded-lg border bg-rose-500/5 p-2.5">
                                <p class="truncate text-[11px] text-muted-foreground">
                                    Pengeluaran ({{ expenseRows.length }})
                                </p>
                                <p class="mt-0.5 font-mono text-sm font-bold text-rose-600 dark:text-rose-400">
                                    {{ formatCurrency(totalExpense) }}
                                </p>
                            </div>

                            <div
                                class="rounded-lg border p-2.5"
                                :class="
                                    remainingBalance >= 0
                                        ? 'border-primary/30 bg-primary/5'
                                        : 'border-destructive/40 bg-destructive/10'
                                "
                            >
                                <p class="truncate text-[11px] font-medium text-foreground">
                                    Sisa Anggaran
                                </p>
                                <p
                                    class="mt-0.5 font-mono text-sm font-extrabold"
                                    :class="
                                        remainingBalance >= 0
                                            ? 'text-primary'
                                            : 'text-destructive'
                                    "
                                >
                                    {{ formatCurrency(remainingBalance) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-1">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-8 px-3 text-xs"
                                as-child
                            >
                                <Link href="/budget-plans">Batal</Link>
                            </Button>
                            <Button
                                type="submit"
                                size="sm"
                                class="h-8 flex-1 text-xs"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Budget Plan' }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- KOLOM 2: Tabs Pemasukan & Pengeluaran -->
            <Card class="gap-0 overflow-hidden py-0 lg:col-span-7 xl:col-span-8">
                <CardHeader class="flex flex-col gap-3 border-b bg-muted/20 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Tab Buttons -->
                    <div class="inline-flex rounded-lg border bg-muted p-1">
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-md px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="
                                activeTab === 'income'
                                    ? 'bg-background text-emerald-600 shadow-xs dark:text-emerald-400'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            @click="activeTab = 'income'"
                        >
                            <ArrowUpCircle class="h-3.5 w-3.5" />
                            <span>Pemasukan ({{ selectedMonthName }})</span>
                            <Badge variant="secondary" class="h-4 px-1.5 text-[10px]">
                                {{ incomeRows.length }}
                            </Badge>
                        </button>
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-md px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="
                                activeTab === 'expense'
                                    ? 'bg-background text-rose-600 shadow-xs dark:text-rose-400'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            @click="activeTab = 'expense'"
                        >
                            <ArrowDownCircle class="h-3.5 w-3.5" />
                            <span>Pengeluaran ({{ nextMonthPeriod.monthName }})</span>
                            <Badge variant="secondary" class="h-4 px-1.5 text-[10px]">
                                {{ expenseRows.length }}
                            </Badge>
                        </button>
                    </div>

                    <!-- Actions for active tab -->
                    <div class="flex items-center gap-1.5">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8"
                            title="Reset ke Kategori Default"
                            @click="activeTab === 'income' ? resetIncomeRows() : resetExpenseRows()"
                        >
                            <RotateCcw class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-8 px-2.5 text-xs"
                            @click="activeTab === 'income' ? addIncomeRow() : addExpenseRow()"
                        >
                            <Plus class="mr-1 h-3.5 w-3.5" />
                            Tambah {{ activeTab === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                        </Button>
                    </div>
                </CardHeader>

                <!-- TAB PEMASUKAN -->
                <CardContent v-if="activeTab === 'income'" class="p-0">
                    <div class="max-h-[calc(100vh-240px)] overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead
                                    class="sticky top-0 z-10 bg-muted text-xs text-muted-foreground uppercase"
                                >
                                    <tr class="border-b">
                                        <th
                                            class="px-3 py-2.5 text-left font-semibold"
                                        >
                                            Kategori
                                        </th>
                                        <th
                                            class="w-40 px-3 py-2.5 text-right font-semibold"
                                        >
                                            Nominal (Rp)
                                        </th>
                                        <th
                                            class="w-10 px-2 py-2.5 text-center font-semibold"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr
                                        v-for="(row, idx) in incomeRows"
                                        :key="idx"
                                        class="hover:bg-muted/30"
                                    >
                                        <td class="p-2.5 align-middle">
                                            <!-- Mode Manual (Ketik Kategori Baru) -->
                                            <div
                                                v-if="row.is_manual"
                                                class="flex items-center gap-1"
                                            >
                                                <Input
                                                    v-model="row.name"
                                                    placeholder="Ketik kategori baru..."
                                                    class="h-8 text-xs"
                                                />
                                                <Button
                                                    v-if="
                                                        getAvailableIncomeCategories(
                                                            row.category_id,
                                                        ).length > 0
                                                    "
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8 shrink-0 text-muted-foreground"
                                                    title="Pilih dari kategori yang ada"
                                                    @click="
                                                        switchToSelectMode(row)
                                                    "
                                                >
                                                    <ListFilter
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                            </div>

                                            <!-- Mode Pilih dari Tabel Kategori -->
                                            <Select
                                                v-else
                                                :model-value="
                                                    row.category_id
                                                        ? String(
                                                              row.category_id,
                                                          )
                                                        : ''
                                                "
                                                @update:model-value="
                                                    (val) =>
                                                        handleSelectIncomeCategory(
                                                            row,
                                                            String(val),
                                                        )
                                                "
                                            >
                                                <SelectTrigger
                                                    class="h-8 w-full text-xs"
                                                >
                                                    <SelectValue
                                                        placeholder="Pilih kategori / buat baru..."
                                                    />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem
                                                        v-for="cat in getAvailableIncomeCategories(
                                                            row.category_id,
                                                        )"
                                                        :key="cat.id"
                                                        :value="String(cat.id)"
                                                    >
                                                        {{ cat.name }}
                                                    </SelectItem>
                                                    <SelectItem
                                                        value="__manual__"
                                                        class="font-medium text-primary"
                                                    >
                                                        + Ketik Manual (Kategori
                                                        Baru)
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </td>
                                        <td class="p-2.5 align-middle">
                                            <CurrencyInput
                                                v-model="row.amount"
                                                :show-prefix="false"
                                                placeholder="0"
                                                class="h-8 text-right font-mono text-xs"
                                            />
                                        </td>
                                        <td
                                            class="p-2 text-center align-middle"
                                        >
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7 text-muted-foreground hover:text-destructive"
                                                title="Hapus baris"
                                                @click="removeIncomeRow(idx)"
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </td>
                                    </tr>
                                    <tr v-if="incomeRows.length === 0">
                                        <td
                                            colspan="3"
                                            class="p-6 text-center text-xs text-muted-foreground"
                                        >
                                            Tidak ada item pemasukan. Klik "+
                                            Baris" untuk menambahkan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>

                <!-- TAB PENGELUARAN -->
                <CardContent v-else class="p-0">
                    <div class="max-h-[calc(100vh-240px)] overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead
                                    class="sticky top-0 z-10 bg-muted text-xs text-muted-foreground uppercase"
                                >
                                    <tr class="border-b">
                                        <th
                                            class="px-3 py-2.5 text-left font-semibold"
                                        >
                                            Kategori
                                        </th>
                                        <th
                                            class="w-40 px-3 py-2.5 text-right font-semibold"
                                        >
                                            Nominal (Rp)
                                        </th>
                                        <th
                                            class="w-10 px-2 py-2.5 text-center font-semibold"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr
                                        v-for="(row, idx) in expenseRows"
                                        :key="idx"
                                        class="hover:bg-muted/30"
                                    >
                                        <td class="p-2.5 align-middle">
                                            <!-- Mode Manual (Ketik Kategori Baru) -->
                                            <div
                                                v-if="row.is_manual"
                                                class="flex items-center gap-1"
                                            >
                                                <Input
                                                    v-model="row.name"
                                                    placeholder="Ketik kategori baru..."
                                                    class="h-8 text-xs"
                                                />
                                                <Button
                                                    v-if="
                                                        getAvailableExpenseCategories(
                                                            row.category_id,
                                                        ).length > 0
                                                    "
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8 shrink-0 text-muted-foreground"
                                                    title="Pilih dari kategori yang ada"
                                                    @click="
                                                        switchToSelectMode(row)
                                                    "
                                                >
                                                    <ListFilter
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                            </div>

                                            <!-- Mode Pilih dari Tabel Kategori -->
                                            <div v-else class="flex items-center gap-1.5">
                                                <div class="flex-1">
                                                    <Select
                                                        :model-value="
                                                            row.category_id
                                                                ? String(
                                                                      row.category_id,
                                                                  )
                                                                : ''
                                                        "
                                                        @update:model-value="
                                                            (val) =>
                                                                handleSelectExpenseCategory(
                                                                    row,
                                                                    String(val),
                                                                )
                                                        "
                                                    >
                                                        <SelectTrigger
                                                            class="h-8 w-full text-xs"
                                                        >
                                                            <SelectValue
                                                                placeholder="Pilih kategori / buat baru..."
                                                            />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem
                                                                v-for="cat in getAvailableExpenseCategories(
                                                                    row.category_id,
                                                                )"
                                                                :key="cat.id"
                                                                :value="String(cat.id)"
                                                            >
                                                                {{ cat.name }}
                                                            </SelectItem>
                                                            <SelectItem
                                                                value="__manual__"
                                                                class="font-medium text-primary"
                                                            >
                                                                + Ketik Manual (Kategori
                                                                Baru)
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                                <Badge
                                                    v-if="row.is_recurring"
                                                    variant="outline"
                                                    class="h-6 shrink-0 px-1.5 text-[10px] bg-rose-500/10 text-rose-600 border-rose-500/30 gap-1 font-medium"
                                                    :title="row.recurring_info ? `Nominal otomatis dari Pengeluaran Rutin (${row.recurring_info})` : 'Nominal otomatis dari Pengeluaran Rutin'"
                                                >
                                                    <CalendarClock class="h-3 w-3" />
                                                    <span>Rutin {{ row.recurring_info ? `(${row.recurring_info})` : '' }}</span>
                                                </Badge>
                                            </div>
                                        </td>
                                        <td class="p-2.5 align-middle">
                                            <CurrencyInput
                                                v-model="row.amount"
                                                :show-prefix="false"
                                                placeholder="0"
                                                class="h-8 text-right font-mono text-xs"
                                            />
                                        </td>
                                        <td
                                            class="p-2 text-center align-middle"
                                        >
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7 text-muted-foreground hover:text-destructive"
                                                title="Hapus baris"
                                                @click="removeExpenseRow(idx)"
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </td>
                                    </tr>
                                    <tr v-if="expenseRows.length === 0">
                                        <td
                                            colspan="3"
                                            class="p-6 text-center text-xs text-muted-foreground"
                                        >
                                            Tidak ada item pengeluaran. Klik "+
                                            Baris" untuk menambahkan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
            </Card>
        </form>

        <!-- Modal Tambah Baris -->
        <Dialog :open="isAddModalOpen" @update:open="isAddModalOpen = $event">
            <DialogContent class="sm:max-w-[460px]">
                <DialogHeader>
                    <DialogTitle>
                        Tambah Baris {{ addModalType === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                    </DialogTitle>
                    <DialogDescription>
                        Cari dan pilih kategori atau buat manual, lalu tentukan nominalnya.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-3 py-1">
                    <div class="relative">
                        <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="addModalSearch"
                            type="search"
                            placeholder="Cari kategori..."
                            class="h-8 pl-8 text-xs"
                        />
                    </div>

                    <div class="max-h-52 divide-y overflow-y-auto rounded-md border">
                        <button
                            v-for="cat in filteredModalCategories"
                            :key="cat.id"
                            type="button"
                            class="flex w-full items-center justify-between px-3 py-2 text-left text-xs transition-colors hover:bg-muted/60"
                            :class="addModalSelectedId === cat.id ? 'bg-primary/10 font-semibold text-primary' : ''"
                            @click="selectModalCategory(cat)"
                        >
                            <span class="truncate">{{ cat.name }}</span>
                            <span class="ml-2 flex shrink-0 items-center gap-1.5 font-mono text-[11px] text-muted-foreground">
                                <template v-if="cat.amount !== null && cat.amount !== undefined">
                                    {{ formatCurrency(Number(cat.amount)) }}
                                </template>
                                <Check v-if="addModalSelectedId === cat.id" class="h-3.5 w-3.5 text-primary" />
                            </span>
                        </button>

                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-3 py-2 text-left text-xs font-medium text-primary transition-colors hover:bg-muted/60"
                            :class="addModalSelectedId === '__manual__' ? 'bg-primary/10' : ''"
                            @click="selectModalCategory('__manual__')"
                        >
                            <span>+ Ketik kategori baru secara manual</span>
                            <Check v-if="addModalSelectedId === '__manual__'" class="h-3.5 w-3.5 text-primary" />
                        </button>
                    </div>

                    <div v-if="addModalSelectedId === '__manual__'" class="grid gap-1">
                        <Label for="modal_manual_name" class="text-xs">Nama Kategori Baru</Label>
                        <Input
                            id="modal_manual_name"
                            v-model="addModalManualName"
                            placeholder="Contoh: Bonus Proyek, Servis Laptop..."
                            class="h-8 text-xs"
                        />
                    </div>

                    <div class="grid gap-1">
                        <Label for="modal_amount" class="text-xs">Nominal (Rp)</Label>
                        <CurrencyInput
                            id="modal_amount"
                            v-model="addModalAmount"
                            placeholder="0"
                            class="h-8 text-xs"
                        />
                    </div>
                </div>

                <DialogFooter class="gap-2 sm:gap-2">
                    <Button type="button" variant="outline" size="sm" @click="isAddModalOpen = false">
                        Batal
                    </Button>
                    <Button type="button" size="sm" :disabled="!canConfirmAdd" @click="confirmAddRow">
                        Tambah Baris
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
