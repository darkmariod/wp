<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ningún test debe tocar el disco real de la Biblioteca: borrar un
        // recurso con archivos lo resolvería y dejaría carpetas en storage/.
        Storage::fake((string) config('biblioteca.disk'));
    }
}
