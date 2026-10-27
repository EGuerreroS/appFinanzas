<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Datos de prueba: 2 ingresos y un gasto por categoría en el mes actual.
 * No se llama desde DatabaseSeeder para no mezclarse con datos reales:
 *   php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'superusuario@live.es')->firstOrFail();
        $month = now()->startOfMonth();
        $day = fn (int $d) => $month->copy()->day($d)->toDateString();

        $user->incomes()->create(['amount' => 30000, 'description' => 'Nómina', 'date' => $day(1)]);
        $user->incomes()->create(['amount' => 30000, 'description' => 'Nómina', 'date' => $day(15)]);

        $expenses = [
            'Renta / Hipoteca' => [12000, 'Renta', 2],
            'Súper' => [2350.50, 'Costco', 5],
            'Servicios' => [1650, 'Luz + internet', 8],
            'Transporte' => [900, 'Gasolina', 9],
            'Salud' => [650, 'Farmacia', 14],
            'Comidas fuera' => [1450, 'Tacos y cena', 12],
            'Salidas' => [100, 'Cine', 10],
            'Suscripciones' => [450, 'Netflix + Spotify', 3],
            'Ropa' => [1200, 'Tenis', 20],
            'Ahorro' => [8000, 'Transferencia ahorro', 16],
            'Inversión' => [3000, 'CETES', 16],
        ];

        foreach ($expenses as $category => [$amount, $description, $d]) {
            $categoryId = $user->categories()->where('name', $category)->value('id');

            if ($categoryId) {
                $user->expenses()->create([
                    'category_id' => $categoryId,
                    'amount' => $amount,
                    'description' => $description,
                    'date' => $day($d),
                ]);
            }
        }
    }
}
