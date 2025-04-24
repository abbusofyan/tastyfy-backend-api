<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            "Value Meals_Boneless Chicken Rice with Achar.jpg",
            "Value Meals_Creamy Chicken Carbonara.jpg",
            "Value Meals_Gram Dhall Rice w Butter Chicken.jpg",
            "Value Meals_HongKong Fried Noodles.jpg",
            "Value Meals_Hor Fun.jpg",
            "Value Meals_Penne with Chicken Bolognese.jpg",
            "Value Meals_Singapore Curry Styled Chicken.jpg",
            "Value Meals_Thai Basil Minced Chicken With Rice.jpg",
            "Value Meals_White Carrot Cake.jpg",
            "sandwiches_Chicken Ham & Cheese.png",
            "sandwiches_Crab Mayonnaise Sandwich.png",
            "sandwiches_Egg Mayonnaise with Cheese.png",
            "sandwiches_Tuna & Cucumber Mayonnaise.png",
            "Onigiri_Chicken Floss.png",
            "Onigiri_Chicken Teriyaki.png",
            "Onigiri_Salmon Butter.png",
            "Onigiri_Tuna Mayonnaise.png",
            "Sushi Roll_Sushi Roll Chicken Teriyaki.png",
            "Sushi Roll_Spicy Tuna.png",
            "Sushi Roll_Wasabi Crab.png",
            "Pizza_Neo Pizza Hawaiian.jpg",
            "Pizza_Neo Pizza Seafood.jpg",
            "Pizza_Neo Pizza Supreme.jpg",
            "Wrap_WRAP 'N' ROLL CHEESEY SAUSAGE.png",
            "Wrap_WRAP 'N' ROLL FRIED CHICKEN.png",
        ];

        DB::table('products')->truncate();

        foreach ($products as $product) {
            $item = explode('_', $product);
            DB::table('products')->insert([
                'category' => $item[0],
                'name' => pathinfo($item[1], PATHINFO_FILENAME),
                'image' => asset('/product_images/' .  $item[1]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

    }
}
