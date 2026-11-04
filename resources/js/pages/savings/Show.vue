<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowDownCircle,
    ArrowUpCircle,
    CalendarRange,
    Edit,
    FileText,
    PiggyBank,
    Target,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import CurrencyInput from '@/components/CurrencyInput.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import type { BreadcrumbItem } from '@/types';

interface Transaction {
    id: number;
    amount: number;
    transaction_date: string;
    note: string | null;
    is_manual: boolean;
}

const props = defineProps<{
    saving: {
        id: number;
        name: string;
        provider: {
            id: number;
            name: string;
            account_number: string | null;
            account_holder: string | null;
        } | null;
        target_amount: string | number | null;
        deadline: string | null;
        notes: string | null;
        balance: number;
    };
    transactions: Transaction[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tabungan', href: '/savings' },
    { title: props.saving.name, href: '#' },
];

const formatCurrency = (amount: string | number | null): string => {
    const numeric = Number(amount || 0);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number.isNaN(numeric) ? 0 : numeric);
};

const progressPercent = computed(() => {
    const target = Number(props.saving.target_amount || 0);
    if (!target) return null;
    return Math.min(100, Math.round((props.saving.balance / target) * 100));
});

const totalIn = computed(() =>
    props.transactions
        .filter((t) => t.amount > 0)
        .reduce((s, t) => s + t.amount, 0),
);

const totalOut = computed(() =>
    props.transactions
        .filter((t) => t.amount < 0)
        .reduce((s, t) => s + Math.abs(t.amount), 0),
);

const form = useForm({
    mode: 'add' as 'add' | 'subtract',
    amount: '' as string | number,
    transaction_date: new Date().toISOString().slice(0, 10),
    note: '',
});

const submit = () => {
    form.transform((d) => ({
        amount:
            (d.mode === 'subtract' ? -1 : 1) * Math.abs(Number(d.amount)),
        transaction_date: d.transaction_date,
        note: d.note,
    })).post(`/savings/${props.saving.id}/transactions`, {
        preserveScroll: true,
        onSuccess: () => form.reset('amount', 'note'),
    });
};

const isDeleteTxOpen = ref(false);
const txToDelete = ref<Transaction | null>(null);

const confirmDeleteTx = (tx: Transaction) => {
    txToDelete.value = tx;
    isDeleteTxOpen.value = true;
};

