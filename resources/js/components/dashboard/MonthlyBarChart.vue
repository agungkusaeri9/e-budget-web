<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    BarChart3,
    Calendar,
    ArrowUpCircle,
    ArrowDownCircle,
    Wallet,
    Info,
    ExternalLink,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
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

export interface MonthlyChartItem {
    month: number;
    month_name: string;
    short_name: string;
    total_income: number;
    total_expense: number;
    balance: number;
    total_overtime: number;
    has_plan: boolean;
    status: 'active' | 'draft' | 'closed' | null;
    plan_id: number | null;
    is_active: boolean;
}

export interface ChartSummary {
    selected_year: number;
    available_years: number[];
    year_total_income: number;
    year_total_expense: number;
    year_total_balance: number;
    year_total_overtime: number;
    plans_count: number;
}

const props = defineProps<{
    data: MonthlyChartItem[];
    summary: ChartSummary;
}>();

const hoveredIndex = ref<number | null>(null);

const formatCurrency = (val: number | string | null | undefined): string => {
    const num = Number(val || 0);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number.isNaN(num) ? 0 : num);
};

const formatCompactCurrency = (num: number): string => {
    if (num >= 1000000000) {
        return (num / 1000000000).toFixed(1).replace(/\.0$/, '') + ' M';
    }
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1).replace(/\.0$/, '') + ' jt';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(0) + ' rb';
    }
    return String(num);
};

// Calculate scale
const maxVal = computed(() => {
    const allAmounts = props.data.flatMap((d) => [d.total_income, d.total_expense]);
    const max = Math.max(...allAmounts, 0);
    if (max <= 0) return 1000000;
    // Round up to nice number
    const magnitude = Math.pow(10, Math.floor(Math.log10(max)));
    return Math.ceil(max / magnitude) * magnitude;
});

const yAxisSteps = computed(() => {
    const max = maxVal.value;
    return [
        { label: formatCompactCurrency(max), value: max },
        { label: formatCompactCurrency(max * 0.75), value: max * 0.75 },
        { label: formatCompactCurrency(max * 0.5), value: max * 0.5 },
        { label: formatCompactCurrency(max * 0.25), value: max * 0.25 },
        { label: '0', value: 0 },
    ];
});

const getBarHeight = (amount: number): number => {
    if (amount <= 0 || maxVal.value <= 0) return 0;
    return Math.min(Math.max((amount / maxVal.value) * 100, 2), 100);
};

const onYearChange = (year: string) => {
    router.get(
        '/dashboard',
        { year },
        { preserveState: true, preserveScroll: true },
    );
};
</script>

