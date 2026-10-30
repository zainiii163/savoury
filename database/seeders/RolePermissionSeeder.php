<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed RBAC catalogue only. Never create users or passwords here.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'Manage menu', 'slug' => 'manage-menu'],
            ['name' => 'Manage orders', 'slug' => 'manage-orders'],
            ['name' => 'Collect cash', 'slug' => 'collect-cash'],
            ['name' => 'Manage settings', 'slug' => 'manage-settings'],
            ['name' => 'View reports', 'slug' => 'view-reports'],
            ['name' => 'View audit logs', 'slug' => 'view-audit-logs'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['name'],
                ]
            );
        }

        $admin = Role::query()->updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full restaurant administration access.',
            ]
        );

        $staff = Role::query()->updateOrCreate(
            ['slug' => 'staff'],
            [
                'name' => 'Staff',
                'description' => 'Order handling and cash collection.',
            ]
        );

        $admin->permissions()->sync(Permission::query()->pluck('id'));
        $staff->permissions()->sync(
            Permission::query()
                ->whereIn('slug', ['manage-orders', 'collect-cash', 'view-reports'])
                ->pluck('id')
        );
    }
}
