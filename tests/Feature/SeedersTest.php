<?php

namespace Tests\Feature;

use App\Models\SeedType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_the_catalog_and_the_demo_user(): void
    {
        $this->seed();

        $this->assertDatabaseCount('seed_types', 2);

        $demo = User::where('email', 'UsuarioPrueba@gmail.com')->first();
        $this->assertNotNull($demo);
        $this->assertTrue(Hash::check('Prueba123', $demo->password));
    }

    public function test_seeders_are_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(2, SeedType::count());
        $this->assertSame(1, User::where('email', 'UsuarioPrueba@gmail.com')->count());
    }
}
