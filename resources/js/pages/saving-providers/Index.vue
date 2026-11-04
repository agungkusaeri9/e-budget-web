<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ChevronLeft,
    ChevronRight,
    Edit,
    Landmark,
    Plus,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { usePermission } from '@/composables/usePermission';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { BreadcrumbItem } from '@/types';

interface SavingProvider {
    id: number;
    name: string;
    account_number: string | null;
    account_holder: string | null;
    description: string | null;
    savings_count: number;
}

interface Props {
    providers: {
        data: SavingProvider[];
        links: any[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
    filters: {
        search?: string;
        per_page?: string;
    };
}

const props = defineProps<Props>();
const { can } = usePermission();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Rekening & Dompet', href: '/saving-providers' },
];

const filterState = reactive({
    search: props.filters.search || '',
    per_page: props.filters.per_page || '10',
});

watch(
    () => ({ ...filterState }),
    () => {
        const query: Record<string, string> = { per_page: filterState.per_page };
        if (filterState.search) query.search = filterState.search;
        router.get('/saving-providers', query, {
            preserveState: true,
            replace: true,
        });
    },
    { deep: true },
);

const clearSearch = () => {
    filterState.search = '';
};

const isDeleteDialogOpen = ref(false);
const providerToDelete = ref<SavingProvider | null>(null);

const confirmDelete = (p: SavingProvider) => {
    providerToDelete.value = p;
    isDeleteDialogOpen.value = true;
};

const executeDelete = () => {
    if (providerToDelete.value) {
        router.delete(`/saving-providers/${providerToDelete.value.id}`, {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                providerToDelete.value = null;
            },
        });
    }
};

