<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'icon' => './backend/assets/media/categories/Buy_&_Sell.svg',
                'name' => 'Buy & Sell',
                'slug' => 'buy-sell',
                'shot_desc' => 'List items for sale or trade',
                'status' => 'active',
            ],
            [
                'id' => 2,
                'icon' => './backend/assets/media/categories/Jobs.svg',
                'name' => 'Jobs',
                'slug' => 'jobs',
                'shot_desc' => 'Post a job opportunity',
                'status' => 'active',
            ],
            [
                'id' => 3,
                'icon' => './backend/assets/media/categories/Business_Directory.svg',
                'name' => 'Business Directory',
                'slug' => 'business-directory',
                'shot_desc' => 'Add your business profile',
                'status' => 'active',
            ],
            [
                'id' => 4,
                'icon' => './backend/assets/media/categories/Services.svg',
                'name' => 'Services',
                'slug' => 'services',
                'shot_desc' => 'Offer your professional services',
                'status' => 'active',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
