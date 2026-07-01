<?php

namespace Database\Seeders;

use HasinHayder\Tyro\Models\Privilege;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PharmacyRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Pharmacy Owner', 'slug' => 'owner'],
            ['name' => 'Pharmacy Manager', 'slug' => 'manager'],
            ['name' => 'Pharmacist', 'slug' => 'pharmacist'],
            ['name' => 'Cashier', 'slug' => 'cashier'],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }

        $definitions = [
            [
                'name' => 'POS Access',
                'slug' => 'pos.access',
                'description' => 'Access the point-of-sale screen.',
                'roles' => ['owner', 'manager', 'pharmacist', 'cashier'],
            ],
            [
                'name' => 'Manage Inventory',
                'slug' => 'inventory.manage',
                'description' => 'Manage products, batches, and stock.',
                'roles' => ['owner', 'manager'],
            ],
            [
                'name' => 'View Reports',
                'slug' => 'reports.view',
                'description' => 'View pharmacy sales and inventory reports.',
                'roles' => ['owner', 'manager'],
            ],
            [
                'name' => 'Manage Settings',
                'slug' => 'settings.manage',
                'description' => 'Manage tenant settings, branches, and staff.',
                'roles' => ['owner'],
            ],
        ];

        $roleMap = Role::query()
            ->whereIn('slug', collect($definitions)->flatMap(fn (array $definition) => $definition['roles'])->unique()->all())
            ->get()
            ->keyBy('slug');

        foreach ($definitions as $definition) {
            $privilege = Privilege::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                Arr::only($definition, ['name', 'description'])
            );

            $roleIds = collect($definition['roles'])
                ->map(fn (string $slug) => $roleMap->get($slug)?->id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($roleIds !== []) {
                $privilege->roles()->sync($roleIds);
            }
        }
    }
}
