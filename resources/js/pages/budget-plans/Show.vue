<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    ArrowRight,
    ArrowUpCircle,
    Calculator,
    CalendarRange,
    Edit,
    FileText,
} from 'lucide-vue-next';
import { computed, ref as vRef } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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

const props = defineProps<{ budgetPlan: BudgetPlanProp }>();

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

const selectedMonthName = computed(
    () =>
        months.find((m) => m.value === Number(props.budgetPlan.month))?.label ||
        'Januari',
);

const nextMonthPeriod = computed(() => {
    const m = Number(props.budgetPlan.month);
    const y = Number(props.budgetPlan.year);
    if (m === 12) return { monthName: 'Januari', year: y + 1 };
    return {
        monthName: months.find((item) => item.value === m + 1)?.label || 'Februari',
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

const activeTab = vRef<'income' | 'expense'>('income');

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
                        Budget Plan: {{ selectedMonthName }}
                        {{ budgetPlan.year }}
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
                    Pemasukan
                    <strong>{{ selectedMonthName }} {{ budgetPlan.year }}</strong>
                    <ArrowRight class="mx-1 inline h-3 w-3" />
                    Pengeluaran
                    <strong
                        >{{ nextMonthPeriod.monthName }}
                        {{ nextMonthPeriod.year }}</strong
                    >
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Button variant="outline" as-child>
                    <Link href="/budget-plans">Kembali</Link>
                </Button>
                <Button as-child>
                    <Link :href="`/budget-plans/${budgetPlan.id}/edit`">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                </Button>
            </div>
        </div>

        <!-- 2-column layout -->
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <!-- KOLOM KIRI: Ringkasan + Info -->
            <div class="flex flex-col gap-4 lg:sticky lg:top-6 lg:col-span-4 xl:col-span-3">
                <!-- Info Periode -->
                <Card class="gap-3 py-4">
                    <CardHeader class="px-4 pb-0">
                        <div class="flex items-center gap-2">
                            <CalendarRange class="h-4 w-4 text-primary" />
                            <CardTitle class="text-sm font-semibold">
                                Periode Anggaran
                            </CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-2 px-4">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-xs text-muted-foreground">Pemasukan</span>
                            <span class="font-medium text-foreground">
                                {{ selectedMonthName }} {{ budgetPlan.year }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-xs text-muted-foreground">Pengeluaran</span>
                            <span class="font-medium text-foreground">
                                {{ nextMonthPeriod.monthName }}
                                {{ nextMonthPeriod.year }}
                            </span>
                        </div>
                        <div
                            v-if="budgetPlan.notes"
                            class="mt-1 flex items-start gap-2 rounded-lg border bg-muted/30 px-3 py-2 text-xs text-muted-foreground"
                        >
                            <FileText class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                            <span>{{ budgetPlan.notes }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Ringkasan -->
                <Card class="gap-3 py-4">
                    <CardHeader class="px-4 pb-0">
                        <div class="flex items-center gap-2">
                            <Calculator class="h-4 w-4 text-primary" />
                            <CardTitle class="text-sm font-semibold">
                                Ringkasan
                            </CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-2.5 px-4">
                        <div
                            class="flex items-center justify-between rounded-lg border bg-emerald-500/5 px-3 py-2.5"
                        >
                            <div>
                                <p class="text-[11px] text-muted-foreground">
                                    Total Pemasukan
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    {{ incomeItems.length }} item
                                </p>
                            </div>
                            <span
                                class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400"
                            >
                                {{ formatCurrency(budgetPlan.total_income) }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border bg-amber-500/5 px-3 py-2.5"
                        >
                            <div>
                                <p class="text-[11px] text-muted-foreground">
                                    Total Lembur
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    dari "Lembur"
                                </p>
                            </div>
                            <span
                                class="font-mono text-sm font-semibold text-amber-600 dark:text-amber-400"
                            >
                                {{ formatCurrency(budgetPlan.total_overtime) }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border bg-rose-500/5 px-3 py-2.5"
                        >
                            <div>
                                <p class="text-[11px] text-muted-foreground">
                                    Total Pengeluaran
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    {{ expenseItems.length }} item
                                </p>
                            </div>
                            <span
                                class="font-mono text-sm font-semibold text-rose-600 dark:text-rose-400"
                            >
                                {{ formatCurrency(budgetPlan.total_expense) }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-xl border px-3 py-3"
                            :class="
                                Number(budgetPlan.balance) >= 0
                                    ? 'border-primary/30 bg-primary/5'
                                    : 'border-destructive/40 bg-destructive/10'
                            "
                        >
                            <div>
                                <p class="text-sm font-medium text-foreground">
                                    Sisa Anggaran
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    Pemasukan - Pengeluaran
                                </p>
                            </div>
                            <span
                                class="font-mono text-base font-bold"
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

            <!-- KOLOM KANAN: Tabs Pemasukan & Pengeluaran -->
            <Card class="gap-0 overflow-hidden py-0 lg:col-span-8 xl:col-span-9">
                <CardHeader class="border-b bg-muted/20 p-4">
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
                            <Badge
                                variant="secondary"
                                class="h-4 px-1.5 text-[10px]"
                                >{{ incomeItems.length }}</Badge
                            >
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
                            <span
                                >Pengeluaran ({{
                                    nextMonthPeriod.monthName
                                }})</span
                            >
                            <Badge
                                variant="secondary"
                                class="h-4 px-1.5 text-[10px]"
                                >{{ expenseItems.length }}</Badge
                            >
                        </button>
                    </div>
                </CardHeader>

                <!-- Tab Pemasukan -->
                <CardContent v-if="activeTab === 'income'" class="p-0">
                    <div class="max-h-[calc(100vh-280px)] overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="sticky top-0 z-10 bg-muted text-xs text-muted-foreground uppercase"
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
                                    <td class="px-3 py-2.5 align-middle">
                                        <p class="text-xs font-medium text-foreground">
                                            {{ item.name }}
                                        </p>
                                        <p
                                            v-if="item.description"
                                            class="mt-0.5 text-[11px] text-muted-foreground"
                                        >
                                            {{ item.description }}
                                        </p>
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                                    >
                                        {{ formatCurrency(item.amount) }}
                                    </td>
                                </tr>
                                <tr v-if="incomeItems.length === 0">
                                    <td
                                        colspan="3"
                                        class="p-8 text-center text-xs text-muted-foreground"
                                    >
                                        Tidak ada item pemasukan.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot
                                v-if="incomeItems.length > 0"
                                class="border-t bg-muted/30"
                            >
                                <tr>
                                    <td
                                        colspan="2"
                                        class="px-3 py-2.5 text-xs font-semibold text-foreground"
                                    >
                                        Total Pemasukan
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400"
                                    >
                                        {{
                                            formatCurrency(
                                                budgetPlan.total_income,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </CardContent>

                <!-- Tab Pengeluaran -->
                <CardContent v-else class="p-0">
                    <div class="max-h-[calc(100vh-280px)] overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="sticky top-0 z-10 bg-muted text-xs text-muted-foreground uppercase"
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
                                    <td class="px-3 py-2.5 align-middle">
                                        <p class="text-xs font-medium text-foreground">
                                            {{ item.name }}
                                        </p>
                                        <p
                                            v-if="item.description"
                                            class="mt-0.5 text-[11px] text-muted-foreground"
                                        >
                                            {{ item.description }}
                                        </p>
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono text-xs font-semibold text-rose-600 dark:text-rose-400"
                                    >
                                        {{ formatCurrency(item.amount) }}
                                    </td>
                                </tr>
                                <tr v-if="expenseItems.length === 0">
                                    <td
                                        colspan="3"
                                        class="p-8 text-center text-xs text-muted-foreground"
                                    >
                                        Tidak ada item pengeluaran.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot
                                v-if="expenseItems.length > 0"
                                class="border-t bg-muted/30"
                            >
                                <tr>
                                    <td
                                        colspan="2"
                                        class="px-3 py-2.5 text-xs font-semibold text-foreground"
                                    >
                                        Total Pengeluaran
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono text-sm font-bold text-rose-600 dark:text-rose-400"
                                    >
                                        {{
                                            formatCurrency(
                                                budgetPlan.total_expense,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
