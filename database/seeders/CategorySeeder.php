<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Categorías iniciales para cada usuario.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Renta / Hipoteca', 'group' => Category::ESSENTIAL, 'color' => '#1565C0'],
            ['name' => 'Súper', 'group' => Category::ESSENTIAL, 'color' => '#2E7D32'],
            ['name' => 'Servicios', 'group' => Category::ESSENTIAL, 'color' => '#00838F'],
            ['name' => 'Transporte', 'group' => Category::ESSENTIAL, 'color' => '#6A1B9A'],
            ['name' => 'Salud', 'group' => Category::ESSENTIAL, 'color' => '#C62828'],
            ['name' => 'Comidas fuera', 'group' => Category::NON_ESSENTIAL, 'color' => '#EF6C00'],
            ['name' => 'Salidas', 'group' => Category::NON_ESSENTIAL, 'color' => '#AD1457'],
            ['name' => 'Suscripciones', 'group' => Category::NON_ESSENTIAL, 'color' => '#4527A0'],
            ['name' => 'Ropa', 'group' => Category::NON_ESSENTIAL, 'color' => '#795548'],
            ['name' => 'Ahorro', 'group' => Category::SAVINGS, 'color' => '#00897B'],
            ['name' => 'Inversión', 'group' => Category::SAVINGS, 'color' => '#558B2F'],
        ];

        User::all()->each(function (User $user) use ($data) {
            foreach ($data as $category) {
                $user->categories()->firstOrCreate(['name' => $category['name']], $category);
            }
        });
    }
}
