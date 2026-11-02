<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
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
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Data Master', href: '#' },
    { title: 'Manajemen Kategori', href: '/categories' },
    { title: 'Tambah Kategori', href: '/categories/create' },
];

const form = useForm({
    name: '',
    type: 'expense' as 'income' | 'expense',
    amount: '' as string | number,
    description: '',
    is_default: false,
    order_no: 0,
});

const submit = () => {
    form.post('/categories');
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head title="Tambah Kategori" />

        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <div class="space-y-1">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                Tambah Kategori Baru
            </h1>
            <p class="text-sm text-muted-foreground">
                Tambahkan kategori pemasukan atau pengeluaran baru ke dalam
                sistem.
            </p>
        </div>

        <Card class="w-full">
            <CardHeader>
                <CardTitle>Informasi Kategori</CardTitle>
                <CardDescription>
                    Masukkan nama, tipe, nominal default (opsional), dan
                    deskripsi kategori.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="name">Nama Kategori</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Contoh: Potongan kerja (BPJS), Kosan, Salary Juni 2026"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="type">Tipe Kategori</Label>
                            <Select v-model="form.type">
                                <SelectTrigger id="type" class="w-full">
                                    <SelectValue
                                        placeholder="Pilih tipe kategori"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="expense">
                                        Pengeluaran (Expense)
                                    </SelectItem>
                                    <SelectItem value="income">
                                        Pemasukan (Income)
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="form.errors.type"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.type }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="amount"
                                >Nominal Default (Opsional)</Label
                            >
                            <CurrencyInput
                                id="amount"
                                v-model="form.amount"
                                :nullable="true"
                                placeholder="Kosongkan jika tidak ada nominal default"
                            />
                            <p
                                v-if="form.errors.amount"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.amount }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="order_no">Nomor Urut (Order No)</Label>
                            <Input
                                id="order_no"
                                v-model.number="form.order_no"
                                type="number"
                                min="0"
                                placeholder="0"
                            />
                            <p
                                v-if="form.errors.order_no"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.order_no }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Deskripsi (Opsional)</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            placeholder="Masukkan keterangan atau catatan tambahan untuk kategori ini..."
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <p
                            v-if="form.errors.description"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div
                        class="flex items-start space-x-3 rounded-lg border bg-muted/30 p-4"
                    >
                        <input
                            id="is_default"
                            v-model="form.is_default"
                            type="checkbox"
                            class="mt-0.5 size-4 rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <div class="grid gap-1 leading-none">
                            <label
                                for="is_default"
                                class="cursor-pointer text-sm font-medium"
                            >
                                Jadikan Kategori Default pada Budget Plan
                            </label>
                            <p class="text-xs text-muted-foreground">
                                Jika dicentang, kategori ini otomatis muncul di
                                tabel pemasukan/pengeluaran saat membuat Budget
                                Plan baru.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button type="button" variant="outline" as-child>
                            <Link href="/categories">Batal</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Kategori'
                            }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>

