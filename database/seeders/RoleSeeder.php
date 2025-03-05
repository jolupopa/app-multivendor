<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Enums\RolesEnum;
use App\Enums\PermissionsEnum;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userRole = Role::create(['name' => RolesEnum::User->value]);
        $vendorRole = Role::create(['name' => RolesEnum::Vendor->value]);
        $adminRole = Role::create(['name' => RolesEnum::Admin->value]);

        $approveVendors = Permission::create(['name' => PermissionsEnum::ApproveVendors->value ]);
        $sellProducts = Permission::create(['name' => PermissionsEnum::SellProducts->value ]);
        $buyProducts = Permission::create(['name' => PermissionsEnum::BuyProducts->value ]);

        // usuarios
            $userRole->syncPermissions([$buyProducts]);
        // proveedores
            $vendorRole->syncPermissions([$sellProducts, $buyProducts]);
        // admins
            $adminRole->syncPermissions([$approveVendors, $sellProducts, $buyProducts]);
    }
}
