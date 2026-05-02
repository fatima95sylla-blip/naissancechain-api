<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'users.create',
            'users.read',
            'users.update',
            'users.delete',
            'naissances.create',
            'naissances.read',
            'naissances.update',
            'naissances.delete',
            'verification.read',
            'sync.process',
            'admin.access',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roles = [
            'ADMIN' => [
                'users.create',
                'users.read',
                'users.update',
                'users.delete',
                'naissances.create',
                'naissances.read',
                'naissances.update',
                'naissances.delete',
                'verification.read',
                'sync.process',
                'admin.access',
            ],
            'AGENT' => [
                'naissances.create',
                'naissances.read',
                'naissances.update',
                'verification.read',
            ],
            'ECOLE' => [
                'naissances.create',
                'naissances.read',
                'verification.read',
            ],
            'SANTE' => [
                'naissances.create',
                'naissances.read',
                'verification.read',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('Roles and permissions created successfully.');
    }
}
