<?php

namespace Database\Seeders;

use App\Models\SeedType;
use Illuminate\Database\Seeder;

class SeedTypeSeeder extends Seeder
{
    /**
     * Idempotente: se puede ejecutar varias veces sin duplicar el catálogo.
     */
    public function run(): void
    {
        SeedType::updateOrCreate(
            ['name' => 'Árbol básico'],
            ['cuidados_por_nivel' => 5, 'monedas_cosecha' => 10],
        );

        SeedType::updateOrCreate(
            ['name' => 'Semilla especial'],
            ['cuidados_por_nivel' => 3, 'monedas_cosecha' => 7],
        );
    }
}
