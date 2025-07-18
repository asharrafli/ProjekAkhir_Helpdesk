<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Note: This seeder requires Spatie Permission package to be installed
        // Run: composer install first, then run migrations and seeders
        
        if (!class_exists('Spatie\Permission\Models\Role')) {
            echo "Spatie Permission package not installed. Run 'composer install' first.\n";
            return;
        }

        $roleClass = 'Spatie\Permission\Models\Role';
        $permissionClass = 'Spatie\Permission\Models\Permission';
        $registrarClass = 'Spatie\Permission\PermissionRegistrar';
        
        app()[$registrarClass]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard permissions
            'view-admin-dashboard',
            'view-manager-dashboard',
            'view-technician-dashboard',
            
            // User management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            
            // Role management
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            
            // Permission management
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            
            // Ticket management
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'delete-tickets',
            'assign-tickets',
            'view-all-tickets',
            'comment-on-tickets',
            'view-internal-notes',
            'claim-tickets',
            'close-tickets',
            'reopen-tickets',
            'view-ticket-attachments',
            'upload-ticket-attachments',
            'view-assigned-tickets',
            
            // Activity logs
            'view-activity-logs',
            'view-ticket-activities',
            
            // Reports and Analytics
            'view-reports',
            'generate-reports',
            'export-reports',
            'view-analytics',
            'view-performance-charts',
            
            // Notifications
            'receive-notifications',
            'manage-notifications',
            
            // System settings
            'manage-settings',
            'manage-categories',
            
            // Bulk operations
            'bulk-ticket-operations',
        ];

        // Use firstOrCreate untuk permissions
        foreach ($permissions as $permission) {
            $permissionClass::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // firstOrCreate roles
        $superAdmin = $roleClass::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = $roleClass::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $manager = $roleClass::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $technician = $roleClass::firstOrCreate(['name' => 'technician', 'guard_name' => 'web']);
        $user = $roleClass::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Assign permissions to roles
        $superAdmin->syncPermissions($permissionClass::all());

        $admin->syncPermissions([
            'view-admin-dashboard',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-roles',
            'create-roles',
            'edit-roles',
            'view-permissions',
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'delete-tickets',
            'assign-tickets',
            'view-all-tickets',
            'comment-on-tickets',
            'view-internal-notes',
            'claim-tickets',
            'close-tickets',
            'reopen-tickets',
            'view-ticket-attachments',
            'upload-ticket-attachments',
            'view-activity-logs',
            'view-ticket-activities',
            'view-reports',
            'generate-reports',
            'export-reports',
            'manage-settings',
            'manage-categories',
            'bulk-ticket-operations',
            'receive-notifications',
            'manage-notifications',
        ]);

        $manager->syncPermissions([
            'view-manager-dashboard',
            'view-users',
            'view-tickets',
            'view-all-tickets',
            'comment-on-tickets',
            'view-ticket-attachments',
            'view-activity-logs',
            'view-ticket-activities',
            'view-reports',
            'generate-reports',
            'export-reports',
            'view-analytics',
            'view-performance-charts',
            'receive-notifications',
        ]);

        $technician->syncPermissions([
            'view-technician-dashboard',
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'comment-on-tickets',
            'view-internal-notes',
            'claim-tickets',
            'close-tickets',
            'view-ticket-attachments',
            'upload-ticket-attachments',
            'view-ticket-activities',
            'receive-notifications',
            'view-assigned-tickets',
        ]);

        $user->syncPermissions([
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'comment-on-tickets',
            'view-ticket-attachments',
            'upload-ticket-attachments',
            'receive-notifications',
        ]);

        // ✅ Ganti User::create() dengan firstOrCreate()
        
        // Create super admin user
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@soluxio.com'], // Kondisi pencarian
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'status' => 'active',
            ]
        );

        if (!$superAdminUser->hasRole('super-admin')) {
            $superAdminUser->assignRole('super-admin');
        }

        // Create sample admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin.user@soluxio.com'], // Kondisi pencarian
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'status' => 'active',
            ]
        );

        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        // Create sample manager
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@soluxio.com'], // Kondisi pencarian
            [
                'name' => 'Manager User',
                'password' => bcrypt('password'),
                'is_admin' => false,
                'status' => 'active',
            ]
        );

        if (!$managerUser->hasRole('manager')) {
            $managerUser->assignRole('manager');
        }

        // Create sample technician
        $techUser = User::firstOrCreate(
            ['email' => 'tech@soluxio.com'], // Kondisi pencarian
            [
                'name' => 'Technician User',
                'password' => bcrypt('password'),
                'is_admin' => false,
                'status' => 'active',
            ]
        );

        if (!$techUser->hasRole('technician')) {
            $techUser->assignRole('technician');
        }

        // Create sample regular user
        $regularUser = User::firstOrCreate(
            ['email' => 'user@soluxio.com'], // Kondisi pencarian
            [
                'name' => 'Regular User',
                'password' => bcrypt('password'),
                'is_admin' => false,
                'status' => 'active',
            ]
        );

        if (!$regularUser->hasRole('user')) {
            $regularUser->assignRole('user');
        }

        $this->command->info('✅ Roles and permissions seeded successfully!');
        $this->command->info('📧 Super Admin: admin@soluxio.com | Password: password');
        $this->command->info('📧 Admin: admin.user@soluxio.com | Password: password');
        $this->command->info('📧 Manager: manager@soluxio.com | Password: password');
        $this->command->info('📧 Technician: tech@soluxio.com | Password: password');
        $this->command->info('📧 User: user@soluxio.com | Password: password');
    }
}