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
            'claim-tickets',
            'close-tickets',
            'reopen-tickets',
            'view-ticket-attachments',
            'upload-ticket-attachments',
            
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

        foreach ($permissions as $permission) {
            $permissionClass::create(['name' => $permission]);
        }

        // Create roles
        $superAdmin = $roleClass::create(['name' => 'super-admin']);
        $admin = $roleClass::create(['name' => 'admin']);
        $manager = $roleClass::create(['name' => 'manager']);
        $technician = $roleClass::create(['name' => 'technician']);
        $user = $roleClass::create(['name' => 'user']);

        // Assign permissions to roles
        $superAdmin->givePermissionTo($permissionClass::all());

        $admin->givePermissionTo([
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

        $manager->givePermissionTo([
            'view-manager-dashboard',
            'view-users',
            'view-tickets',
            'view-all-tickets',
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

        $technician->givePermissionTo([
            'view-technician-dashboard',
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'claim-tickets',
            'close-tickets',
            'view-ticket-attachments',
            'upload-ticket-attachments',
            'view-ticket-activities',
            'receive-notifications',
        ]);

        $user->givePermissionTo([
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'view-ticket-attachments',
            'upload-ticket-attachments',
            'receive-notifications',
        ]);

        // Create super admin user
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@soluxio.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'status' => 'active',
        ]);

        $superAdminUser->assignRole('super-admin');

        // Create sample admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin.user@soluxio.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'status' => 'active',
        ]);

        $adminUser->assignRole('admin');

        // Create sample manager
        $managerUser = User::create([
            'name' => 'Manager User',
            'email' => 'manager@soluxio.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'status' => 'active',
        ]);

        $managerUser->assignRole('manager');

        // Create sample technician
        $techUser = User::create([
            'name' => 'Technician User',
            'email' => 'tech@soluxio.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'status' => 'active',
        ]);

        $techUser->assignRole('technician');

        // Create sample regular user
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@soluxio.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'status' => 'active',
        ]);

        $regularUser->assignRole('user');
    }
}