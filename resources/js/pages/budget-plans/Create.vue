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
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import CurrencyInput from '@/components/CurrencyInput.vue';
import { Button } from '@/components/ui/button';
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

const getAvailableIncomeCategories = (currentRowCategoryId: number | null) => {
    const usedIds = new Set(
        incomeRows.value
            .map((r) => r.category_id)
            .filter((id): id is number => id !== null && id !== currentRowCategoryId),
    );
    return props.incomeCategories.filter((cat) => !usedIds.has(cat.id));
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

const addIncomeRow = () => {
    const available = getAvailableIncomeCategories(null);
    incomeRows.value.push({
        category_id: null,
        name: '',
        type: 'income',
        amount: 0,
        description: null,
        is_manual: available.length === 0,
    });
};

const addExpenseRow = () => {
    const available = getAvailableExpenseCategories(null);
    expenseRows.value.push({
        category_id: null,
        name: '',
        type: 'expense',
        amount: 0,
        description: null,
        is_manual: available.length === 0,
    });
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

        <form @submit.prevent="submit" class="grid gap-6">
            <!-- Bagian Atas: Pilih Bulan, Tahun, dan Status -->
            <Card>
                <CardHeader class="pb-4">
                    <CardTitle class="text-base">
                        Periode &amp; Status Budget Plan
                    </CardTitle>
                    <CardDescription>
                        Pemasukan dihitung untuk bulan yang dipilih (<strong
                            >{{ selectedMonthName }} {{ form.year }}</strong
                        >), sedangkan pengeluaran dialokasikan untuk bulan
                        berikutnya (<strong
                            >{{ nextMonthPeriod.monthName }}
                            {{ nextMonthPeriod.year }}</strong
                        >).
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="month">Bulan Pemasukan</Label>
                            <Select v-model="form.month">
                                <SelectTrigger id="month" class="w-full">
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
                            <p
                                v-if="form.errors.month"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.month }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="year">Tahun</Label>
                            <Select v-model="form.year">
                                <SelectTrigger id="year" class="w-full">
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
                            <p
                                v-if="form.errors.year"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.year }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="status">Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger id="status" class="w-full">
                                    <SelectValue placeholder="Pilih status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="active">Aktif</SelectItem>
                                    <SelectItem value="closed">
                                        Selesai / Ditutup
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="form.errors.status"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.status }}
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                * Status awal adalah <strong>Draft</strong>. Hanya ada 1 Budget Plan berstatus <strong>Aktif</strong> (jika memilih Aktif, plan aktif lain otomatis menjadi Draft).
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Bagian Bawah: 3 Kolom (Pemasukan | Pengeluaran Bulan Depan | Ringkasan) -->
            <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
                <!-- KOLOM 1: Tabel Pemasukan -->
                <Card class="border-emerald-500/20">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 border-b pb-4"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <ArrowUpCircle
                                    class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                                />
                                <CardTitle class="text-base">
                                    Pemasukan
                                </CardTitle>
                                <Badge
                                    variant="default"
                                    class="bg-emerald-600 text-[11px] hover:bg-emerald-700"
                                >
                                    {{ selectedMonthName }} {{ form.year }}
                                </Badge>
                            </div>
                            <CardDescription class="text-xs">
                                Diambil dari kategori default (urut Order No)
                            </CardDescription>
                        </div>

                        <div class="flex items-center gap-1">
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8"
                                title="Reset ke Kategori Default"
                                @click="resetIncomeRows"
                            >
                                <RotateCcw class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-8 px-2 text-xs"
                                @click="addIncomeRow"
                            >
                                <Plus class="mr-1 h-3.5 w-3.5" />
                                Baris
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead
                                    class="bg-muted/50 text-xs text-muted-foreground uppercase"
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
                </Card>

                <!-- KOLOM 2: Tabel Pengeluaran (Bulan Depan) -->
                <Card class="border-rose-500/20">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 border-b pb-4"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <ArrowDownCircle
                                    class="h-4 w-4 text-rose-600 dark:text-rose-400"
                                />
                                <CardTitle class="text-base">
                                    Pengeluaran
                                </CardTitle>
                                <Badge
                                    variant="destructive"
                                    class="text-[11px]"
                                >
                                    {{ nextMonthPeriod.monthName }}
                                    {{ nextMonthPeriod.year }}
                                </Badge>
                            </div>
                            <CardDescription class="text-xs">
                                Alokasi pengeluaran untuk bulan depan
                            </CardDescription>
                        </div>

                        <div class="flex items-center gap-1">
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8"
                                title="Reset ke Kategori Default"
                                @click="resetExpenseRows"
                            >
                                <RotateCcw class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-8 px-2 text-xs"
                                @click="addExpenseRow"
                            >
                                <Plus class="mr-1 h-3.5 w-3.5" />
                                Baris
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead
                                    class="bg-muted/50 text-xs text-muted-foreground uppercase"
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

                <!-- KOLOM 3: Informasi Ringkasan -->
                <Card class="sticky top-6">
                    <CardHeader class="border-b pb-4">
                        <div class="flex items-center gap-2">
                            <Calculator class="h-4 w-4 text-primary" />
                            <CardTitle class="text-base">
                                Ringkasan Budget Plan
                            </CardTitle>
                        </div>
                        <CardDescription
                            class="flex items-center gap-1.5 text-xs"
                        >
                            <span>{{ selectedMonthName }} {{ form.year }}</span>
                            <ArrowRight class="h-3 w-3" />
                            <span>
                                {{ nextMonthPeriod.monthName }}
                                {{ nextMonthPeriod.year }}
                            </span>
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="grid gap-4 pt-5">
                        <div
                            class="flex items-center justify-between rounded-lg border bg-emerald-500/5 p-3.5"
                        >
                            <div class="space-y-0.5">
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Total Pemasukan ({{ selectedMonthName }})
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    {{ incomeRows.length }} item pemasukan
                                </p>
                            </div>
                            <span
                                class="font-mono text-base font-bold text-emerald-600 dark:text-emerald-400"
                            >
                                {{ formatCurrency(totalIncome) }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border bg-amber-500/5 p-3.5"
                        >
                            <div class="space-y-0.5">
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Total Lembur
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    Dari kategori berlabel "Lembur"
                                </p>
                            </div>
                            <span
                                class="font-mono text-base font-bold text-amber-600 dark:text-amber-400"
                            >
                                {{ formatCurrency(totalOvertime) }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border bg-rose-500/5 p-3.5"
                        >
                            <div class="space-y-0.5">
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Total Pengeluaran ({{
                                        nextMonthPeriod.monthName
                                    }})
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    {{ expenseRows.length }} item pengeluaran
                                </p>
                            </div>
                            <span
                                class="font-mono text-base font-bold text-rose-600 dark:text-rose-400"
                            >
                                {{ formatCurrency(totalExpense) }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-xl border-2 p-4"
                            :class="
                                remainingBalance >= 0
                                    ? 'border-primary/30 bg-primary/5'
                                    : 'border-destructive/40 bg-destructive/10'
                            "
                        >
                            <div class="space-y-0.5">
                                <p class="text-sm font-semibold text-foreground">
                                    Sisa Anggaran
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Total Pemasukan - Total Pengeluaran
                                </p>
                            </div>
                            <span
                                class="font-mono text-lg font-extrabold"
                                :class="
                                    remainingBalance >= 0
                                        ? 'text-primary'
                                        : 'text-destructive'
                                "
                            >
                                {{ formatCurrency(remainingBalance) }}
                            </span>
                        </div>

                        <div class="grid gap-2 pt-2">
                            <Label for="notes" class="text-xs">
                                Catatan (Opsional)
                            </Label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="2"
                                placeholder="Tambahkan catatan untuk budget plan ini..."
                                class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-xs shadow-xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                            />
                        </div>

                        <div class="flex flex-col gap-2 pt-2">
                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="form.processing"
                            >
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Budget Plan'
                                }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                class="w-full"
                                as-child
                            >
                                <Link href="/budget-plans">Batal</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </form>
    </div>
</template>
