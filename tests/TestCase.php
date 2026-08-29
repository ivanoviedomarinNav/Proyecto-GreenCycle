<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // para impedir que las pruebas intenten cargar el manifiesto de recursos frontend.
        $this->withoutVite();
    }
}
