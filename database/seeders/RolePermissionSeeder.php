<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view saving providers',
            'create saving providers',
            'edit saving providers',
            'delete saving providers',
            'view savings',
            'create savings',
            'edit savings',
            'delete savings',
            'view recurring expenses',
            'create recurring expenses',
            'edit recurring expenses',
            'delete recurring expenses',
            'view budget plans',
            'create budget plans',
            'edit budget plans',
            'delete budget plans',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'view activity logs',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create Super Admin role
        $superAdminRole = Role::findOrCreate('super admin');
        // No need to sync permissions for super admin if using Gate::before

        // Create Admin role and assign some permissions
        $adminRole = Role::findOrCreate('admin');
        $adminRole->syncPermissions([
            'view users',
            'create users',
            'edit users',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view saving providers',
            'create saving providers',
            'edit saving providers',
            'delete saving providers',
            'view savings',
            'create savings',
            'edit savings',
            'delete savings',
            'view recurring expenses',
            'create recurring expenses',
            'edit recurring expenses',
            'delete recurring expenses',
            'view budget plans',
            'create budget plans',
            'edit budget plans',
            'delete budget plans',
            'view activity logs',
        ]);

        // Create User role for standard users
        $userRole = Role::findOrCreate('user');
        $userRole->syncPermissions([
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view saving providers',
            'create saving providers',
            'edit saving providers',
            'delete saving providers',
            'view savings',
            'create savings',
            'edit savings',
            'delete savings',
            'view recurring expenses',
            'create recurring expenses',
            'edit recurring expenses',
            'delete recurring expenses',
            'view budget plans',
            'create budget plans',
            'edit budget plans',
            'delete budget plans',
        ]);

        // Assign super admin role to the first user
        $user = User::first();
        if ($user) {
            $user->assignRole($superAdminRole);
        }
    }
}
