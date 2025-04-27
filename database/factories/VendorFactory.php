<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\VendorStatusEnum;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class VendorFactory extends Factory
{


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->unique()->id(),
            'status' => VendorStatusEnum::Approved,
            'store_name' => 'Vendor' . fake()->word(),
            'store_address' => fake()->address(),
            'store_phone' => fake()->phoneNumber(),
        ];
    }


}
