<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    CalendarClock,
    ArrowLeft,
    Info,
    Calendar,
    ListFilter,
    CheckCircle2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import CurrencyInput from '@/components/CurrencyInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
import { Checkbox } from '@/components/ui/checkbox';
import type { BreadcrumbItem } from '@/types';

interface CategoryOption {
    id: number;
    name: string;
    amount: string | number | null;
    description: string | null;
}

interface RecurringExpenseData {
    id: number;
    category_id: number;
    name: string | null;
    amount: string | number;
    start_month: number;
    start_year: number;
    end_month: number | null;
    end_year: number | null;
    is_active: boolean;
    description: string | null;
    paid_count: number;
    total_duration: number | null;
    remaining_count: number | null;
    progress_percentage: number | null;
    is_completed: boolean;
    total_amount: number | null;
    paid_amount: number;
    remaining_amount: number | null;
}

const props = defineProps<{
    recurringExpense: RecurringExpenseData;
    expenseCategories: CategoryOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pengeluaran Rutin', href: '/recurring-expenses' },
    { title: 'Edit Pengeluaran Rutin', href: `/recurring-expenses/${props.recurringExpense.id}/edit` },
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
const yearOptions = Array.from({ length: 11 }, (_, i) =>
    String(currentYear - 2 + i),
);

const hasEndDate = ref(
    props.recurringExpense.end_month !== null &&
    props.recurringExpense.end_year !== null,
);

const isManualCategory = ref(false);

const form = useForm({
    category_id: String(props.recurringExpense.category_id),
    category_name: '',
    name: props.recurringExpense.name || '',
    amount: Number(props.recurringExpense.amount || 0),
    start_month: String(props.recurringExpense.start_month),
    start_year: String(props.recurringExpense.start_year),
    end_month: props.recurringExpense.end_month
        ? String(props.recurringExpense.end_month)
        : '',
    end_year: props.recurringExpense.end_year
        ? String(props.recurringExpense.end_year)
        : '',
    is_active: props.recurringExpense.is_active,
    description: props.recurringExpense.description || '',
});

const onSelectCategory = (val: any) => {
    const strVal = String(val ?? '');
    if (strVal === '__manual__') {
        isManualCategory.value = true;
        form.category_id = '';
        form.category_name = '';
        return;
    }

    isManualCategory.value = false;
    form.category_id = strVal;
    form.category_name = '';
};

const switchToManual = () => {
    isManualCategory.value = true;
    form.category_id = '';
    form.category_name = '';
};

const switchToSelect = () => {
    isManualCategory.value = false;
    form.category_id = String(props.recurringExpense.category_id);
    form.category_name = '';
};

watch(hasEndDate, (val) => {
    if (!val) {
        form.end_month = '';
        form.end_year = '';
    } else {
        if (!form.end_month) form.end_month = '12';
        if (!form.end_year) form.end_year = form.start_year || String(currentYear);
    }
});

const startMonthLabel = computed(() => {
    return months.find((m) => m.value === String(form.start_month))?.label || '';
});

const endMonthLabel = computed(() => {
    if (!hasEndDate.value || !form.end_month) return '';
    return months.find((m) => m.value === String(form.end_month))?.label || '';
});

const durationText = computed(() => {
    if (!hasEndDate.value || !form.end_month || !form.end_year) {
        return 'Berkelanjutan tanpa batas akhir';
    }
    const sm = Number(form.start_month);
    const sy = Number(form.start_year);
    const em = Number(form.end_month);
    const ey = Number(form.end_year);

    const total = (ey - sy) * 12 + (em - sm) + 1;
    if (total <= 0) {
        return 'Periode tidak valid (selesai lebih awal dari mulai)';
    }
    return `${total} Bulan`;
});

const computedTotalAmount = computed(() => {
    if (!hasEndDate.value || !form.end_month || !form.end_year) return null;
    const sm = Number(form.start_month);
    const sy = Number(form.start_year);
    const em = Number(form.end_month);
    const ey = Number(form.end_year);
    const totalMonths = (ey - sy) * 12 + (em - sm) + 1;
    if (totalMonths <= 0) return null;
    return Number(form.amount || 0) * totalMonths;
});

const formatCurrency = (val: number | null | undefined): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const submit = () => {
    form.put(`/recurring-expenses/${props.recurringExpense.id}`);
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head title="Edit Pengeluaran Rutin" />

        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Edit Pengeluaran Rutin
                </h1>
                <p class="text-sm text-muted-foreground">
                    Perbarui nominal, periode berlaku, atau status keaktifan pos pengeluaran rutin.
                </p>
            </div>
            <div>
                <Button as-child variant="outline" size="sm">
                    <Link href="/recurring-expenses">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Link>
                </Button>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid gap-6">
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Form (2 cols) -->
                <div class="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Informasi Pengeluaran</CardTitle>
                            <CardDescription>
                                Pilih kategori pengeluaran dan tentukan nominal yang akan otomatis dibayarkan.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="grid gap-5">
                            <!-- Kategori -->
                            <div class="grid gap-2">
                                <div class="flex items-center justify-between">
                                    <Label for="category_id">
                                        Kategori Pengeluaran <span class="text-destructive">*</span>
                                    </Label>
                                    <button
                                        type="button"
                                        @click="isManualCategory ? switchToSelect() : switchToManual()"
                                        class="text-xs text-primary hover:underline cursor-pointer flex items-center gap-1 font-medium"
                                    >
                                        <span v-if="!isManualCategory">+ Ketik Kategori Baru</span>
                                        <span v-else>← Pilih dari Daftar Kategori</span>
                                    </button>
                                </div>

                                <!-- Mode Pilih Kategori dari Dropdown -->
                                <div v-if="!isManualCategory">
                                    <Select
                                        :model-value="form.category_id ? String(form.category_id) : ''"
                                        @update:model-value="onSelectCategory"
                                    >
                                        <SelectTrigger id="category_id" class="w-full">
                                            <SelectValue placeholder="Pilih Kategori Pengeluaran / Ketik Baru..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="cat in expenseCategories"
                                                :key="cat.id"
                                                :value="String(cat.id)"
                                            >
                                                {{ cat.name }}
                                            </SelectItem>
                                            <SelectItem
                                                value="__manual__"
                                                class="font-medium text-primary"
                                            >
                                                + Ketik Kategori Baru...
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Mode Ketik Kategori Baru -->
                                <div v-else class="flex items-center gap-2">
                                    <Input
                                        id="category_name"
                                        v-model="form.category_name"
                                        placeholder="Ketik nama kategori pengeluaran baru..."
                                        class="flex-1"
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-9 w-9 shrink-0 text-muted-foreground"
                                        title="Kembali ke pilihan dropdown"
                                        @click="switchToSelect"
                                    >
                                        <ListFilter class="h-4 w-4" />
                                    </Button>
                                </div>

                                <p v-if="form.errors.category_id" class="text-xs text-destructive">
                                    {{ form.errors.category_id }}
                                </p>
                                <p v-if="form.errors.category_name" class="text-xs text-destructive">
                                    {{ form.errors.category_name }}
                                </p>
                            </div>

                            <!-- Nama Komitmen (Opsional) -->
                            <div class="grid gap-2">
                                <Label for="name">
                                    Nama Komitmen / Label (Opsional)
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Contoh: Cicilan Motor Vario 160, Kosan Bulanan, BPJS Kesehatan"
                                />
                                <p class="text-[11px] text-muted-foreground">
                                    Nama spesifik untuk membedakan pos ini di budget plan (default: nama kategori).
                                </p>
                                <p v-if="form.errors.name" class="text-xs text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Nominal -->
                            <div class="grid gap-2">
                                <Label for="amount">
                                    Nominal Pengeluaran Rutin <span class="text-destructive">*</span>
                                </Label>
                                <CurrencyInput
                                    id="amount"
                                    v-model="form.amount"
                                    placeholder="0"
                                />
                                <p class="text-[11px] text-muted-foreground">
                                    Nominal yang akan otomatis terisi setiap bulan dalam rentang periode yang diset.
                                </p>
                                <p v-if="form.errors.amount" class="text-xs text-destructive">
                                    {{ form.errors.amount }}
                                </p>
                            </div>

                            <!-- Deskripsi / Catatan -->
                            <div class="grid gap-2">
                                <Label for="description">Catatan / Keterangan (Opsional)</Label>
                                <Input
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Contoh: Pembayaran tempo tgl 10 tiap bulan"
                                />
                                <p v-if="form.errors.description" class="text-xs text-destructive">
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Periode Jadwal -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <Calendar class="h-4 w-4 text-primary" />
                                <CardTitle>Jadwal Periode Rutin</CardTitle>
                            </div>
                            <CardDescription>
                                Tentukan mulai kapan dan sampai kapan nominal rutin ini otomatis diaplikasikan.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="grid gap-5">
                            <!-- Periode Mulai -->
                            <div class="grid gap-2">
                                <Label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                    Mulai Periode <span class="text-destructive">*</span>
                                </Label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="grid gap-1.5">
                                        <Label for="start_month" class="text-xs">Bulan Mulai</Label>
                                        <Select v-model="form.start_month">
                                            <SelectTrigger id="start_month">
                                                <SelectValue placeholder="Bulan" />
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
                                        <p v-if="form.errors.start_month" class="text-xs text-destructive">
                                            {{ form.errors.start_month }}
                                        </p>
                                    </div>

                                    <div class="grid gap-1.5">
                                        <Label for="start_year" class="text-xs">Tahun Mulai</Label>
                                        <Select v-model="form.start_year">
                                            <SelectTrigger id="start_year">
                                                <SelectValue placeholder="Tahun" />
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
                                        <p v-if="form.errors.start_year" class="text-xs text-destructive">
                                            {{ form.errors.start_year }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Checkbox Batas Akhir -->
                            <div class="flex items-center space-x-2 pt-2 border-t">
                                <Checkbox
                                    id="has_end_date"
                                    :checked="hasEndDate"
                                    @update:checked="(val: boolean) => (hasEndDate = val)"
                                />
                                <Label
                                    for="has_end_date"
                                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
                                >
                                    Memiliki batas akhir periode (misal: kontrak / cicilan n-bulan)
                                </Label>
                            </div>

                            <!-- Periode Selesai (Jika memiliki batas akhir) -->
                            <div v-if="hasEndDate" class="grid gap-2">
                                <Label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                    Selesai Periode <span class="text-destructive">*</span>
                                </Label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="grid gap-1.5">
                                        <Label for="end_month" class="text-xs">Bulan Selesai</Label>
                                        <Select v-model="form.end_month">
                                            <SelectTrigger id="end_month">
                                                <SelectValue placeholder="Bulan" />
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
                                        <p v-if="form.errors.end_month" class="text-xs text-destructive">
                                            {{ form.errors.end_month }}
                                        </p>
                                    </div>

                                    <div class="grid gap-1.5">
                                        <Label for="end_year" class="text-xs">Tahun Selesai</Label>
                                        <Select v-model="form.end_year">
                                            <SelectTrigger id="end_year">
                                                <SelectValue placeholder="Tahun" />
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
                                        <p v-if="form.errors.end_year" class="text-xs text-destructive">
                                            {{ form.errors.end_year }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Aktif -->
                            <div class="flex items-center space-x-2 pt-2 border-t">
                                <Checkbox
                                    id="is_active"
                                    :checked="form.is_active"
                                    @update:checked="(val: boolean) => (form.is_active = val)"
                                />
                                <div class="grid gap-1 leading-none">
                                    <Label
                                        for="is_active"
                                        class="text-sm font-medium cursor-pointer"
                                    >
                                        Jadikan Status Aktif
                                    </Label>
                                    <p class="text-xs text-muted-foreground">
                                        Hanya jadwal berstatus aktif yang akan otomatis diterapkan saat membuat Budget Plan.
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Preview / Info Sidebar (1 col) -->
                <div class="space-y-6">
                    <!-- Card Realisasi Pembayaran -->
                    <Card class="border-border">
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-foreground">
                                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                                    <CardTitle class="text-base">Realisasi Pembayaran</CardTitle>
                                </div>
                                <Badge v-if="recurringExpense.is_completed" variant="secondary" class="bg-emerald-500/10 text-emerald-600 border-emerald-500/30">
                                    Lunas
                                </Badge>
                            </div>
                            <CardDescription class="text-xs">
                                Dihitung dari Budget Plan berstatus Closed
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3 text-xs">
                            <div v-if="recurringExpense.total_duration" class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-foreground">Status Bayar:</span>
                                    <span class="font-semibold text-foreground">
                                        {{ recurringExpense.paid_count }} dari {{ recurringExpense.total_duration }} Kali
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-foreground">Sudah Terbayar:</span>
                                    <span class="font-mono font-medium text-emerald-600 dark:text-emerald-400">
                                        {{ formatCurrency(recurringExpense.paid_amount) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-foreground">Sisa Tagihan:</span>
                                    <span class="font-mono font-medium text-foreground">
                                        {{ formatCurrency(recurringExpense.remaining_amount) }} ({{ recurringExpense.remaining_count }}x)
                                    </span>
                                </div>
                                <div class="space-y-1 pt-1">
                                    <div class="flex justify-between text-[11px] text-muted-foreground">
                                        <span>Progres</span>
                                        <span class="font-mono font-medium">{{ recurringExpense.progress_percentage }}%</span>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                                        <div
                                            class="h-full rounded-full transition-all duration-300"
                                            :class="recurringExpense.is_completed ? 'bg-emerald-500' : 'bg-primary'"
                                            :style="{ width: `${recurringExpense.progress_percentage}%` }"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div v-else class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-foreground">Terbayar:</span>
                                    <span class="font-semibold text-foreground">
                                        {{ recurringExpense.paid_count }} Kali
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-foreground">Total Terbayar:</span>
                                    <span class="font-mono font-medium text-emerald-600 dark:text-emerald-400">
                                        {{ formatCurrency(recurringExpense.paid_amount) }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-muted-foreground pt-1">
                                    Pengeluaran rutin berjalan tanpa batas akhir periode.
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="border-primary/20 bg-gradient-to-br from-primary/5 via-card to-card">
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-2 text-primary">
                                <CalendarClock class="h-4 w-4" />
                                <CardTitle class="text-base">Ringkasan Jadwal</CardTitle>
                            </div>
                            <CardDescription class="text-xs">
                                Simulasi otomatisasi saat membuat Budget Plan
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4 text-xs">
                            <div class="rounded-lg border bg-background/50 p-3 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-foreground">Kategori:</span>
                                    <span class="font-semibold text-foreground">
                                        {{
                                            isManualCategory
                                                ? (form.category_name || 'Kategori Baru')
                                                : (expenseCategories.find(c => String(c.id) === String(form.category_id))?.name || '-')
                                        }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-foreground">Nominal Rutin:</span>
                                    <span class="font-mono font-bold text-rose-600 dark:text-rose-400">
                                        {{ formatCurrency(form.amount) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center border-t pt-2">
                                    <span class="text-muted-foreground">Mulai:</span>
                                    <span class="font-medium text-foreground">
                                        {{ startMonthLabel }} {{ form.start_year }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-foreground">Selesai:</span>
                                    <span class="font-medium text-foreground">
                                        {{ hasEndDate && endMonthLabel ? `${endMonthLabel} ${form.end_year}` : 'Seterusnya' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center border-t pt-2">
                                    <span class="text-muted-foreground">Estimasi Durasi:</span>
                                    <span class="font-semibold text-primary">
                                        {{ durationText }}
                                    </span>
                                </div>
                                <div v-if="computedTotalAmount !== null" class="flex justify-between items-center border-t pt-2">
                                    <span class="text-muted-foreground">Total Keseluruhan:</span>
                                    <span class="font-mono font-bold text-foreground">
                                        {{ formatCurrency(computedTotalAmount) }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-start gap-2 text-[11px] text-muted-foreground">
                                <Info class="h-4 w-4 shrink-0 text-primary mt-0.5" />
                                <p>
                                    Ketika Anda membuat Budget Plan di rentang bulan tersebut, nominal pengeluaran kategori ini akan otomatis terisi sebesar
                                    <strong class="text-foreground">{{ formatCurrency(form.amount) }}</strong>.
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-2">
                        <Button type="submit" :disabled="form.processing" class="w-full">
                            {{ form.processing ? 'Menyimpan...' : 'Perbarui Pengeluaran Rutin' }}
                        </Button>
                        <Button as-child variant="ghost" class="w-full">
                            <Link href="/recurring-expenses">Batal</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

