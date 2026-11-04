<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Database,
    LayoutGrid,
    User,
    Tags,
    PiggyBank,
    Landmark,
    WalletCards,
    CalendarClock,
    History,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { usePermission } from '@/composables/usePermission';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const { can } = usePermission();

const mainNavItems = computed<NavItem[]>(() => {
    const raw: (NavItem & {
        permission?: string;
        items?: (NavItem & { permission?: string })[];
    })[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Budget Plan',
            href: '/budget-plans',
            icon: WalletCards,
            permission: 'view budget plans',
        },
        {
            title: 'Tabungan',
            href: '/savings',
            icon: PiggyBank,
            permission: 'view savings',
        },
        {
            title: 'Pengeluaran Rutin',
            href: '/recurring-expenses',
            icon: CalendarClock,
            permission: 'view recurring expenses',
        },
        {
            title: 'Kategori Anggaran',
            href: '/categories',
            icon: Tags,
            permission: 'view categories',
        },
        {
            title: 'Data Master',
            href: '#',
            icon: Database,
            items: [
                {
                    title: 'Pengguna',
                    href: '/users',
                    icon: User,
                    permission: 'view users',
                },
            ],
        },
        {
            title: 'Peran & Hak Akses',
            href: '#',
            icon: ShieldCheck,
            items: [
                {
                    title: 'Peran (Roles)',
                    href: '/roles',
                    permission: 'view roles',
                },
                {
                    title: 'Hak Akses (Permissions)',
                    href: '/permissions',
                    permission: 'view permissions',
                },
            ],
        },
        {
            title: 'Log Aktivitas',
            href: '/activity',
            icon: History,
            permission: 'view activity logs',
        },
        {
            title: 'Rekening & Dompet',
            href: '/saving-providers',
            icon: Landmark,
            permission: 'view saving providers',
        },
    ];

    return raw
        .map((item) => {
            if (item.items) {
                const visibleChildren = item.items.filter((child) =>
                    can(child.permission),
                );
                return visibleChildren.length > 0
                    ? { ...item, items: visibleChildren }
                    : null;
            }
            return can(item.permission) ? item : null;
        })
        .filter((item): item is NavItem => item !== null);
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
