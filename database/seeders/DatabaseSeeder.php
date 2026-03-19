<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            VusumuziUserSeeder::class,
            AssignRoleToUserSeeder::class,
            // CommonCategoriesSeeder::class, // REMOVED: Replaced by FashionSupportSeeder
            VariationAttributeSeeder::class,
            VariationAttributeValueSeeder::class,
            FashionSupportSeeder::class,
            ExternalProductSeeder::class,
        ]);
    }
}
