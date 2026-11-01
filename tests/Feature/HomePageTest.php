<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    /**
     * La ruta raíz sirve el dashboard de GreenCycle (login, registro y árboles).
     */
    public function test_home_page_is_available(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('GreenCycle')
            ->assertSee('Iniciar sesión');
    }
}
