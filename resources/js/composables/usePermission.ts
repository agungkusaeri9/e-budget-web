import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermission() {
    const page = usePage();
    const roles = computed<string[]>(
        () => ((page.props.auth as any)?.roles as string[]) ?? [],
    );
    const permissions = computed<string[]>(
        () => ((page.props.auth as any)?.permissions as string[]) ?? [],
    );

    const isSuperAdmin = computed(() => roles.value.includes('super admin'));

    const can = (permission?: string): boolean => {
        if (!permission) return true;
        if (isSuperAdmin.value) return true;
        if (roles.value.length === 0 && permissions.value.length === 0) {
            return true;
        }
        return permissions.value.includes(permission);
    };

    const hasRole = (role: string): boolean => roles.value.includes(role);

    return { roles, permissions, isSuperAdmin, can, hasRole };
}
