<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Cuenta demo documentada en el README.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'UsuarioPrueba@gmail.com'],
            ['name' => 'UsuarioPr', 'password' => 'Prueba123'],
        );
    }
}
