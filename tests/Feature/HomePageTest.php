<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_home_page_is_available(): void
    {
        $response = $this->get(route('home'));
        $response
            ->assertOk()
            ->assertSee('Desarrollo backend con Laravel');
    }
}