<template>
    <Card class="w-full">
        <!-- Header & Year Selector -->
        <CardHeader class="flex flex-col gap-4 border-b pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <BarChart3 class="h-4 w-4 text-primary" />
                    <CardTitle class="text-base sm:text-lg">
                        Tren Pemasukan &amp; Pengeluaran Bulanan
                    </CardTitle>
                </div>
                <CardDescription class="text-xs">
                    Perbandingan total pemasukan dan alokasi pengeluaran per bulan pada tahun {{ summary.selected_year }}
                </CardDescription>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Legend -->
                <div class="flex items-center gap-3 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span>
                        <span class="text-muted-foreground">Pemasukan</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-sm bg-rose-500"></span>
                        <span class="text-muted-foreground">Pengeluaran</span>
                    </div>
                </div>

                <!-- Year Select -->
                <Select
                    :model-value="String(summary.selected_year)"
                    @update:model-value="(val) => onYearChange(String(val))"
                >
                    <SelectTrigger class="h-8 w-28 text-xs">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="year in summary.available_years"
                            :key="year"
                            :value="String(year)"
                        >
                            {{ year }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </CardHeader>

        <CardContent class="pt-6">
            <!-- Main Chart Area -->
            <div class="relative w-full">
                <!-- Grid Lines and Y-Axis -->
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-8 text-[11px] text-muted-foreground/60">
                    <div
                        v-for="(step, idx) in yAxisSteps"
                        :key="idx"
                        class="flex items-center gap-2 border-b border-border/30 w-full"
                    >
                        <span class="w-10 text-right font-mono text-[10px] shrink-0 text-muted-foreground">
                            {{ step.label }}
                        </span>
                        <div class="w-full"></div>
                    </div>
                </div>

                <!-- Bars Container -->
                <div class="relative z-10 flex h-64 items-end pl-12 pr-2 pb-8 gap-1 sm:gap-2">
                    <div
                        v-for="(item, idx) in data"
                        :key="item.month"
                        class="group relative flex h-full flex-1 flex-col items-center justify-end cursor-pointer"
                        @mouseenter="hoveredIndex = idx"
                        @mouseleave="hoveredIndex = null"
                    >
                        <!-- Active Month Highlight Background -->
                        <div
                            v-if="item.is_active"
                            class="absolute inset-x-0 -bottom-1 -top-2 rounded-lg bg-primary/5 border border-primary/20 pointer-events-none"
                        ></div>

                        <!-- Hover Highlight Background -->
                        <div
                            class="absolute inset-x-0 -bottom-1 -top-2 rounded-lg transition-colors pointer-events-none"
                            :class="hoveredIndex === idx ? 'bg-muted/40' : ''"
                        ></div>

                        <!-- Pair of Bars -->
                        <div class="relative z-10 flex w-full items-end justify-center gap-1 sm:gap-1.5 h-full px-0.5 sm:px-1">
                            <!-- Income Bar (Emerald) -->
                            <div class="w-full max-w-[16px] flex flex-col items-center justify-end h-full">
                                <div
                                    class="w-full rounded-t-sm bg-emerald-500 hover:bg-emerald-600 transition-all duration-300 shadow-xs"
                                    :style="{ height: `${getBarHeight(item.total_income)}%` }"
                                    :title="`Pemasukan: ${formatCurrency(item.total_income)}`"
                                ></div>
                            </div>

                            <!-- Expense Bar (Rose) -->
                            <div class="w-full max-w-[16px] flex flex-col items-center justify-end h-full">
                                <div
                                    class="w-full rounded-t-sm bg-rose-500 hover:bg-rose-600 transition-all duration-300 shadow-xs"
                                    :style="{ height: `${getBarHeight(item.total_expense)}%` }"
                                    :title="`Pengeluaran: ${formatCurrency(item.total_expense)}`"
                                ></div>
                            </div>
                        </div>

                        <!-- X-Axis Month Label -->
                        <div class="absolute -bottom-7 flex flex-col items-center">
                            <span
                                class="text-[11px] font-medium transition-colors"
                                :class="
                                    item.is_active
                                        ? 'text-primary font-bold'
                                        : hoveredIndex === idx
                                          ? 'text-foreground font-semibold'
                                          : 'text-muted-foreground'
                                "
                            >
                                {{ item.short_name }}
                            </span>
                            <!-- Dot for active month -->
                            <span
                                v-if="item.is_active"
                                class="h-1 w-1 rounded-full bg-primary mt-0.5"
                                title="Bulan Aktif"
                            ></span>
                        </div>

                        <!-- Floating Tooltip on Hover -->
                        <div
                            v-if="hoveredIndex === idx"
                            class="absolute -top-28 z-30 min-w-[210px] rounded-lg border bg-popover/95 backdrop-blur-md p-2.5 shadow-lg text-popover-foreground transition-all duration-150 animate-in fade-in-50 zoom-in-95"
                            :class="idx > 7 ? 'right-0' : idx < 4 ? 'left-0' : 'left-1/2 -translate-x-1/2'"
                        >
                            <div class="flex items-center justify-between border-b pb-1.5 mb-1.5 text-xs font-semibold">
                                <span>{{ item.month_name }} {{ summary.selected_year }}</span>
                                <Badge
                                    v-if="item.is_active"
                                    variant="default"
                                    class="bg-emerald-600 text-[10px] h-4 px-1.5 py-0"
                                >
                                    Aktif
                                </Badge>
                                <Badge
                                    v-else-if="item.status === 'closed'"
                                    variant="secondary"
                                    class="text-[10px] h-4 px-1.5 py-0"
                                >
                                    Selesai
                                </Badge>
                                <Badge
                                    v-else-if="item.status === 'draft'"
                                    variant="outline"
                                    class="text-[10px] h-4 px-1.5 py-0"
                                >
                                    Draft
                                </Badge>
                                <span v-else class="text-[10px] text-muted-foreground font-normal">
                                    Belum Ada
                                </span>
                            </div>

                            <div class="space-y-1 text-xs">
                                <div class="flex items-center justify-between gap-4 font-mono">
                                    <span class="text-muted-foreground flex items-center gap-1 font-sans">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Pemasukan:
                                    </span>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                        {{ formatCurrency(item.total_income) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between gap-4 font-mono">
                                    <span class="text-muted-foreground flex items-center gap-1 font-sans">
                                        <span class="h-2 w-2 rounded-full bg-rose-500"></span> Pengeluaran:
                                    </span>
                                    <span class="font-semibold text-rose-600 dark:text-rose-400">
                                        {{ formatCurrency(item.total_expense) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between gap-4 font-mono border-t pt-1">
                                    <span class="text-muted-foreground font-sans">Sisa / Selisih:</span>
                                    <span
                                        class="font-bold"
                                        :class="item.balance >= 0 ? 'text-primary' : 'text-destructive'"
                                    >
                                        {{ formatCurrency(item.balance) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Annual Summary Bottom Strip -->
            <div class="mt-8 grid grid-cols-1 divide-y rounded-xl border bg-muted/20 sm:grid-cols-3 sm:divide-y-0 sm:divide-x divide-border">
                <div class="flex items-center gap-3 p-3.5">
                    <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400 shrink-0">
                        <ArrowUpCircle class="h-4 w-4" />
                    </div>
                    <div>
                        <p class="text-[11px] text-muted-foreground">Akumulasi Pemasukan {{ summary.selected_year }}</p>
                        <p class="font-mono text-sm font-bold text-foreground">
                            {{ formatCurrency(summary.year_total_income) }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3.5">
                    <div class="rounded-lg bg-rose-500/10 p-2 text-rose-600 dark:text-rose-400 shrink-0">
                        <ArrowDownCircle class="h-4 w-4" />
                    </div>
                    <div>
                        <p class="text-[11px] text-muted-foreground">Akumulasi Pengeluaran {{ summary.selected_year }}</p>
                        <p class="font-mono text-sm font-bold text-rose-600 dark:text-rose-400">
                            {{ formatCurrency(summary.year_total_expense) }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3.5">
                    <div
                        class="rounded-lg p-2 shrink-0"
                        :class="summary.year_total_balance >= 0 ? 'bg-primary/10 text-primary' : 'bg-destructive/10 text-destructive'"
                    >
                        <Wallet class="h-4 w-4" />
                    </div>
                    <div>
                        <p class="text-[11px] text-muted-foreground">Total Sisa Akumulatif {{ summary.selected_year }}</p>
                        <p
                            class="font-mono text-sm font-bold"
                            :class="summary.year_total_balance >= 0 ? 'text-primary' : 'text-destructive'"
                        >
                            {{ formatCurrency(summary.year_total_balance) }}
                        </p>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

