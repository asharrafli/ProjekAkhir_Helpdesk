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
            
            // Activity logs
            'view-activity-logs',
            
            // System settings
            'manage-settings',
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
            'view-roles',
            'view-permissions',
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'assign-tickets',
            'view-all-tickets',
            'view-activity-logs',
        ]);

        $manager->givePermissionTo([
            'view-admin-dashboard',
            'view-users',
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'assign-tickets',
            'view-all-tickets',
        ]);

        $technician->givePermissionTo([
            'view-tickets',
            'create-tickets',
            'edit-tickets',
        ]);

        $user->givePermissionTo([
            'view-tickets',
            'create-tickets',
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