const perPageOptions = ['10', '20', '30', '50'];
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head title="Rekening & Dompet" />

        <Breadcrumbs :breadcrumbs="breadcrumbs" />

        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-1">
                <h1
                    class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                >
                    Rekening &amp; Dompet
                </h1>
                <p class="text-sm text-muted-foreground">
                    Kelola daftar rekening bank, e-wallet, atau akun investasi untuk
                    penyimpanan tabungan.
                </p>
            </div>

            <Button v-if="can('create saving providers')" as-child class="shrink-0">
                <Link href="/saving-providers/create">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Rekening / Dompet
                </Link>
            </Button>
        </div>

        <!-- DataTable Card Container -->
        <div class="rounded-xl border bg-card shadow-xs">
            <!-- Top Filters -->
            <div
                class="flex flex-col gap-4 border-b p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex flex-wrap items-center gap-3">
                    <div
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <span>Tampilkan</span>
                        <Select v-model="filterState.per_page">
                            <SelectTrigger class="h-8 w-[72px] text-xs">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in perPageOptions"
                                    :key="opt"
                                    :value="opt"
                                >
                                    {{ opt }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="relative w-full max-w-xs sm:w-64">
                    <Search
                        class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                    />
                    <Input
                        v-model="filterState.search"
                        type="search"
                        placeholder="Cari nama, no rek, atas nama..."
                        class="h-8 pr-8 pl-8 text-xs"
                    />
                    <button
                        v-if="filterState.search"
                        type="button"
                        class="absolute top-2 right-2 text-muted-foreground hover:text-foreground"
                        @click="clearSearch"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="bg-muted/50 [&_tr]:border-b">
                        <tr class="border-b transition-colors">
                            <th
                                class="h-10 w-14 px-4 text-center align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                No
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Nama
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                No. Rekening / Akun
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Atas Nama
                            </th>
                            <th
                                class="h-10 px-4 text-left align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Deskripsi
                            </th>
                            <th
                                class="h-10 px-4 text-center align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Tabungan
                            </th>
                            <th
                                class="h-10 px-4 text-right align-middle text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border [&_tr:last-child]:border-0">
                        <tr
                            v-for="(p, index) in providers.data"
                            :key="p.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td
                                class="p-3.5 text-center align-middle text-xs text-muted-foreground"
                            >
                                {{ (providers.from || 1) + index }}
                            </td>
                            <td class="p-3.5 align-middle">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                                    >
                                        <Landmark class="h-4 w-4" />
                                    </div>
                                    <span class="font-medium text-foreground">{{
                                        p.name
                                    }}</span>
                                </div>
                            </td>
                            <td
                                class="p-3.5 align-middle font-mono text-xs text-muted-foreground"
                            >
                                {{ p.account_number || '-' }}
                            </td>
                            <td
                                class="p-3.5 align-middle text-sm text-muted-foreground"
                            >
                                {{ p.account_holder || '-' }}
                            </td>
                            <td
                                class="max-w-xs truncate p-3.5 align-middle text-xs text-muted-foreground"
                                :title="p.description || ''"
                            >
                                {{ p.description || '-' }}
                            </td>
                            <td
                                class="p-3.5 text-center align-middle text-xs text-muted-foreground"
                            >
                                {{ p.savings_count }} tabungan
                            </td>
                            <td class="p-3.5 text-right align-middle">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        v-if="can('edit saving providers')"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-muted-foreground hover:text-foreground"
                                        as-child
                                        title="Edit"
                                    >
                                        <Link
                                            :href="`/saving-providers/${p.id}/edit`"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        v-if="can('delete saving providers')"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-muted-foreground hover:text-destructive"
                                        title="Hapus"
                                        @click="confirmDelete(p)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="providers.data.length === 0">
                            <td colspan="7" class="p-10 text-center">
                                <div
                                    class="flex flex-col items-center justify-center gap-2 text-muted-foreground"
                                >
                                    <div class="rounded-full bg-muted p-3">
                                        <Landmark class="h-6 w-6" />
                                    </div>
                                    <p class="font-medium text-foreground">
                                        Belum ada data rekening atau dompet
                                    </p>
                                    <p class="text-xs">
                                        Klik tombol "Tambah Rekening / Dompet"
                                        untuk menambahkan bank, e-wallet, atau
                                        platform investasi.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer / Pagination -->
            <div
                class="flex flex-col gap-3 border-t p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-xs text-muted-foreground">
                    Menampilkan {{ providers.from || 0 }} sampai
                    {{ providers.to || 0 }} dari {{ providers.total }} data
                </div>

                <div class="flex flex-wrap items-center gap-1">
                    <template v-for="(link, i) in providers.links" :key="i">
                        <Button
                            v-if="i === 0"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            :disabled="!link.url"
                            @click="
                                link.url &&
                                router.get(
                                    link.url,
                                    { ...filterState },
                                    { preserveState: true },
                                )
                            "
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <Button
                            v-else-if="i === providers.links.length - 1"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            :disabled="!link.url"
                            @click="
                                link.url &&
                                router.get(
                                    link.url,
                                    { ...filterState },
                                    { preserveState: true },
                                )
                            "
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                        <span
                            v-else-if="link.label === '...'"
                            class="px-2 text-xs text-muted-foreground"
                        >
                            ...
                        </span>
                        <Button
                            v-else
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            class="h-8 min-w-8 px-2.5 text-xs font-medium"
                            :disabled="!link.url"
                            @click="
                                link.url &&
                                router.get(
                                    link.url,
                                    { ...filterState },
                                    { preserveState: true },
                                )
                            "
                        >
                            {{ link.label }}
                        </Button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Dialog
            :open="isDeleteDialogOpen"
            @update:open="isDeleteDialogOpen = $event"
        >
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <div class="mb-2 flex items-center gap-2 text-destructive">
                        <AlertTriangle class="h-5 w-5" />
                        <DialogTitle>Konfirmasi Hapus</DialogTitle>
                    </div>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus
                        <strong>{{ providerToDelete?.name }}</strong
                        >? Tabungan yang terkait tidak ikut terhapus, hanya
                        kehilangan referensi tempat penyimpanannya.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-3 sm:gap-3">
                    <Button
                        variant="outline"
                        @click="isDeleteDialogOpen = false"
                    >
                        Batal
                    </Button>
                    <Button variant="destructive" @click="executeDelete">
                        Hapus
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
