<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            ['name' => '鶏むね肉', 'category' => 'main', 'emoji' => '🍗', 'kcal' => 110, 'protein' => 23, 'fat' => 1.5, 'carbs' => 0],
            ['name' => 'サーモン', 'category' => 'main', 'emoji' => '🐟', 'kcal' => 208, 'protein' => 20, 'fat' => 13, 'carbs' => 0],
            ['name' => '卵', 'category' => 'main', 'emoji' => '🥚', 'kcal' => 151, 'protein' => 12, 'fat' => 10, 'carbs' => 0.3],
            ['name' => 'サラダチキン', 'category' => 'main', 'emoji' => '🍱', 'kcal' => 110, 'protein' => 22, 'fat' => 1.8, 'carbs' => 0.5],
            ['name' => '玄米', 'category' => 'staple', 'emoji' => '🍚', 'kcal' => 165, 'protein' => 2.8, 'fat' => 1, 'carbs' => 35],
            ['name' => 'オートミール', 'category' => 'staple', 'emoji' => '🥣', 'kcal' => 350, 'protein' => 13, 'fat' => 6, 'carbs' => 60],
            ['name' => 'ブロッコリー', 'category' => 'side', 'emoji' => '🥦', 'kcal' => 34, 'protein' => 4.3, 'fat' => 0.5, 'carbs' => 7],
            ['name' => 'バナナ', 'category' => 'other', 'emoji' => '🍌', 'kcal' => 86, 'protein' => 1.1, 'fat' => 0.2, 'carbs' => 23],
        ];

        foreach ($foods as $food) {
            Food::firstOrCreate(['name' => $food['name']], $food);
        }
    }
}
