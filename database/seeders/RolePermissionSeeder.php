<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Admin permissions
            'view admin panel',
            'manage users',
            'manage all chalets',
            'manage all bookings',
            'manage all payments',
            'manage refunds',
            'manage reviews',
            'manage amenities',
            
            // Owner permissions
            'view owner panel',
            'manage own chalets',
            'view own bookings',
            'update own bookings',
            'view own payments',
            'manage own refunds',
            
            // Customer permissions (API only)
            'view own bookings',
            'create bookings',
            'cancel own bookings',
            'view own payments',
            'create payments',
            'create reviews',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Assign permissions to admin
        $adminRole->givePermissionTo([
            'view admin panel',
            'manage users',
            'manage all chalets',
            'manage all bookings',
            'manage all payments',
            'manage refunds',
            'manage reviews',
            'manage amenities',
        ]);

        // Assign permissions to owner
        $ownerRole->givePermissionTo([
            'view owner panel',
            'manage own chalets',
            'view own bookings',
            'update own bookings',
            'view own payments',
            'manage own refunds',
        ]);

        // Assign permissions to customer
        $customerRole->givePermissionTo([
            'view own bookings',
            'create bookings',
            'cancel own bookings',
            'view own payments',
            'create payments',
            'create reviews',
        ]);

        // Assign roles to existing users based on user_type
        User::where('user_type', 'admin')->each(function ($user) use ($adminRole) {
            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
            }
        });

        User::where('user_type', 'owner')->each(function ($user) use ($ownerRole) {
            if (!$user->hasRole('owner')) {
                $user->assignRole('owner');
            }
        });

        User::where('user_type', 'customer')->each(function ($user) use ($customerRole) {
            if (!$user->hasRole('customer')) {
                $user->assignRole('customer');
            }
        });

        $this->command->info('Roles and permissions have been created and assigned successfully!');
    }
}





