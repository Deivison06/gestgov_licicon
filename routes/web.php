<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProcessoController;
use App\Http\Controllers\PrefeituraController;
use App\Http\Controllers\ContratacaoController;
use App\Http\Controllers\ContratoProcessoController;
use App\Http\Controllers\FinalizacaoProcessoController;

// Rotas de perfil (usuário logado)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rota inicial -> Dashboard
Route::get('/', [PrefeituraController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('admin.dashboard');

// Grupo admin
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        /**
         * Usuários
         */
        Route::resource('usuarios', UsuarioController::class)->names([
            'index'   => 'usuarios.index',
            'create'  => 'usuarios.create',
            'store'   => 'usuarios.store',
            'edit'    => 'usuarios.edit',
            'update'  => 'usuarios.update',
            'destroy' => 'usuarios.destroy',
        ])->except(['show']); // não tem show no CRUD de usuário

        /**
         * Prefeituras
         */
        Route::resource('prefeituras', PrefeituraController::class)->names([
            'index'   => 'prefeituras.index',
            'create'  => 'prefeituras.create',
            'store'   => 'prefeituras.store',
            'show'    => 'prefeituras.show',
            'edit'    => 'prefeituras.edit',
            'update'  => 'prefeituras.update',
            'destroy' => 'prefeituras.destroy',
        ]);

        /**
         * Unidades (vinculadas à prefeitura)
         */
        Route::post('prefeituras/{prefeitura}/unidades', [UnidadeController::class, 'storeUnidade'])->name('prefeituras.unidades.store');
        Route::get('unidades/{id}', [UnidadeController::class, 'getUnidade'])->name('unidades.get');
        Route::put('unidades/{id}', [UnidadeController::class, 'updateUnidade'])->name('unidades.update');
        Route::delete('unidades/{id}', [UnidadeController::class, 'destroyUnidade'])->name('unidades.destroy');

        /**
         * Processos
         */
        Route::resource('processos', ProcessoController::class)->names([
            'index'   => 'processos.index',
            'create'  => 'processos.create',
            'store'   => 'processos.store',
            'show'    => 'processos.show',
            'edit'    => 'processos.edit',
            'update'  => 'processos.update',
            'destroy' => 'processos.destroy',
        ]);

        // Rota extra para iniciar processo (se não for o mesmo que create)
        Route::get('processos/{processo}/iniciar', [ProcessoController::class, 'iniciar'])->name('processos.iniciar');
        Route::post('processos/{processo}/iniciar', [ProcessoController::class, 'storeDetalhe'])->name('processos.detalhes.store');
        Route::get('processos/{processo}/pdf', [ProcessoController::class, 'gerarPdf'])->name('processos.pdf');
        Route::get('/processos/{processo}/visualizar-pdf', [ProcessoController::class, 'visualizarPdf'])
            ->name('processos.visualizar-pdf');
        Route::get('/processo/{processo}/documento/{tipo}/baixar', [ProcessoController::class, 'baixarDocumento'])->name('processo.documento.dowload');
        Route::get('/processo/{processo}/documentos/baixar-todos', [ProcessoController::class, 'baixarTodosDocumentos'])->name('processo.documento.dowload-all');

        // Rota extra para Finalizar processo (se não for o mesmo que create)
        Route::get('processos/{processo}/finalizar', [FinalizacaoProcessoController::class, 'finalizar'])->name('processos.finalizar');
        Route::post('processos/{processo}/finalizar', [FinalizacaoProcessoController::class, 'storeFinalizacao'])->name('processos.finalizacao.store');
        Route::get('finalizacao/processos/{processo}/pdf', [FinalizacaoProcessoController::class, 'gerarPdf'])->name('processos.finalizacao.pdf');
        Route::get('/finalizacao/processo/{processo}/documento/{tipo}/baixar', [FinalizacaoProcessoController::class, 'baixarDocumento'])->name('processo.finalizacao.documento.dowload');
        Route::get('/finalizacao/processo/{processo}/documentos/baixar-todos', [FinalizacaoProcessoController::class, 'baixarTodosDocumentos'])->name('processo.finalizacao.documento.dowload-all');

        // Novas rotas para vencedores
        Route::post('/processos/{processo}/vencedores', [FinalizacaoProcessoController::class, 'storeVencedores'])
            ->name('processos.finalizacao.vencedores.store');

        Route::get('/processos/{processo}/vencedores', [FinalizacaoProcessoController::class, 'getVencedores'])
            ->name('processos.finalizacao.vencedores.get');

        // Rota para importar excel
        Route::post('/processos/{processo}/finalizacao/importar-excel', [FinalizacaoProcessoController::class, 'importarExcel'])
            ->name('processos.finalizacao.importar-excel');

        // Rota para exibir a view de contrato
        Route::get('processos/{processo}/contrato', [ContratoProcessoController::class, 'contrato'])->name('processos.contrato');

        // Rota para gerar PDF do contrato
        Route::get('contrato/processos/{processo}/pdf', [ContratoProcessoController::class, 'gerarPdf'])->name('processos.contrato.pdf');

        // Rota para baixar contrato específico
        Route::get('/contrato/processo/{processo}/baixar', [ContratoProcessoController::class, 'baixarContrato'])->name('processo.contrato.download');

        // Rotas para reservas
        Route::post('/processos/{processo}/finalizacao/reservas', [ReservaController::class, 'store'])
            ->name('processos.finalizacao.reservas.store');

        Route::get('/processos/{processo}/finalizacao/reservas', [ReservaController::class, 'getReservas'])
            ->name('processos.finalizacao.reservas.get');

        /**
         * Contratações - Rotas específicas por processo
         */
        Route::prefix('processos/{processo}')->group(function () {
            // Rotas de contratação
            Route::post('contratacao', [ContratacaoController::class, 'store'])
                ->name('processos.contratacao.store');

            // ROTA NOVA: Contratações em lote (com checkboxes)
            Route::post('contratacoes-em-lote', [ContratacaoController::class, 'storeEmLote'])
                ->name('processos.contratacao.store-em-lote');

            Route::get('contratacao/{contratacao}/edit', [ContratacaoController::class, 'edit'])
                ->name('processos.contratacao.edit');

            Route::put('contratacao/{contratacao}', [ContratacaoController::class, 'update'])
                ->name('processos.contratacao.update');

            Route::put('contratacao/{contratacao}/confirmar', [ContratacaoController::class, 'confirmar'])
                ->name('processos.contratacao.confirmar');

            Route::delete('contratacao/{contratacao}', [ContratacaoController::class, 'destroy'])
                ->name('processos.contratacao.destroy');

            Route::get('contratacao/listar', [ContratacaoController::class, 'listar'])
                ->name('processos.contratacao.listar');

            Route::get('vencedores/{vencedor}/lotes-disponiveis', [ContratacaoController::class, 'lotesDisponiveis'])
                ->name('processos.contratacao.lotes-disponiveis');

                // Nova rota para verificar disponibilidade
    Route::post('estoque/verificar', [ContratacaoController::class, 'verificarDisponibilidade'])
        ->name('processos.estoque.verificar');

    // Rota para vincular contratações a contrato
    Route::post('contratacoes/vincular-contrato', [ContratacaoController::class, 'vincularAoContrato'])
        ->name('processos.contratacoes.vincular-contrato');

    // Rota para relatório
    Route::get('estoque/relatorio', [ContratacaoController::class, 'relatorio'])
        ->name('processos.estoque.relatorio');

    // Rota para recalcular estoque (admin)
    Route::post('estoque/recalcular', [ContratacaoController::class, 'recalcularEstoque'])
        ->name('processos.estoque.recalcular');

    // Rota para dashboard de estoque
    Route::get('estoque/dashboard', [ContratacaoController::class, 'dashboardEstoque'])
        ->name('processos.estoque.dashboard');
        });

        /**
         * Contratações - Rotas gerais (para compatibilidade com código existente)
         * Mantenha essas rotas para não quebrar código que já referencia 'admin.processos.contratacao.*'
         */
        Route::get('contratacao', [ContratacaoController::class, 'index'])
            ->name('admin.processos.contratacao.index');

        Route::post('contratacao', [ContratacaoController::class, 'store'])
            ->name('admin.processos.contratacao.store');

        Route::get('contratacao/listar', [ContratacaoController::class, 'listar'])
            ->name('admin.processos.contratacao.listar');

        Route::put('contratacao/{contratacao}', [ContratacaoController::class, 'finalizar'])
            ->name('admin.processos.contratacao.finalizar');

        Route::delete('contratacao/{contratacao}', [ContratacaoController::class, 'destroy'])
            ->name('admin.processos.contratacao.destroy');
    });

require __DIR__ . '/auth.php';
