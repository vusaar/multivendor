<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignRoleToUserSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('email', 'vusaar@gmail.com')->first();
        $role = Role::where('name', 'super.admin')->first();
        if ($user && $role) {
            $user->assignRole($role);
        }
    }
}
