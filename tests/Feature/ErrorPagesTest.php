<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    /**
     * Testa se rotas inexistentes renderizam a tela 404 personalizada do Licicon.
     */
    public function test_404_error_page_renders_with_licicon_theme(): void
    {
        $response = $this->get('/rota-inexistente-'.uniqid());

        $response->assertStatus(404);
        $response->assertSee('404', false);
        $response->assertSee('Página Não Encontrada');
        $response->assertSee('Licicon');
        $response->assertSee('Desenvolvido por');
        $response->assertSee('GestCloud');
    }

    /**
     * Testa se a view 403 compila e renderiza com sucesso.
     */
    public function test_403_view_compiles_successfully(): void
    {
        $view = view('errors.403', [
            'exception' => new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'Acesso negado: Este contrato pertence a outra unidade.'),
        ])->render();

        $this->assertStringContainsString('403', $view);
        $this->assertStringContainsString('Acesso Não Autorizado', $view);
        $this->assertStringContainsString('Este contrato pertence a outra unidade.', $view);
        $this->assertStringContainsString('Licicon', $view);
    }

    /**
     * Testa se a view 500 compila e renderiza com sucesso.
     */
    public function test_500_view_compiles_successfully(): void
    {
        $view = view('errors.500', [
            'exception' => new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'Falha interna de banco de dados.'),
        ])->render();

        $this->assertStringContainsString('500', $view);
        $this->assertStringContainsString('Instabilidade', $view);
        $this->assertStringContainsString('Tentar Novamente', $view);
        $this->assertStringContainsString('Licicon', $view);
    }

    /**
     * Testa se a view 419 compila e renderiza com sucesso.
     */
    public function test_419_view_compiles_successfully(): void
    {
        $view = view('errors.419', [
            'exception' => new \Symfony\Component\HttpKernel\Exception\HttpException(419, 'Page Expired'),
        ])->render();

        $this->assertStringContainsString('419', $view);
        $this->assertStringContainsString('Sessão Expirada', $view);
        $this->assertStringContainsString('Atualizar Página', $view);
    }

    /**
     * Testa se a view 503 compila e renderiza com sucesso.
     */
    public function test_503_view_compiles_successfully(): void
    {
        $view = view('errors.503', [
            'exception' => new \Symfony\Component\HttpKernel\Exception\HttpException(503, 'Service Unavailable'),
        ])->render();

        $this->assertStringContainsString('503', $view);
        $this->assertStringContainsString('Manutenção', $view);
        $this->assertStringContainsString('Verificar Disponibilidade', $view);
    }

    /**
     * Testa se a view 429 compila e renderiza com sucesso.
     */
    public function test_429_view_compiles_successfully(): void
    {
        $view = view('errors.429', [
            'exception' => new \Symfony\Component\HttpKernel\Exception\HttpException(429, 'Too Many Requests'),
        ])->render();

        $this->assertStringContainsString('429', $view);
        $this->assertStringContainsString('Limite de Requisições', $view);
    }

    /**
     * Testa se a view 401 compila e renderiza com sucesso.
     */
    public function test_401_view_compiles_successfully(): void
    {
        $view = view('errors.401', [
            'exception' => new \Symfony\Component\HttpKernel\Exception\HttpException(401, 'Unauthorized'),
        ])->render();

        $this->assertStringContainsString('401', $view);
        $this->assertStringContainsString('Autenticação Necessária', $view);
        $this->assertStringContainsString('Fazer Login', $view);
    }
}
