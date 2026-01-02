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
use App\Http\Controllers\AtaController;
use App\Http\Controllers\ContratoManualController; // Adicionado

// Rotas de perfil (usuário logado)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rota inicial -> Dashboard
Route::get('/', [PrefeituraController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

// ================================================
// GRUPO ADMIN
// ================================================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        // ========================================
        // 1. USUÁRIOS
        // ========================================
        Route::resource('usuarios', UsuarioController::class)
            ->names([
                'index'   => 'usuarios.index',
                'create'  => 'usuarios.create',
                'store'   => 'usuarios.store',
                'edit'    => 'usuarios.edit',
                'update'  => 'usuarios.update',
                'destroy' => 'usuarios.destroy',
            ])
            ->except(['show']); // não tem show no CRUD de usuário

        // ========================================
        // 2. PREFEITURAS
        // ========================================
        Route::resource('prefeituras', PrefeituraController::class)
            ->names([
                'index'   => 'prefeituras.index',
                'create'  => 'prefeituras.create',
                'store'   => 'prefeituras.store',
                'show'    => 'prefeituras.show',
                'edit'    => 'prefeituras.edit',
                'update'  => 'prefeituras.update',
                'destroy' => 'prefeituras.destroy',
            ]);

        // ========================================
        // 3. CONTRATOS MANUAIS (EXTERNOS)
        // ========================================
         Route::get('contratos', [ContratoManualController::class, 'index'])
            ->name('contratos.index');
            
        Route::resource('contratos', ContratoManualController::class)
            ->names([
                'create'  => 'contratos.create',
                'store'   => 'contratos.store',
                'edit'    => 'contratos.edit',
                'update'  => 'contratos.update',
                'destroy' => 'contratos.destroy',
            ])
            ->except(['show']);

        // Rota para atualizar dados da empresa via AJAX
        Route::put('/contratos/{id}/empresa', [ContratoManualController::class, 'updateEmpresa'])
            ->name('contratos.empresa.update');

        // ========================================
        // 4. UNIDADES (vinculadas à prefeitura)
        // ========================================
        Route::prefix('prefeituras/{prefeitura}')->group(function () {
            Route::post('/unidades', [UnidadeController::class, 'storeUnidade'])
                ->name('prefeituras.unidades.store');
        });

        Route::prefix('unidades')->group(function () {
            Route::get('/{id}', [UnidadeController::class, 'getUnidade'])
                ->name('unidades.get');
            Route::put('/{id}', [UnidadeController::class, 'updateUnidade'])
                ->name('unidades.update');
            Route::delete('/{id}', [UnidadeController::class, 'destroyUnidade'])
                ->name('unidades.destroy');
        });

        // ========================================
        // 5. PROCESSOS
        // ========================================
        Route::resource('processos', ProcessoController::class)
            ->names([
                'index'   => 'processos.index',
                'create'  => 'processos.create',
                'store'   => 'processos.store',
                'show'    => 'processos.show',
                'edit'    => 'processos.edit',
                'update'  => 'processos.update',
                'destroy' => 'processos.destroy',
            ]);

        // ========================================
        // 6. DETALHES DO PROCESSO
        // ========================================
        Route::prefix('processos/{processo}')->group(function () {
            // Iniciar processo
            Route::get('/iniciar', [ProcessoController::class, 'iniciar'])
                ->name('processos.iniciar');
            
            Route::post('/iniciar', [ProcessoController::class, 'storeDetalhe'])
                ->name('processos.detalhes.store');

            // Gerar PDF
            Route::get('/pdf', [ProcessoController::class, 'gerarPdf'])
                ->name('processos.pdf');
            
            Route::get('/visualizar-pdf', [ProcessoController::class, 'visualizarPdf'])
                ->name('processos.visualizar-pdf');

            // Download documentos
            Route::get('/documento/{tipo}/baixar', [ProcessoController::class, 'baixarDocumento'])
                ->name('processo.documento.dowload');
            
            Route::get('/documentos/baixar-todos', [ProcessoController::class, 'baixarTodosDocumentos'])
                ->name('processo.documento.dowload-all');
        });

        // ========================================
        // 7. FINALIZAÇÃO DO PROCESSO
        // ========================================
        Route::prefix('processos/{processo}/finalizacao')->name('processos.finalizacao.')->group(function () {
            // Finalizar processo
            Route::get('/', [FinalizacaoProcessoController::class, 'finalizar'])
                ->name('finalizar');
            
            Route::post('/', [FinalizacaoProcessoController::class, 'storeFinalizacao'])
                ->name('store');

            // Vencedores
            Route::post('/vencedores', [FinalizacaoProcessoController::class, 'storeVencedores'])
                ->name('vencedores.store');
            
            Route::get('/vencedores', [FinalizacaoProcessoController::class, 'getVencedores'])
                ->name('vencedores.get');

            // Importar Excel
            Route::post('/importar-excel', [FinalizacaoProcessoController::class, 'importarExcel'])
                ->name('importar-excel');

            // Reservas
            Route::post('/reservas', [ReservaController::class, 'store'])
                ->name('reservas.store');
            
            Route::get('/reservas', [ReservaController::class, 'getReservas'])
                ->name('reservas.get');

            // Gerar PDF da finalização
            Route::get('/pdf', [FinalizacaoProcessoController::class, 'gerarPdf'])
                ->name('pdf');

            // Download documentos da finalização
            Route::get('/documento/{tipo}/baixar', [FinalizacaoProcessoController::class, 'baixarDocumento'])
                ->name('documento.dowload');
            
            Route::get('/documentos/baixar-todos', [FinalizacaoProcessoController::class, 'baixarTodosDocumentos'])
                ->name('documento.dowload-all');
        });

        // ========================================
        // 8. CONTRATO DO PROCESSO
        // ========================================
        Route::prefix('processos/{processo}/contrato')->name('processos.contrato.')->group(function () {
            // View de contrato
            Route::get('/', [ContratoProcessoController::class, 'contrato'])
                ->name('index');

            // Gerenciamento de campos do contrato
            Route::post('/salvar-campo', [ContratoProcessoController::class, 'salvarCampoContrato'])
                ->name('salvar-campo');
            
            Route::get('/dados', [ContratoProcessoController::class, 'obterDadosContrato'])
                ->name('dados');

            // Gerar PDF do contrato
            Route::get('/pdf', [ContratoProcessoController::class, 'gerarPdf'])
                ->name('gerar-pdf');

            // Download do contrato
            Route::get('/download', [ContratoProcessoController::class, 'baixarContrato'])
                ->name('download');
        });

        // ========================================
        // 9. CONTRATAÇÕES E ESTOQUE
        // ========================================
        Route::prefix('processos/{processo}')->group(function () {
            // Contratações individuais
            Route::post('/contratacao', [ContratacaoController::class, 'store'])
                ->name('processos.contratacao.store');
            
            Route::get('/contratacao/{contratacao}/edit', [ContratacaoController::class, 'edit'])
                ->name('processos.contratacao.edit');
            
            Route::put('/contratacao/{contratacao}', [ContratacaoController::class, 'update'])
                ->name('processos.contratacao.update');
            
            Route::put('/contratacao/{contratacao}/confirmar', [ContratacaoController::class, 'confirmar'])
                ->name('processos.contratacao.confirmar');
            
            Route::delete('/contratacao/{contratacao}', [ContratacaoController::class, 'destroy'])
                ->name('processos.contratacao.destroy');

            // Contratações em lote
            Route::post('/contratacoes-em-lote', [ContratacaoController::class, 'storeEmLote'])
                ->name('processos.contratacao.store-em-lote');
            
            Route::get('/contratacao/listar', [ContratacaoController::class, 'listar'])
                ->name('processos.contratacao.listar');

            // Lotes disponíveis por vencedor
            Route::get('/vencedores/{vencedor}/lotes-disponiveis', [ContratacaoController::class, 'lotesDisponiveis'])
                ->name('processos.contratacao.lotes-disponiveis');

            // Gerenciamento de estoque
            Route::prefix('estoque')->name('processos.estoque.')->group(function () {
                Route::post('/verificar', [ContratacaoController::class, 'verificarDisponibilidade'])
                    ->name('verificar');
                
                Route::get('/relatorio', [ContratacaoController::class, 'relatorio'])
                    ->name('relatorio');
                
                Route::post('/recalcular', [ContratacaoController::class, 'recalcularEstoque'])
                    ->name('recalcular');
                
                Route::get('/dashboard', [ContratacaoController::class, 'dashboardEstoque'])
                    ->name('dashboard');
            });

            // Vincular contratações a contrato
            Route::post('/contratacoes/vincular-contrato', [ContratacaoController::class, 'vincularAoContrato'])
                ->name('processos.contratacoes.vincular-contrato');
        });

        // ========================================
        // 10. CONTRATAÇÕES - ROTAS GERAIS (para compatibilidade)
        // ========================================
        Route::prefix('contratacao')->name('processos.contratacao.')->group(function () {
            Route::get('/', [ContratacaoController::class, 'index'])
                ->name('index');
            
            Route::post('/', [ContratacaoController::class, 'store'])
                ->name('store');
            
            Route::get('/listar', [ContratacaoController::class, 'listar'])
                ->name('listar');
            
            Route::put('/{contratacao}', [ContratacaoController::class, 'finalizar'])
                ->name('finalizar');
            
            Route::delete('/{contratacao}', [ContratacaoController::class, 'destroy'])
                ->name('destroy');
        });

        // ========================================
        // 11. CONTRATO - ROTAS ALTERNATIVAS (para compatibilidade)
        // ========================================
        Route::prefix('contrato')->name('processo.contrato.')->group(function () {
            Route::get('/processos/{processo}/pdf', [ContratoProcessoController::class, 'gerarPdf'])
                ->name('gerar-pdf');
            
            Route::get('/processo/{processo}/baixar', [ContratoProcessoController::class, 'baixarContrato'])
                ->name('download');
        });

        // ========================================
        // 12. FINALIZAÇÃO - ROTAS ALTERNATIVAS (para compatibilidade)
        // ========================================
        Route::prefix('finalizacao')->name('processo.finalizar')->group(function () {
            Route::get('/processos/{processo}/pdf', [FinalizacaoProcessoController::class, 'gerarPdf'])
                ->name('documento.pdf');
            
            Route::get('/processo/{processo}/documento/{tipo}/baixar', [FinalizacaoProcessoController::class, 'baixarDocumento'])
                ->name('documento.dowload');
            
            Route::get('/processo/{processo}/documentos/baixar-todos', [FinalizacaoProcessoController::class, 'baixarTodosDocumentos'])
                ->name('documento.dowload-all');
        });

        Route::get('/processos/by-prefeitura', [ProcessoController::class, 'byPrefeitura'])
        ->name('atas.processos-by-prefeitura');


        Route::prefix('atas')->name('atas.')->group(function () {
            Route::get('/', [AtaController::class, 'index'])->name('index');
            Route::get('/dashboard', [AtaController::class, 'dashboard'])->name('dashboard');
            Route::get('/{processo}', [AtaController::class, 'show'])->name('show');
            Route::post('/{processo}/gerar', [AtaController::class, 'gerarESalvarAta'])->name('gerar');
            Route::get('/{processo}/download', [AtaController::class, 'downloadAta'])->name('download');
            Route::get('/{processo}/dados', [AtaController::class, 'getDadosAta'])->name('dados');
            
            // Novas rotas para a nova lógica
            Route::get('/{processo}/lotes-disponiveis/{vencedorId}', [AtaController::class, 'getLotesDisponiveis'])->name('lotes.disponiveis');
            Route::post('/{processo}/contratacao-direta', [AtaController::class, 'criarContratacaoDireta'])->name('contratacao.direta');
            Route::post('/{processo}/marcar-contratado', [AtaController::class, 'marcarComoContratado'])->name('marcar.contratado');
            
            // Rotas para salvar dados
            Route::post('/{processo}/salvar-campo', [AtaController::class, 'salvarCampoContrato'])->name('salvar.campo');
            Route::post('/{processo}/salvar-assinantes', [AtaController::class, 'salvarAssinantesAta'])->name('salvar.assinantes');
            Route::post('/{processo}/salvar-contratacoes', [AtaController::class, 'salvarContratacoesSelecionadas'])->name('salvar.contratacoes');
            
            Route::post('/relatorio-consolidado', [AtaController::class, 'relatorioConsolidado'])->name('relatorio.consolidado');

            // Nas suas rotas de atas, adicione:
            Route::get('/{processo}/get-contratacoes-pendentes', [AtaController::class, 'getContratacoesPendentes'])->name('get.contratacoes.pendentes');
            Route::get('/{processo}/get-contratacoes-atualizadas', [AtaController::class, 'getContratacoesAtualizadas'])->name('get.contratacoes.atualizadas');
            
            // Adicione esta rota dentro do grupo de atas, antes do fechamento do grupo:
            Route::get('/{processo}/contrato-itens/{documentoId}', [AtaController::class, 'getItensContrato'])
                ->name('contrato.itens');

            Route::get('{processo}/debug-contratos', [AtaController::class, 'debugContratos'])
                ->name('admin.atas.debug');

            Route::get('{processo}/download/{nomeArquivo}', [AtaController::class, 'downloadAta'])
            ->name('admin.atas.download.file');
        
        });
    });

require __DIR__ . '/auth.php';