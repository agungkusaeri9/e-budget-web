<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Plus,
    WalletCards,
} from 'lucide-vue-next';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import ActivePlanGraphic, {
    type ActivePlanData,
} from '@/components/dashboard/ActivePlanGraphic.vue';
import MonthlyBarChart, {
    type MonthlyChartItem,
    type ChartSummary,
} from '@/components/dashboard/MonthlyBarChart.vue';
import type { BreadcrumbItem } from '@/types';

interface Props {
    activePlan: ActivePlanData | null;
    monthlyChartData: MonthlyChartItem[];
    summary: ChartSummary & {
        selected_month: number;
    };
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head title="Dashboard Anggaran" />

        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <!-- Header Dashboard -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                    Dashboard Anggaran
                </h1>
                <p class="text-sm text-muted-foreground">
                    Pantau kondisi keuangan, alokasi anggaran aktif, dan perbandingan arus kas bulanan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <Button variant="outline" as-child class="shrink-0">
                    <Link href="/budget-plans">
                        <WalletCards class="mr-2 h-4 w-4" />
                        Semua Budget Plan
                    </Link>
                </Button>
                <Button as-child class="shrink-0">
                    <Link href="/budget-plans/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Buat Budget Plan
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Section 1: Budget Plan (Dapat difilter berdasarkan Bulan & Tahun) -->
        <div>
            <ActivePlanGraphic
                :plan="activePlan"
                :selected-month="summary.selected_month"
                :selected-year="summary.selected_year"
                :available-years="summary.available_years"
            />
        </div>

        <!-- Section 2: Bar Chart Total Pemasukan & Pengeluaran Setiap Bulan -->
        <div>
            <MonthlyBarChart
                :data="monthlyChartData"
                :summary="summary"
            />
        </div>
    </div>
</template>
