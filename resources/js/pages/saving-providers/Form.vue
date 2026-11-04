<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
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
    provider?: {
        id: number;
        name: string;
        account_number: string | null;
        account_holder: string | null;
        description: string | null;
    };
}>();

const isEdit = !!props.provider;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Data Master', href: '#' },
    { title: 'Tempat Penyimpanan', href: '/saving-providers' },
    {
        title: isEdit ? `Edit: ${props.provider!.name}` : 'Tambah',
        href: '#',
    },
];

const form = useForm({
    name: props.provider?.name ?? '',
    account_number: props.provider?.account_number ?? '',
    account_holder: props.provider?.account_holder ?? '',
    description: props.provider?.description ?? '',
});

const submit = () =>
    isEdit
        ? form.patch(`/saving-providers/${props.provider!.id}`)
        : form.post('/saving-providers');
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head
            :title="
                isEdit
                    ? `Edit: ${provider!.name}`
                    : 'Tambah Tempat Penyimpanan'
            "
        />

        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <div class="space-y-1">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                {{
                    isEdit
                        ? `Edit: ${provider!.name}`
                        : 'Tambah Tempat Penyimpanan Baru'
                }}
            </h1>
            <p class="text-sm text-muted-foreground">
                {{
                    isEdit
                        ? 'Perbarui informasi tempat penyimpanan.'
                        : 'Tambahkan bank, e-wallet, atau platform investasi baru. No. rekening, atas nama, dan deskripsi bersifat opsional.'
                }}
            </p>
        </div>

        <Card class="w-full">
            <CardHeader>
                <CardTitle>Informasi Tempat Penyimpanan</CardTitle>
                <CardDescription>
                    Masukkan nama tempat penyimpanan, nomor rekening, dan atas
                    nama pemilik akun.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form class="grid gap-6" @submit.prevent="submit">
                    <div class="grid gap-6 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="name">Nama</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Contoh: Bank Mandiri, Bibit, BCA"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="account_number"
                                >No. Rekening / Akun (Opsional)</Label
                            >
                            <Input
                                id="account_number"
                                v-model="form.account_number"
                                placeholder="Contoh: 1234567890"
                            />
                            <p
                                v-if="form.errors.account_number"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.account_number }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="account_holder"
                                >Atas Nama (Opsional)</Label
                            >
                            <Input
                                id="account_holder"
                                v-model="form.account_holder"
                                placeholder="Contoh: Agung Pratama"
                            />
                            <p
                                v-if="form.errors.account_holder"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.account_holder }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Deskripsi (Opsional)</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            placeholder="Masukkan keterangan atau catatan tambahan..."
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <p
                            v-if="form.errors.description"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button type="button" variant="outline" as-child>
                            <Link href="/saving-providers">Batal</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : isEdit
                                      ? 'Simpan Perubahan'
                                      : 'Simpan'
                            }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
