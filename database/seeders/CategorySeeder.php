<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Top-level categories (depth 0)
            [
                'name' => 'Electronics',
                'departament_id' => 1, // assuming departament_id 1 is for Electronics
                'parent_id' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fashion',
                'departament_id' => 2, // assuming departament_id 2 is for Fashion
                'parent_id' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //subcategories of Electronics (deph 1)
            [
                'name' => 'Computers',
                'departament_id' => 1, 
                'parent_id' => 1, // parent is electronics
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Smartphones',
                'departament_id' => 1, 
                'parent_id' => 1, // parent is electronics
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

             //subcategories of Electronics (deph 2)
             [
                'name' => 'Laptos',
                'departament_id' => 1, 
                'parent_id' => 3, // parent is Computers
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            [
                'name' => 'Desktops',
                'departament_id' => 1, 
                'parent_id' => 3, // parent is Computers
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //subcategories of Electronics (deph 2)
            [
                'name' => 'Android',
                'departament_id' => 1, 
                'parent_id' => 4, // parent is smartphones
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'iPhones',
                'departament_id' => 1, 
                'parent_id' => 4, // parent is smartphones
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ];

        DB::table('categories')->insert($categories);
    }
}