const executeDeleteTx = () => {
    if (!txToDelete.value) return;
    router.delete(
        `/savings/${props.saving.id}/transactions/${txToDelete.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                isDeleteTxOpen.value = false;
                txToDelete.value = null;
            },
        },
    );
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head :title="`Detail Tabungan: ${saving.name}`" />

        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        {{ saving.name }}
                    </h1>
                </div>
                <p class="text-sm text-muted-foreground">
                    Detail saldo dan riwayat transaksi tabungan.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Button variant="outline" as-child>
                    <Link href="/savings">Kembali</Link>
                </Button>
                <Button as-child>
                    <Link :href="`/savings/${saving.id}/edit`">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit Tabungan
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Info Card -->
        <Card>
            <CardContent
                class="flex flex-col gap-4 p-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary"
                    >
                        <PiggyBank class="h-5 w-5" />
                    </div>
                    <div class="space-y-1">
                        <div
                            class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-foreground"
                        >
                            <span
                                v-if="saving.provider"
                                class="flex items-center gap-1.5"
                            >
                                <CalendarRange class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ saving.provider.name
                                }}{{
                                    saving.provider.account_number
                                        ? ` - ${saving.provider.account_number}`
                                        : ''
                                }}{{
                                    saving.provider.account_holder
                                        ? ` (${saving.provider.account_holder})`
                                        : ''
                                }}
                            </span>
                            <span
                                v-if="saving.target_amount"
                                class="flex items-center gap-1.5"
                            >
                                <Target class="h-3.5 w-3.5 text-muted-foreground" />
                                Target: {{ formatCurrency(saving.target_amount) }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <CalendarRange class="h-3.5 w-3.5 text-muted-foreground" />
                                Tenggat:
                                {{ saving.deadline || 'Tanpa tenggat' }}
                            </span>
                        </div>
                        <div
                            v-if="progressPercent !== null"
                            class="flex items-center gap-3"
                        >
                            <div
                                class="h-2 w-48 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full bg-primary transition-all"
                                    :style="{ width: `${progressPercent}%` }"
                                />
                            </div>
                            <span class="text-xs text-muted-foreground"
                                >{{ progressPercent }}% dari target</span
                            >
                        </div>
                    </div>
                </div>

                <div
                    v-if="saving.notes"
                    class="flex items-start gap-2 rounded-lg border bg-muted/30 px-3 py-2 text-xs text-muted-foreground sm:max-w-md"
                >
                    <FileText class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                    <span>{{ saving.notes }}</span>
                </div>
            </CardContent>
        </Card>

        <!-- 3-col layout: pemasukan | pengeluaran | ringkasan -->
        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
            <!-- Kolom 1: Transaksi masuk -->
            <Card class="border-emerald-500/20">
                <CardHeader class="border-b pb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <ArrowUpCircle
                                class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                            />
                            <CardTitle class="text-base">Uang Masuk</CardTitle>
                        </div>
                    </div>
                    <CardDescription class="text-xs">
                        {{ transactions.filter((t) => t.amount > 0).length }} transaksi
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
                                        class="px-3 py-2.5 text-left font-semibold"
                                    >
                                        Tanggal
                                    </th>
                                    <th
                                        class="px-3 py-2.5 text-left font-semibold"
                                    >
                                        Catatan
                                    </th>
                                    <th
                                        class="px-3 py-2.5 text-right font-semibold"
                                    >
                                        Nominal
                                    </th>
                                    <th class="w-10 px-3 py-2.5" />
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr
                                    v-for="t in transactions.filter(
                                        (t) => t.amount > 0,
                                    )"
                                    :key="t.id"
                                    class="hover:bg-muted/30"
                                >
                                    <td
                                        class="px-3 py-2.5 text-xs text-muted-foreground"
                                    >
                                        {{ t.transaction_date }}
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-foreground">
                                        {{
                                            t.note ||
                                            (t.is_manual
                                                ? 'Penyesuaian'
                                                : 'Budget plan')
                                        }}
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono text-xs text-emerald-600 dark:text-emerald-400"
                                    >
                                        {{ formatCurrency(t.amount) }}
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <Button
                                            v-if="t.is_manual"
                                            variant="ghost"
                                            size="icon"
                                            class="h-6 w-6 text-muted-foreground hover:text-destructive"
                                            @click="confirmDeleteTx(t)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </td>
                                </tr>
                                <tr
                                    v-if="
                                        transactions.filter((t) => t.amount > 0)
                                            .length === 0
                                    "
                                >
                                    <td
                                        colspan="4"
                                        class="p-6 text-center text-xs text-muted-foreground"
                                    >
                                        Belum ada uang masuk.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Kolom 2: Transaksi keluar -->
            <Card class="border-rose-500/20">
                <CardHeader class="border-b pb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <ArrowDownCircle
                                class="h-4 w-4 text-rose-600 dark:text-rose-400"
                            />
                            <CardTitle class="text-base">Uang Keluar</CardTitle>
                        </div>
                    </div>
                    <CardDescription class="text-xs">
                        {{ transactions.filter((t) => t.amount < 0).length }} transaksi
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
                                        class="px-3 py-2.5 text-left font-semibold"
                                    >
                                        Tanggal
                                    </th>
                                    <th
                                        class="px-3 py-2.5 text-left font-semibold"
                                    >
                                        Catatan
                                    </th>
                                    <th
                                        class="px-3 py-2.5 text-right font-semibold"
                                    >
                                        Nominal
                                    </th>
                                    <th class="w-10 px-3 py-2.5" />
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr
                                    v-for="t in transactions.filter(
                                        (t) => t.amount < 0,
                                    )"
                                    :key="t.id"
                                    class="hover:bg-muted/30"
                                >
                                    <td
                                        class="px-3 py-2.5 text-xs text-muted-foreground"
                                    >
                                        {{ t.transaction_date }}
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-foreground">
                                        {{
                                            t.note ||
                                            (t.is_manual
                                                ? 'Penyesuaian'
                                                : 'Budget plan')
                                        }}
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-right font-mono text-xs text-rose-600 dark:text-rose-400"
                                    >
                                        {{ formatCurrency(Math.abs(t.amount)) }}
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <Button
                                            v-if="t.is_manual"
                                            variant="ghost"
                                            size="icon"
                                            class="h-6 w-6 text-muted-foreground hover:text-destructive"
                                            @click="confirmDeleteTx(t)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </td>
                                </tr>
                                <tr
                                    v-if="
                                        transactions.filter((t) => t.amount < 0)
                                            .length === 0
                                    "
                                >
                                    <td
                                        colspan="4"
                                        class="p-6 text-center text-xs text-muted-foreground"
                                    >
                                        Belum ada uang keluar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Kolom 3: Ringkasan + Form Sesuaikan -->
            <div class="flex flex-col gap-6">
                <!-- Ringkasan -->
                <Card class="sticky top-6">
                    <CardHeader class="border-b pb-4">
                        <div class="flex items-center gap-2">
                            <PiggyBank class="h-4 w-4 text-primary" />
                            <CardTitle class="text-base">Ringkasan</CardTitle>
                        </div>
                        <CardDescription class="text-xs">
                            {{ saving.name }}
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="grid gap-4 pt-5">
                        <div
                            class="flex items-center justify-between rounded-lg border bg-emerald-500/5 p-3.5"
                        >
                            <div class="space-y-0.5">
                                <p class="text-xs text-muted-foreground">
                                    Total Masuk
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    {{
                                        transactions.filter((t) => t.amount > 0)
                                            .length
                                    }}
                                    transaksi
                                </p>
                            </div>
                            <span
                                class="font-mono text-sm text-emerald-600 dark:text-emerald-400"
                            >
                                {{ formatCurrency(totalIn) }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border bg-rose-500/5 p-3.5"
                        >
                            <div class="space-y-0.5">
                                <p class="text-xs text-muted-foreground">
                                    Total Keluar
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    {{
                                        transactions.filter((t) => t.amount < 0)
                                            .length
                                    }}
                                    transaksi
                                </p>
                            </div>
                            <span
                                class="font-mono text-sm text-rose-600 dark:text-rose-400"
                            >
                                {{ formatCurrency(totalOut) }}
                            </span>
                        </div>

                        <div
                            v-if="saving.target_amount"
                            class="flex items-center justify-between rounded-lg border bg-amber-500/5 p-3.5"
                        >
                            <div class="space-y-0.5">
                                <p class="text-xs text-muted-foreground">
                                    Kekurangan Target
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    Target:
                                    {{ formatCurrency(saving.target_amount) }}
                                </p>
                            </div>
                            <span
                                class="font-mono text-sm"
                                :class="
                                    Number(saving.target_amount) -
                                        saving.balance >
                                    0
                                        ? 'text-amber-600 dark:text-amber-400'
                                        : 'text-emerald-600 dark:text-emerald-400'
                                "
                            >
                                {{
                                    Number(saving.target_amount) -
                                        saving.balance >
                                    0
                                        ? formatCurrency(
                                              Number(saving.target_amount) -
                                                  saving.balance,
                                          )
                                        : 'Tercapai'
                                }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-xl border p-4"
                            :class="
                                saving.balance >= 0
                                    ? 'border-primary/30 bg-primary/5'
                                    : 'border-destructive/40 bg-destructive/10'
                            "
                        >
                            <div class="space-y-0.5">
                                <p class="text-sm text-foreground">
                                    Saldo Saat Ini
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Total Masuk - Total Keluar
                                </p>
                            </div>
                            <span
                                class="font-mono text-base"
                                :class="
                                    saving.balance >= 0
                                        ? 'text-primary'
                                        : 'text-destructive'
                                "
                            >
                                {{ formatCurrency(saving.balance) }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Sesuaikan Saldo -->
                <Card>
                    <CardHeader class="border-b pb-4">
                        <CardTitle class="text-base">
                            Sesuaikan Saldo
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Tambah atau kurangi saldo secara manual.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-4 pt-5">
                        <form
                            class="grid gap-4"
                            @submit.prevent="submit"
                        >
                            <div class="grid gap-2">
                                <Label for="mode">Jenis</Label>
                                <select
                                    id="mode"
                                    v-model="form.mode"
                                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option value="add">Tambah saldo</option>
                                    <option value="subtract">
                                        Kurangi saldo
                                    </option>
                                </select>
                            </div>

                            <div class="grid gap-2">
                                <Label for="amount">Nominal</Label>
                                <CurrencyInput
                                    id="amount"
                                    v-model="form.amount"
                                />
                                <p
                                    v-if="form.errors.amount"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.amount }}
                                </p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="tx_date">Tanggal</Label>
                                <Input
                                    id="tx_date"
                                    v-model="form.transaction_date"
                                    type="date"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="note"
                                    >Catatan (Opsional)</Label
                                >
                                <Input
                                    id="note"
                                    v-model="form.note"
                                    placeholder="Misal: transfer dari gaji"
                                />
                            </div>

                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full"
                            >
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Penyesuaian'
                                }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Delete Transaction Dialog -->
        <Dialog
            :open="isDeleteTxOpen"
            @update:open="isDeleteTxOpen = $event"
        >
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <div class="mb-2 flex items-center gap-2 text-destructive">
                        <AlertTriangle class="h-5 w-5" />
                        <DialogTitle>Hapus Transaksi</DialogTitle>
                    </div>
                    <DialogDescription>
                        Hapus transaksi
                        <strong>{{
                            txToDelete
                                ? formatCurrency(txToDelete.amount)
                                : ''
                        }}</strong>
                        pada
                        <strong>{{ txToDelete?.transaction_date }}</strong
                        >? Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-3 sm:gap-3">
                    <Button
                        variant="outline"
                        @click="isDeleteTxOpen = false"
                    >
                        Batal
                    </Button>
                    <Button variant="destructive" @click="executeDeleteTx">
                        Hapus Transaksi
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
