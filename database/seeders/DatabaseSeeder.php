<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin Viera',
            'email' => 'admin@viera.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create sample pelanggan
        User::factory()->count(5)->create();

        // Categories
        $categories = [
            ['name' => 'Kue Tart', 'slug' => 'kue-tart', 'description' => 'Kue tart untuk berbagai acara'],
            ['name' => 'Cupcake', 'slug' => 'cupcake', 'description' => 'Cupcake manis dan lucu'],
            ['name' => 'Cookies', 'slug' => 'cookies', 'description' => 'Cookies renyah'],
        ];

        foreach ($categories as $c) {
            Category::create($c);
        }

        // Products
        $cats = Category::all();
        foreach ($cats as $cat) {
            for ($i = 1; $i <= 4; $i++) {
                Product::create([
                    'category_id' => $cat->id,
                    'name' => $cat->name . ' Sample ' . $i,
                    'slug' => Str::slug($cat->name . '-sample-' . $i) . '-' . Str::random(4),
                    'description' => 'Deskripsi singkat untuk produk ' . $i,
                    'price' => rand(20000, 150000),
                    'stock' => rand(5, 50),
                ]);
            }
        }
    }
}
