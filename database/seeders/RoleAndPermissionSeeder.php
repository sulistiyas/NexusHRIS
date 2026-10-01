<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permission Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 4 Role Utama
        $roles = [
            'super_admin',
            'hr_admin',
            'manager',
            'employee',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Permissions Dasar & Fitur Sprint 1-5
        $permissions = [
            'manage_users',
            'manage_employees',
            'manage_attendance',
            'approve_leave',
            'manage_payroll',
            'view_reports',
            'manage_reimbursements',
            'manage_assets',
            'view_analytics',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Assign semua permissions ke super_admin
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }

        // Assign permissions operasional ke hr_admin
        $hrAdmin = Role::where('name', 'hr_admin')->first();
        if ($hrAdmin) {
            $hrAdmin->syncPermissions([
                'manage_employees',
                'manage_attendance',
                'approve_leave',
                'manage_payroll',
                'view_reports',
                'manage_reimbursements',
                'manage_assets',
                'view_analytics',
            ]);
        }

        // Assign permissions ke manager
        $manager = Role::where('name', 'manager')->first();
        if ($manager) {
            $manager->syncPermissions([
                'manage_attendance',
                'approve_leave',
                'view_reports',
                'view_analytics',
            ]);
        }
    }
}
