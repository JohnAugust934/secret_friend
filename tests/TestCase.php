<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken; // <--- Importante

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // CORREÇÃO: Desabilita a verificação de CSRF (Erro 419) em todos os testes.
        // Isso corrige as falhas de autenticação nos testes automatizados.
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
