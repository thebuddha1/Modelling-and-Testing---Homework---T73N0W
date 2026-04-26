<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Túra tapasztalatok'],
            ['name' => 'Karbantartás'],
            ['name' => 'Egyéb'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
