<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VusumuziUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'vusaar@gmail.com'],
            [
                'name' => 'Vusumuzi Ndhlovu',
                'email' => 'vusaar@gmail.com',
                'password' => Hash::make('password'),
            ]
        );
    }
}
