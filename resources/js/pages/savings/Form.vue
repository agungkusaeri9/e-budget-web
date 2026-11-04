<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    saving?: {
        id: number;
        name: string;
        saving_provider_id: number | null;
        target_amount: string | null;
        deadline: string | null;
        notes: string | null;
    };
    providers: {
        id: number;
        name: string;
        account_number: string | null;
        account_holder: string | null;
    }[];
}>();

const isEdit = !!props.saving;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tabungan', href: '/savings' },
    {
        title: isEdit
            ? `Edit: ${props.saving!.name}`
            : 'Tambah Tabungan',
        href: '#',
    },
];

const form = useForm({
    name: props.saving?.name ?? '',
    saving_provider_id: (props.saving?.saving_provider_id ?? null) as
        | number
        | null,
    target_amount: (props.saving?.target_amount
        ? Number(props.saving.target_amount)
        : '') as string | number,
    deadline: props.saving?.deadline ?? '',
    notes: props.saving?.notes ?? '',
});

const submit = () =>
    isEdit
        ? form.patch(`/savings/${props.saving!.id}`)
        : form.post('/savings');
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head
            :title="isEdit ? `Edit Tabungan: ${saving!.name}` : 'Tambah Tabungan'"
        />

        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <div class="space-y-1">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                {{ isEdit ? `Edit Tabungan: ${saving!.name}` : 'Tambah Tabungan Baru' }}
            </h1>
            <p class="text-sm text-muted-foreground">
                {{
                    isEdit
                        ? 'Perbarui informasi tabungan.'
                        : 'Tambahkan master tabungan baru. Tenggat dan target bersifat opsional.'
                }}
            </p>
        </div>

        <Card class="w-full">
            <CardHeader>
                <CardTitle>Informasi Tabungan</CardTitle>
                <CardDescription>
                    Masukkan nama, tempat penyimpanan, target nominal, dan
                    tenggat tabungan.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form class="grid gap-6" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="name">Nama Tabungan</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Contoh: Tabungan Nikah, Dana Darurat"
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
                            <Label for="saving_provider_id"
                                >Disimpan di (Opsional)</Label
                            >
                            <select
                                id="saving_provider_id"
                                v-model="form.saving_provider_id"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                <option :value="null">
                                    -- Pilih tempat penyimpanan --
                                </option>
                                <option
                                    v-for="p in providers"
                                    :key="p.id"
                                    :value="p.id"
                                >
                                    {{ p.name
                                    }}{{
                                        p.account_number
                                            ? ` - ${p.account_number}`
                                            : ''
                                    }}{{
                                        p.account_holder
                                            ? ` (${p.account_holder})`
                                            : ''
                                    }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.saving_provider_id"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.saving_provider_id }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="target_amount"
                                >Target Nominal (Opsional)</Label
                            >
                            <CurrencyInput
                                id="target_amount"
                                v-model="form.target_amount"
                                :nullable="true"
                                placeholder="Kosongkan jika tidak ada target"
                            />
                            <p
                                v-if="form.errors.target_amount"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.target_amount }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="deadline">Tenggat (Opsional)</Label>
                            <Input
                                id="deadline"
                                v-model="form.deadline"
                                type="date"
                            />
                            <p
                                v-if="form.errors.deadline"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.deadline }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="notes">Catatan (Opsional)</Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            placeholder="Masukkan keterangan atau catatan tambahan untuk tabungan ini..."
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <p
                            v-if="form.errors.notes"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.notes }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button type="button" variant="outline" as-child>
                            <Link href="/savings">Batal</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : isEdit
                                      ? 'Simpan Perubahan'
                                      : 'Simpan Tabungan'
                            }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
