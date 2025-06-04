<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Super Admin permissions (full access)
            'manage all',

            // Vendor Admin permissions
            'manage own vendor',
            'manage own products',
            'manage own orders',
            'view own sales',

            // General CRUD for reference (can be assigned as needed)
            'create vendor',
            'edit vendor',
            'delete vendor',
            'view vendor',
            'create product',
            'edit product',
            'delete product',
            'view product',
            'create order',
            'edit order',
            'delete order',
            'view order',
            'manage categories',
            'manage brands',
            'manage users',
            'manage roles',
            'manage permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['name' => $permission, 'guard_name' => 'web']
            );
        }
    }
}
