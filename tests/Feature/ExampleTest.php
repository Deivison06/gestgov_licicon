<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // A raiz ("/") é o dashboard protegido (auth + verified): um visitante
        // não autenticado é redirecionado para o login.
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
