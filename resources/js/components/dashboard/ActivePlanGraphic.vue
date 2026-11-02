<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowUpCircle,
    ArrowDownCircle,
    Wallet,
    Clock,
    PieChart,
    Layers,
    Plus,
    CalendarX2,
    ChevronDown,
    ChevronUp,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
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

interface ExpenseItem {
    id: number;
    name: string;
    amount: number;
    percentage: number;
}

interface IncomeItem {
    id: number;
    name: string;
    amount: number;
    percentage: number;
}

export interface ActivePlanData {
    id: number;
    month: number;
    year: number;
    status: 'draft' | 'active' | 'closed';
    notes: string | null;
    total_income: number;
    total_expense: number;
    balance: number;
    total_overtime: number;
    expense_ratio: number;
    balance_ratio: number;
    expense_items: ExpenseItem[];
    income_items: IncomeItem[];
}

const props = defineProps<{
    plan: ActivePlanData | null;
    selectedMonth: number;
    selectedYear: number;
    availableYears: number[];
}>();

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

const currentSelectedMonthName = computed(() => {
    return (
        months.find((m) => m.value === Number(props.selectedMonth))?.label ||
        'Januari'
    );
});

const onMonthChange = (month: string) => {
    router.get(
        '/dashboard',
        { month, year: String(props.selectedYear) },
        { preserveState: true, preserveScroll: true },
    );
};

const onYearChange = (year: string) => {
    router.get(
        '/dashboard',
        { month: String(props.selectedMonth), year },
        { preserveState: true, preserveScroll: true },
    );
};

const formatCurrency = (val: number | string | null | undefined): string => {
    const num = Number(val || 0);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number.isNaN(num) ? 0 : num);
};

// SVG Donut calculation
const radius = 54;
const circumference = 2 * Math.PI * radius; // ~339.29

const expensePercent = computed(() => {
    if (!props.plan || props.plan.total_income <= 0) {
        return props.plan && props.plan.total_expense > 0 ? 100 : 0;
    }
    return Math.min(
        Math.max((props.plan.total_expense / props.plan.total_income) * 100, 0),
        100,
    );
});

const balancePercent = computed(() => {
    return Math.max(100 - expensePercent.value, 0);
});

const expenseStrokeDasharray = computed(() => {
    const fill = (expensePercent.value / 100) * circumference;
    return `${fill} ${circumference}`;
});

const balanceStrokeDasharray = computed(() => {
    const fill = (balancePercent.value / 100) * circumference;
    return `${fill} ${circumference}`;
});

const balanceStrokeDashoffset = computed(() => {
    const expenseFill = (expensePercent.value / 100) * circumference;
    return `-${expenseFill}`;
});

// State to toggle between top 5 and all expenses
const showAllExpenses = ref(false);

watch(
    () => [props.selectedMonth, props.selectedYear],
    () => {
        showAllExpenses.value = false;
    },
);

const displayedExpenses = computed(() => {
    if (!props.plan) return [];
    return showAllExpenses.value
        ? props.plan.expense_items
        : props.plan.expense_items.slice(0, 5);
});
</script>

