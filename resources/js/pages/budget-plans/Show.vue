<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Edit,
    ArrowUpCircle,
    ArrowDownCircle,
    Calculator,
    ArrowRight,
    CalendarRange,
    FileText,
} from 'lucide-vue-next';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

interface BudgetPlanItem {
    id: number;
    category_id: number | null;
    name: string;
    type: 'income' | 'expense';
    amount: string | number;
    description: string | null;
}

interface BudgetPlanProp {
    id: number;
    month: number;
    year: number;
    status: 'draft' | 'active' | 'closed';
    notes: string | null;
    total_income: string | number;
    total_overtime: string | number;
    total_expense: string | number;
    balance: string | number;
    created_at: string;
    items: BudgetPlanItem[];
}

const props = defineProps<{
    budgetPlan: BudgetPlanProp;
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

const selectedMonthName = computed(() => {
    return (
        months.find((m) => m.value === Number(props.budgetPlan.month))?.label ||
        'Januari'
    );
});

const nextMonthPeriod = computed(() => {
    const m = Number(props.budgetPlan.month);
    const y = Number(props.budgetPlan.year);
    if (m === 12) {
        return {
            monthName: 'Januari',
            year: y + 1,
        };
    }
    const nextLabel =
        months.find((item) => item.value === m + 1)?.label || 'Februari';
    return {
        monthName: nextLabel,
        year: y,
    };
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Budget Plan', href: '/budget-plans' },
    {
        title: `Detail: ${selectedMonthName.value} ${props.budgetPlan.year}`,
        href: '#',
    },
];

const incomeItems = computed(() =>
    props.budgetPlan.items.filter((item) => item.type === 'income'),
);

const expenseItems = computed(() =>
    props.budgetPlan.items.filter((item) => item.type === 'expense'),
);

const formatCurrency = (amount: string | number | null): string => {
    const numeric = Number(amount || 0);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number.isNaN(numeric) ? 0 : numeric);
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head
            :title="`Detail Budget Plan: ${selectedMonthName} ${budgetPlan.year}`"
        />

        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        Budget Plan: {{ selectedMonthName }} {{ budgetPlan.year }}
                    </h1>
                    <Badge
                        v-if="budgetPlan.status === 'active'"
                        variant="default"
                        class="bg-emerald-600 text-xs font-normal hover:bg-emerald-700"
                    >
                        Aktif
                    </Badge>
                    <Badge
                        v-else-if="budgetPlan.status === 'closed'"
                        variant="secondary"
                        class="text-xs font-normal"
                    >
                        Selesai
                    </Badge>
                    <Badge v-else variant="outline" class="text-xs font-normal">
                        Draft
                    </Badge>
                </div>
                <p class="text-sm text-muted-foreground">
                    Rincian pemasukan bulan
                    <span>{{ selectedMonthName }} {{ budgetPlan.year }}</span>
                    dan alokasi pengeluaran bulan
                    <span
                        >{{ nextMonthPeriod.monthName }}
                        {{ nextMonthPeriod.year }}</span
                    >.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Button variant="outline" as-child>
                    <Link href="/budget-plans">Kembali</Link>
                </Button>
                <Button as-child>
                    <Link :href="`/budget-plans/${budgetPlan.id}/edit`">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit Budget Plan
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Info Periode Card -->
        <Card>
            <CardContent
                class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary"
                    >
                        <CalendarRange class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Periode Anggaran
                        </p>
                        <div class="flex items-center gap-2 text-sm text-foreground">
                            <span>
                                Pemasukan: {{ selectedMonthName }}
                                {{ budgetPlan.year }}
                            </span>
                            <ArrowRight class="h-3.5 w-3.5 text-muted-foreground" />
                            <span>
                                Pengeluaran: {{ nextMonthPeriod.monthName }}
                                {{ nextMonthPeriod.year }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    v-if="budgetPlan.notes"
                    class="flex items-start gap-2 rounded-lg border bg-muted/30 px-3 py-2 text-xs text-muted-foreground sm:max-w-md"
                >
                    <FileText class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                    <span>{{ budgetPlan.notes }}</span>
                </div>
            </CardContent>
        </Card>

        <!-- 3 Kolom: Pemasukan | Pengeluaran Bulan Depan | Ringkasan -->
        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
            <!-- KOLOM 1: Tabel Pemasukan -->
            <Card class="border-emerald-500/20">
                <CardHeader class="border-b pb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <ArrowUpCircle
                                class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                            />
                            <CardTitle class="text-base">Pemasukan</CardTitle>
                        </div>
                        <Badge
                            variant="default"
                            class="bg-emerald-600 text-[11px] font-normal hover:bg-emerald-700"
                        >
                            {{ selectedMonthName }} {{ budgetPlan.year }}
                        </Badge>
                    </div>
                    <CardDescription class="text-xs">
                        {{ incomeItems.length }} item pemasukan
                    </CardDescription>
                </CardHeader>

                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-muted/50 text-xs text-muted-foreground uppercase"
                            >
                                <tr class="border-b">
                                    <th
                                        class="w-10 px-3 py-2.5 text-center font-semibold"
                                    >
                                        No
                                    </th>
                                    <th
                                        class="px-3 py-2.5 text-left font-semibold"
                                    >
                                        Kategori
                                    </th>
                                    <th
                                        class="px-3 py-2.5 text-right font-semibold"
                                    >
                                        Nominal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr
                                    v-for="(item, idx) in incomeItems"
                                    :key="item.id"
                                    class="hover:bg-muted/30"
                                >
                                    <td
                                        class="px-3 py-2.5 text-center text-xs text-muted-foreground"
                                    >
                                        {{ idx + 1 }}
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-xs text-foreground"
                                    >
                                        {{ item.name }}
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono text-xs text-emerald-600 dark:text-emerald-400"
                                    >
                                        {{ formatCurrency(item.amount) }}
                                    </td>
                                </tr>
                                <tr v-if="incomeItems.length === 0">
                                    <td
                                        colspan="3"
                                        class="p-6 text-center text-xs text-muted-foreground"
                                    >
                                        Tidak ada item pemasukan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- KOLOM 2: Tabel Pengeluaran Bulan Depan -->
            <Card class="border-rose-500/20">
                <CardHeader class="border-b pb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <ArrowDownCircle
                                class="h-4 w-4 text-rose-600 dark:text-rose-400"
                            />
                            <CardTitle class="text-base">Pengeluaran</CardTitle>
                        </div>
                        <Badge
                            variant="destructive"
                            class="text-[11px] font-normal"
                        >
                            {{ nextMonthPeriod.monthName }}
                            {{ nextMonthPeriod.year }}
                        </Badge>
                    </div>
                    <CardDescription class="text-xs">
                        {{ expenseItems.length }} item pengeluaran
                    </CardDescription>
                </CardHeader>

                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-muted/50 text-xs text-muted-foreground uppercase"
                            >
                                <tr class="border-b">
                                    <th
                                        class="w-10 px-3 py-2.5 text-center font-semibold"
                                    >
                                        No
                                    </th>
                                    <th
                                        class="px-3 py-2.5 text-left font-semibold"
                                    >
                                        Kategori
                                    </th>
                                    <th
                                        class="px-3 py-2.5 text-right font-semibold"
                                    >
                                        Nominal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr
                                    v-for="(item, idx) in expenseItems"
                                    :key="item.id"
                                    class="hover:bg-muted/30"
                                >
                                    <td
                                        class="px-3 py-2.5 text-center text-xs text-muted-foreground"
                                    >
                                        {{ idx + 1 }}
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-xs text-foreground"
                                    >
                                        {{ item.name }}
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono text-xs text-rose-600 dark:text-rose-400"
                                    >
                                        {{ formatCurrency(item.amount) }}
                                    </td>
                                </tr>
                                <tr v-if="expenseItems.length === 0">
                                    <td
                                        colspan="3"
                                        class="p-6 text-center text-xs text-muted-foreground"
                                    >
                                        Tidak ada item pengeluaran.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- KOLOM 3: Ringkasan Budget Plan -->
            <Card class="sticky top-6">
                <CardHeader class="border-b pb-4">
                    <div class="flex items-center gap-2">
                        <Calculator class="h-4 w-4 text-primary" />
                        <CardTitle class="text-base">
                            Ringkasan Budget Plan
                        </CardTitle>
                    </div>
                    <CardDescription class="flex items-center gap-1.5 text-xs">
                        <span
                            >{{ selectedMonthName }} {{ budgetPlan.year }}</span
                        >
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
                            <p class="text-xs text-muted-foreground">
                                Total Pemasukan ({{ selectedMonthName }})
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                {{ incomeItems.length }} item pemasukan
                            </p>
                        </div>
                        <span
                            class="font-mono text-sm text-emerald-600 dark:text-emerald-400"
                        >
                            {{ formatCurrency(budgetPlan.total_income) }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-lg border bg-amber-500/5 p-3.5"
                    >
                        <div class="space-y-0.5">
                            <p class="text-xs text-muted-foreground">
                                Total Lembur
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                Dari kategori berlabel "Lembur"
                            </p>
                        </div>
                        <span
                            class="font-mono text-sm text-amber-600 dark:text-amber-400"
                        >
                            {{ formatCurrency(budgetPlan.total_overtime) }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-lg border bg-rose-500/5 p-3.5"
                    >
                        <div class="space-y-0.5">
                            <p class="text-xs text-muted-foreground">
                                Total Pengeluaran ({{
                                    nextMonthPeriod.monthName
                                }})
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                {{ expenseItems.length }} item pengeluaran
                            </p>
                        </div>
                        <span
                            class="font-mono text-sm text-rose-600 dark:text-rose-400"
                        >
                            {{ formatCurrency(budgetPlan.total_expense) }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-xl border p-4"
                        :class="
                            Number(budgetPlan.balance) >= 0
                                ? 'border-primary/30 bg-primary/5'
                                : 'border-destructive/40 bg-destructive/10'
                        "
                    >
                        <div class="space-y-0.5">
                            <p class="text-sm text-foreground">Sisa Anggaran</p>
                            <p class="text-xs text-muted-foreground">
                                Total Pemasukan - Total Pengeluaran
                            </p>
                        </div>
                        <span
                            class="font-mono text-base"
                            :class="
                                Number(budgetPlan.balance) >= 0
                                    ? 'text-primary'
                                    : 'text-destructive'
                            "
                        >
                            {{ formatCurrency(budgetPlan.balance) }}
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

