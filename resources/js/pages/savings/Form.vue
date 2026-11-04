<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';
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

const isProviderModalOpen = ref(false);
const providerForm = useForm({
    name: '',
    account_number: '',
    account_holder: '',
    description: '',
    from_modal: true,
});

const submitProvider = () => {
    const createdName = providerForm.name.trim().toLowerCase();
    providerForm.post('/saving-providers', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            const matched = props.providers.find(
                (p) => p.name.trim().toLowerCase() === createdName,
            );
            if (matched) {
                form.saving_provider_id = matched.id;
            }
            isProviderModalOpen.value = false;
            providerForm.reset();
        },
    });
};
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
                                >Rekening / Dompet (Opsional)</Label
                            >
                            <div class="flex items-center gap-2">
                                <select
                                    id="saving_provider_id"
                                    v-model="form.saving_provider_id"
                                    class="h-9 flex-1 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option :value="null">
                                        -- Pilih rekening / dompet --
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
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    class="h-9 w-9 shrink-0"
                                    title="Tambah Rekening / Dompet Baru"
                                    @click="isProviderModalOpen = true"
                                >
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>
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

        <!-- Modal Tambah Rekening / Dompet -->
        <Dialog :open="isProviderModalOpen" @update:open="isProviderModalOpen = $event">
            <DialogContent class="sm:max-w-[460px]">
                <DialogHeader>
                    <DialogTitle>Tambah Rekening / Dompet</DialogTitle>
                    <DialogDescription>
                        Tambahkan rekening bank, dompet digital, atau platform investasi baru.
                    </DialogDescription>
                </DialogHeader>

                <form class="grid gap-4 py-1" @submit.prevent="submitProvider">
                    <div class="grid gap-1.5">
                        <Label for="modal_provider_name" class="text-xs">Nama</Label>
                        <Input
                            id="modal_provider_name"
                            v-model="providerForm.name"
                            placeholder="Contoh: Bank Mandiri, Bibit, BCA"
                            class="h-8 text-xs"
                        />
                        <p v-if="providerForm.errors.name" class="text-xs text-destructive">
                            {{ providerForm.errors.name }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="modal_account_number" class="text-xs">No. Rekening / Akun (Opsional)</Label>
                            <Input
                                id="modal_account_number"
                                v-model="providerForm.account_number"
                                placeholder="Contoh: 1234567890"
                                class="h-8 text-xs"
                            />
                            <p v-if="providerForm.errors.account_number" class="text-xs text-destructive">
                                {{ providerForm.errors.account_number }}
                            </p>
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="modal_account_holder" class="text-xs">Atas Nama (Opsional)</Label>
                            <Input
                                id="modal_account_holder"
                                v-model="providerForm.account_holder"
                                placeholder="Contoh: Agung Pratama"
                                class="h-8 text-xs"
                            />
                            <p v-if="providerForm.errors.account_holder" class="text-xs text-destructive">
                                {{ providerForm.errors.account_holder }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="modal_provider_desc" class="text-xs">Deskripsi (Opsional)</Label>
                        <textarea
                            id="modal_provider_desc"
                            v-model="providerForm.description"
                            rows="2"
                            placeholder="Catatan tambahan..."
                            class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-xs shadow-xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                        />
                    </div>

                    <DialogFooter class="gap-2 pt-2 sm:gap-2">
                        <Button type="button" variant="outline" size="sm" @click="isProviderModalOpen = false">
                            Batal
                        </Button>
                        <Button type="submit" size="sm" :disabled="providerForm.processing">
                            {{ providerForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