<template>
    <div class="flex flex-col gap-4">
        <!-- Header: Judul & Filter Bulan + Tahun -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                Budget Plan
            </h2>

            <!-- Filter Bulan & Tahun -->
            <div class="flex flex-wrap items-center gap-2">
                <Select
                    :model-value="String(selectedMonth)"
                    @update:model-value="(val) => onMonthChange(String(val))"
                >
                    <SelectTrigger class="h-8 w-36 text-xs">
                        <SelectValue placeholder="Pilih Bulan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="m in months"
                            :key="m.value"
                            :value="String(m.value)"
                        >
                            {{ m.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select
                    :model-value="String(selectedYear)"
                    @update:model-value="(val) => onYearChange(String(val))"
                >
                    <SelectTrigger class="h-8 w-28 text-xs">
                        <SelectValue placeholder="Pilih Tahun" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="y in availableYears"
                            :key="y"
                            :value="String(y)"
                        >
                            {{ y }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <!-- Jika Ada Data Plan -->
        <template v-if="plan">
            <!-- 4 KPI Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Pemasukan -->
                <Card class="border-emerald-500/20 bg-gradient-to-br from-emerald-500/5 via-card to-card">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-muted-foreground">Total Pemasukan</span>
                            <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400">
                                <ArrowUpCircle class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="mt-2 space-y-0.5">
                            <p class="font-mono text-xl font-bold text-foreground">
                                {{ formatCurrency(plan.total_income) }}
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                {{ plan.income_items.length }} item pemasukan
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pengeluaran -->
                <Card class="border-rose-500/20 bg-gradient-to-br from-rose-500/5 via-card to-card">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-muted-foreground">Total Pengeluaran</span>
                            <div class="rounded-lg bg-rose-500/10 p-2 text-rose-600 dark:text-rose-400">
                                <ArrowDownCircle class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="mt-2 space-y-0.5">
                            <p class="font-mono text-xl font-bold text-rose-600 dark:text-rose-400">
                                {{ formatCurrency(plan.total_expense) }}
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                {{ plan.expense_ratio }}% dari total pemasukan
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Sisa / Tabungan -->
                <Card
                    class="bg-gradient-to-br via-card to-card"
                    :class="
                        plan.balance >= 0
                            ? 'border-primary/20 from-primary/5'
                            : 'border-destructive/30 from-destructive/5'
                    "
                >
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-muted-foreground">Sisa Anggaran</span>
                            <div
                                class="rounded-lg p-2"
                                :class="
                                    plan.balance >= 0
                                        ? 'bg-primary/10 text-primary'
                                        : 'bg-destructive/10 text-destructive'
                                "
                            >
                                <Wallet class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="mt-2 space-y-0.5">
                            <p
                                class="font-mono text-xl font-bold"
                                :class="plan.balance >= 0 ? 'text-primary' : 'text-destructive'"
                            >
                                {{ formatCurrency(plan.balance) }}
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                {{ plan.balance_ratio }}% sisa tersimpan
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Lembur -->
                <Card class="border-amber-500/20 bg-gradient-to-br from-amber-500/5 via-card to-card">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-muted-foreground">Total Lembur</span>
                            <div class="rounded-lg bg-amber-500/10 p-2 text-amber-600 dark:text-amber-400">
                                <Clock class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="mt-2 space-y-0.5">
                            <p class="font-mono text-xl font-bold text-amber-600 dark:text-amber-400">
                                {{ formatCurrency(plan.total_overtime) }}
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                Terhitung di pemasukan
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- 2 Graphic Cards: Donut Ratio & Top Expense Breakdown -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                <!-- Donut Chart: Alokasi Pengeluaran vs Sisa -->
                <Card class="lg:col-span-5">
                    <CardHeader class="pb-2">
                        <div class="flex items-center gap-2">
                            <PieChart class="h-4 w-4 text-primary" />
                            <CardTitle class="text-base">Grafik Alokasi Dana</CardTitle>
                        </div>
                        <CardDescription class="text-xs">
                            Rasio pengeluaran vs sisa anggaran bulan ini
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-2">
                        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row sm:justify-around">
                            <!-- SVG Donut -->
                            <div class="relative flex items-center justify-center">
                                <svg class="h-36 w-36 -rotate-90 transform" viewBox="0 0 128 128">
                                    <circle
                                        cx="64"
                                        cy="64"
                                        :r="radius"
                                        fill="transparent"
                                        stroke="currentColor"
                                        stroke-width="12"
                                        class="text-muted/30"
                                    />
                                    <circle
                                        cx="64"
                                        cy="64"
                                        :r="radius"
                                        fill="transparent"
                                        stroke="#f43f5e"
                                        stroke-width="12"
                                        stroke-linecap="round"
                                        :stroke-dasharray="expenseStrokeDasharray"
                                        stroke-dashoffset="0"
                                        class="transition-all duration-700 ease-out"
                                    />
                                    <circle
                                        v-if="plan.balance > 0"
                                        cx="64"
                                        cy="64"
                                        :r="radius"
                                        fill="transparent"
                                        stroke="#10b981"
                                        stroke-width="12"
                                        stroke-linecap="round"
                                        :stroke-dasharray="balanceStrokeDasharray"
                                        :stroke-dashoffset="balanceStrokeDashoffset"
                                        class="transition-all duration-700 ease-out"
                                    />
                                </svg>
                                <div class="absolute flex flex-col items-center justify-center text-center">
                                    <span class="font-mono text-xl font-extrabold text-foreground">
                                        {{ Math.round(expensePercent) }}%
                                    </span>
                                    <span class="text-[10px] text-muted-foreground uppercase tracking-wider">
                                        Terpakai
                                    </span>
                                </div>
                            </div>

                            <!-- Legend Details -->
                            <div class="flex flex-col gap-2.5 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-rose-500 shrink-0"></span>
                                    <div>
                                        <p class="font-medium text-foreground">Pengeluaran</p>
                                        <p class="font-mono text-muted-foreground">
                                            {{ formatCurrency(plan.total_expense) }} ({{ plan.expense_ratio }}%)
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-emerald-500 shrink-0"></span>
                                    <div>
                                        <p class="font-medium text-foreground">Sisa / Tabungan</p>
                                        <p class="font-mono text-muted-foreground">
                                            {{ formatCurrency(plan.balance) }} ({{ plan.balance_ratio }}%)
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 pt-1 border-t border-border/50">
                                    <span class="h-3 w-3 rounded-full bg-amber-500 shrink-0"></span>
                                    <div>
                                        <p class="font-medium text-foreground">Komponen Lembur</p>
                                        <p class="font-mono text-muted-foreground">
                                            {{ formatCurrency(plan.total_overtime) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Top 5 Pengeluaran Breakdown Bars -->
                <Card class="lg:col-span-7">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Layers class="h-4 w-4 text-primary" />
                                <CardTitle class="text-base">
                                    {{ showAllExpenses ? 'Semua Pengeluaran' : (plan.expense_items.length > 5 ? '5 Pengeluaran Terbesar' : 'Daftar Pengeluaran') }}
                                </CardTitle>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    v-if="plan.expense_items.length > 5"
                                    type="button"
                                    @click="showAllExpenses = !showAllExpenses"
                                    class="text-xs font-medium text-primary hover:underline cursor-pointer"
                                >
                                    {{ showAllExpenses ? 'Tampilkan 5 Teratas' : 'Tampilkan Semua' }}
                                </button>
                                <span class="text-xs text-muted-foreground">
                                    ({{ plan.expense_items.length }} total item)
                                </span>
                            </div>
                        </div>
                        <CardDescription class="text-xs">
                            {{ showAllExpenses ? 'Menampilkan seluruh rincian pos pengeluaran' : 'Pos pengeluaran dengan alokasi nominal terbesar' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-2">
                        <div v-if="displayedExpenses.length > 0" class="flex flex-col gap-3">
                            <div
                                class="flex flex-col gap-3 transition-all duration-300"
                                :class="{ 'max-h-[380px] overflow-y-auto pr-1.5': showAllExpenses }"
                            >
                                <div
                                    v-for="(item, index) in displayedExpenses"
                                    :key="item.id || index"
                                    class="space-y-1"
                                >
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-medium text-foreground truncate max-w-[200px] sm:max-w-xs">
                                            {{ index + 1 }}. {{ item.name }}
                                        </span>
                                        <div class="flex items-center gap-2 font-mono">
                                            <span class="text-muted-foreground">({{ item.percentage }}%)</span>
                                            <span class="font-semibold text-rose-600 dark:text-rose-400">
                                                {{ formatCurrency(item.amount) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-muted/60">
                                        <div
                                            class="h-full rounded-full bg-rose-500 transition-all duration-500"
                                            :style="{ width: `${Math.min(item.percentage, 100)}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Tampilkan Semua / Tampilkan Lebih Sedikit di Bawah List -->
                            <div
                                v-if="plan.expense_items.length > 5"
                                class="pt-2 text-center border-t border-border/50"
                            >
                                <button
                                    type="button"
                                    @click="showAllExpenses = !showAllExpenses"
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-primary hover:text-primary/80 transition-colors cursor-pointer focus:outline-none"
                                >
                                    <span>
                                        {{
                                            showAllExpenses
                                                ? 'Tampilkan Lebih Sedikit (5 Teratas)'
                                                : `Tampilkan Semua (${plan.expense_items.length} Item)`
                                        }}
                                    </span>
                                    <ChevronUp v-if="showAllExpenses" class="h-3.5 w-3.5" />
                                    <ChevronDown v-else class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                        <div v-else class="py-6 text-center text-xs text-muted-foreground">
                            Belum ada pos pengeluaran pada periode ini.
                        </div>
                    </CardContent>
                </Card>
            </div>
        </template>

        <!-- Jika Tidak Ada Data Plan untuk Bulan & Tahun Ini -->
        <template v-else>
            <Card class="border-dashed border-2 bg-muted/20">
                <CardContent class="flex flex-col items-center justify-center p-8 text-center sm:p-12">
                    <div class="rounded-full bg-muted p-3 text-muted-foreground mb-3">
                        <CalendarX2 class="h-6 w-6" />
                    </div>
                    <h3 class="text-base font-semibold text-foreground">
                        Belum Ada Budget Plan untuk {{ currentSelectedMonthName }} {{ selectedYear }}
                    </h3>
                    <p class="mt-1 max-w-sm text-xs text-muted-foreground">
                        Tidak ditemukan rencana anggaran untuk periode ini. Pilih bulan/tahun lain atau buat rencana baru.
                    </p>
                    <div class="mt-4">
                        <Button as-child size="sm" class="h-8 text-xs">
                            <Link href="/budget-plans/create">
                                <Plus class="mr-1.5 h-3.5 w-3.5" />
                                Buat Budget Plan
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </template>
    </div>
</template>
