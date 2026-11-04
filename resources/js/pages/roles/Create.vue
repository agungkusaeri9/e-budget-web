<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Shield } from 'lucide-vue-next';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import type { BreadcrumbItem } from '@/types';

interface Permission {
    id: number;
    name: string;
}

defineProps<{
    permissions: Permission[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Peran & Hak Akses', href: '#' },
    { title: 'Manajemen Peran', href: '/roles' },
    { title: 'Tambah Peran', href: '/roles/create' },
];

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const togglePermission = (name: string) => {
    const index = form.permissions.indexOf(name);
    if (index === -1) {
        form.permissions.push(name);
    } else {
        form.permissions.splice(index, 1);
    }
};

const submit = () => {
    form.post('/roles');
};
</script>

<template>
    <Head title="Tambah Peran" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <!-- Breadcrumbs in Content -->
        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <div class="space-y-1">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                Tambah Peran Baru
            </h1>
            <p class="text-sm text-muted-foreground">
                Tentukan nama peran dan pilih hak akses (permissions) yang diberikan.
            </p>
        </div>

        <Card class="w-full">
            <CardHeader>
                <CardTitle>Informasi Peran</CardTitle>
                <CardDescription>Masukkan nama peran dan centang hak akses yang relevan.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="name">Nama Peran</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Contoh: manager, kasir, admin gudang"
                        />
                        <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid gap-4">
                        <div class="flex items-center justify-between">
                            <Label>Hak Akses / Permissions ({{ form.permissions.length }} dipilih)</Label>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-7 text-xs"
                                @click="
                                    form.permissions =
                                        form.permissions.length === permissions.length
                                            ? []
                                            : permissions.map((p) => p.name)
                                "
                            >
                                {{ form.permissions.length === permissions.length ? 'Hapus Semua' : 'Pilih Semua' }}
                            </Button>
                        </div>
                        <div class="grid grid-cols-1 gap-3 rounded-lg border bg-muted/30 p-4 md:grid-cols-3">
                            <label
                                v-for="perm in permissions"
                                :key="perm.id"
                                :for="`perm-${perm.id}`"
                                class="flex cursor-pointer items-center space-x-2.5 rounded-md border bg-background p-2.5 transition-colors hover:bg-muted/50"
                            >
                                <input
                                    :id="`perm-${perm.id}`"
                                    v-model="form.permissions"
                                    type="checkbox"
                                    :value="perm.name"
                                    class="size-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <span class="text-xs font-medium leading-none">
                                    {{ perm.name }}
                                </span>
                            </label>
                        </div>
                        <p v-if="form.errors.permissions" class="text-xs text-destructive">{{ form.errors.permissions }}</p>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button type="button" variant="outline" as-child>
                            <Link href="/roles">Batal</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Shield class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Peran' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
