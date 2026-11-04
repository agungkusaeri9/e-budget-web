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

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Budget Plan',
        href: '/budget-plans',
        icon: WalletCards,
    },
    {
        title: 'Tabungan',
        href: '/savings',
        icon: PiggyBank,
    },
    {
        title: 'Pengeluaran Rutin',
        href: '/recurring-expenses',
        icon: CalendarClock,
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
            },
            {
                title: 'Kategori',
                href: '/categories',
                icon: Tags,
            },
            {
                title: 'Tempat Penyimpanan',
                href: '/saving-providers',
                icon: Landmark,
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
            },
            {
                title: 'Hak Akses (Permissions)',
                href: '/permissions',
            },
        ],
    },
    {
        title: 'Log Aktivitas',
        href: '/activity',
        icon: History,
    },
]);
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
