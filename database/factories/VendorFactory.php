<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'shop_name' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'logo' => null,
            'address' => $this->faker->address(),
            'status' => 'approved',
        ];
    }
}
