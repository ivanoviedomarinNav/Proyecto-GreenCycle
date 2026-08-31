<?php

namespace Database\Seeders;

use App\Models\SeedType;
use Illuminate\Database\Seeder;

class SeedTypeSeeder extends Seeder
{
    public function run(): void
    {
        SeedType::create([
            'name' => 'Árbol básico',
            'cuidados_por_nivel' => 5,
            'monedas_cosecha' => 10,
        ]);

        SeedType::create([
            'name' => 'Semilla especial',
            'cuidados_por_nivel' => 3,
            'monedas_cosecha' => 7,
        ]);
    }
}
