<?php

namespace Database\Seeders;
use App\Models\User;
use App\Models\Vendor;
use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'User',
            'email' => 'user@gmail.com',
        ])->assignRole(RolesEnum::User->value);

        $vendor = User::factory()->create([
            'name' => 'Vendor',
            'email' => 'vendor@gmail.com',
        ]);

        $vendor->assignRole(RolesEnum::Vendor->value);

        Vendor::create([
            'user_id' => $vendor->id,
            'status' => VendorStatusEnum::Approved,
            'store_name' => 'Vendor Store',
            'store_address' => fake()->address(),
            'store_phone' => fake()->phoneNumber(),
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
        ])->assignRole(RolesEnum::Admin->value);
    }
}
