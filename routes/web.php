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
use App\Http\Controllers\EtpController;
use App\Http\Controllers\EtpItemController;
use App\Http\Controllers\Admin\PncpController;
use App\Http\Controllers\Admin\PesquisaPrecoController;
use App\Http\Controllers\AdminEtpController;
use App\Http\Controllers\SolicitacaoController;
use App\Http\Controllers\PcaController;
use App\Http\Controllers\FiscalizacaoController;
use App\Http\Controllers\IAController;

// ================================================
// FISCALIZAÇÃO DE CONTRATOS
// ================================================
Route::prefix('admin/fiscalizacoes')
    ->name('admin.fiscalizacoes.')
    ->middleware(['auth', 'verified','can:fiscalizar contratos'])
    ->group(function () {
        Route::get('/buscar-contratos', [FiscalizacaoController::class, 'buscarContratos'])->name('buscar-contratos');
        Route::get('/', [FiscalizacaoController::class, 'index'])->name('index');
        Route::get('/create', [FiscalizacaoController::class, 'create'])->name('create');
        Route::post('/', [FiscalizacaoController::class, 'store'])->name('store');
        Route::get('/selecionar-contrato', [FiscalizacaoController::class, 'selecionarContrato'])->name('selecionar-contrato');
        Route::get('/{id}', [FiscalizacaoController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [FiscalizacaoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FiscalizacaoController::class, 'update'])->name('update');
        Route::delete('/{id}', [FiscalizacaoController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/pdf', [FiscalizacaoController::class, 'gerarRelatorio'])->name('pdf');
        Route::get('/{id}/relatorio-tecnico', [FiscalizacaoController::class, 'imprimirRelatorioTecnico'])->name('relatorio-tecnico');
        Route::get('/{id}/notificacoes', [FiscalizacaoController::class, 'imprimirNotificacoes'])->name('notificacoes');
    });

// ================================================
// PCA - PLANO DE CONTRATAÇÃO ANUAL
// ================================================
Route::prefix('admin/pcas')->name('admin.pcas.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [PcaController::class, 'index'])->name('index');
    Route::get('/create', [PcaController::class, 'create'])->name('create');
    Route::post('/', [PcaController::class, 'store'])->name('store');
    Route::get('/{id}', [PcaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [PcaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PcaController::class, 'update'])->name('update');
    Route::delete('/{id}', [PcaController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/pdf', [PcaController::class, 'gerarPdf'])->name('pdf');
});

// ================================================
// SOLICITAÇÕES INTERNAS (Chat Administrativo)
// ================================================
Route::prefix('admin/solicitacoes')->name('admin.solicitacoes.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [SolicitacaoController::class, 'index'])->name('index');
    Route::get('/create', [SolicitacaoController::class, 'create'])->name('create');
    Route::post('/', [SolicitacaoController::class, 'store'])->name('store');
    Route::get('/{id}', [SolicitacaoController::class, 'show'])->name('show');
    Route::post('/{id}/responder', [SolicitacaoController::class, 'responder'])->name('responder');
    Route::patch('/{id}/finalizar', [SolicitacaoController::class, 'finalizar'])->name('finalizar');
});

// Rotas de perfil (usuário logado)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ================================================
// IA — Geração de conteúdo nos textareas (DFD/ETP)
// ================================================
Route::middleware(['auth', 'throttle:ia-write'])->group(function () {
    Route::post('/admin/ia/gerar-conteudo', [IAController::class, 'gerarConteudo'])
        ->name('admin.ia.gerar');
});

// Rota inicial -> Dashboard
Route::get('/', [PrefeituraController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

// ================================================
// ETP INTELIGENTE (Secretário e Admin)
// ================================================
Route::prefix('admin/etps')->name('admin.etps.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [EtpController::class, 'index'])->name('index');
    Route::get('/create', [EtpController::class, 'create'])->name('create');
    Route::post('/', [EtpController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [EtpController::class, 'edit'])->name('edit');
    Route::post('/importar-itens', [EtpController::class, 'importarItensEtp'])->name('importar-itens');

    // ← NOVA ROTA: criar item rápido via modal (AJAX)
    Route::post('/criar-item-rapido', [EtpController::class, 'criarItemRapido'])->name('criar-item-rapido');

    // Rotas com parâmetro por último (para não conflitar com rotas estáticas acima)
    Route::get('/{etp}', [EtpController::class, 'show'])->name('show');
    Route::put('/{id}', [EtpController::class, 'update'])->name('update');
    Route::delete('/{id}', [EtpController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/export-itens', [EtpController::class, 'exportItens'])->name('export-itens');
    Route::get('/{id}/pdf', [EtpController::class, 'gerarPdf'])->name('pdf');

});


// Admin ETPs Recebidos
Route::prefix('admin/etps-recebidos')->name('admin.etps_recebidos.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [AdminEtpController::class, 'index'])->name('index');
    Route::get('/{etp}', [AdminEtpController::class, 'show'])->name('show');
    Route::put('/{etp}/status', [AdminEtpController::class, 'alterarStatus'])->name('status');
    Route::post('/{etp}/processo', [AdminEtpController::class, 'vincularProcesso'])->name('processo');
});

// Admin Itens ETP
Route::prefix('admin/etp-itens')->name('admin.etp_itens.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [EtpItemController::class, 'index'])->name('index');
    Route::get('/create', [EtpItemController::class, 'create'])->name('create');
    Route::post('/', [EtpItemController::class, 'store'])->name('store');
    Route::post('/importar-excel', [EtpItemController::class, 'importarExcel'])->name('importar_excel');
    Route::get('/{item}/edit', [EtpItemController::class, 'edit'])->name('edit');
    Route::put('/{item}', [EtpItemController::class, 'update'])->name('update');
    Route::delete('/{item}', [EtpItemController::class, 'destroy'])->name('destroy');
});

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

        Route::prefix('contratos')->name('contratos.')->group(function () {
            Route::get('/manual/{id}', [ContratoManualController::class, 'showManual'])->name('show.manual');
            Route::get('/sistema/{id}', [ContratoManualController::class, 'showSistema'])->name('show.sistema');
        });

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
        // 4.5. POLLING ASSÍNCRONO DE DOCUMENTOS
        // ========================================
        Route::get('/documentos-async/status/{token}', [\App\Http\Controllers\ProcessoController::class, 'verificarStatusDownloadDocs'])
            ->name('documentos.async.status');
            
        Route::get('/documentos-async/download/{token}', [\App\Http\Controllers\ProcessoController::class, 'finalizarDownloadDocs'])
            ->name('documentos.async.download');

        Route::get('processos/gerar-numeros', [ProcessoController::class, 'gerarNumeros'])
            ->name('processos.gerar-numeros');

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

            // As rotas já estão definidas no seu arquivo de rotas:
            Route::post('/republicar-edital', [ProcessoController::class, 'republicarEdital'])
                ->name('processos.republicar.edital');

            Route::post('/republicar-processo', [ProcessoController::class, 'republicarProcesso'])
                ->name('processos.republicar.processo');

            Route::post('/cancelar-licitacao', [ProcessoController::class, 'cancelarLicitacao'])
                ->name('processos.cancelar.licitacao');

            // Adicione esta linha às rotas do ProcessoController
            Route::post('/reverter-cancelamento', [ProcessoController::class, 'reverterCancelamento'])
                ->name('processos.reverter-cancelamento');

            Route::post('/adiar-licitacao', [ProcessoController::class, 'adiarLicitacao'])
                ->name('processos.adiar.licitacao');

            Route::post('/iniciar', [ProcessoController::class, 'storeDetalhe'])
                ->name('processos.detalhes.store');

            Route::put('/status', [ProcessoController::class, 'updateStatus'])
                ->name('processos.status.update');

            // Vínculo Dinâmico de ETP Inteligente
            Route::get('/etps-disponiveis', [ProcessoController::class, 'getEtpsDisponiveis'])
                ->name('processos.etps.disponiveis');
            Route::post('/vincular-etp', [ProcessoController::class, 'vincularEtp'])
                ->name('processos.etps.vincular');
            Route::post('/desvincular-etp', [ProcessoController::class, 'desvincularEtp'])
                ->name('processos.etps.desvincular');

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

            // Homologação parcial — criar nova homologação para os lotes pendentes
            Route::post('/homologacoes', [FinalizacaoProcessoController::class, 'gerarNovaHomologacao'])
                ->name('homologacoes.store');

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

            Route::get('/{processo}/contratacao/{contratacao}/edit', [AtaController::class, 'editContratacao'])
                ->name('contratacao.edit');

            Route::put('/{processo}/contratacao/{contratacao}', [AtaController::class, 'updateContratacao'])
                ->name('contratacao.update');

            Route::delete('/{processo}/contratacao/{contratacao}', [AtaController::class, 'destroyContratacao'])
                ->name('contratacao.destroy');
            Route::delete('/{processo}/excluir-todas-contratacoes', [AtaController::class, 'excluirTodasContratacoes'])
                ->name('excluir.todas.contratacoes');

            // Dentro do grupo de atas, adicione essas novas rotas:

            // Desfazer/cancelar contrato
            Route::post('/{processo}/desfazer-contrato/{documento}', [AtaController::class, 'desfazerContrato'])
                ->name('contrato.desfazer');
            // Exportar saldo para PDF
            Route::get('/{processo}/exportar-saldo-pdf', [AtaController::class, 'exportarSaldoPdf'])
                ->name('exportar.saldo.pdf');
        });
    });

// ================================================
// PNCP — API JSON (compartilhada entre módulos)
// ================================================
Route::prefix('admin/pncp')->name('admin.pncp.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/search', [PncpController::class, 'search'])->name('search');
    Route::get('/items/{cnpj}/{ano}/{sequencial}', [PncpController::class, 'getItems'])->name('items');
    Route::get('/mercado/search', [PncpController::class, 'buscarMercado'])->name('mercado.search');
    Route::get('/contratacao/{cnpj}/{ano}/{sequencial}', [PncpController::class, 'getContratacao'])->name('contratacao');
    Route::get('/atas/search', [PncpController::class, 'buscarAtas'])->name('atas.search');
});

// ================================================
// PESQUISA DE PREÇOS — Módulo dedicado (página web)
// ================================================
Route::prefix('admin/pesquisa-preco')->name('admin.pesquisa_preco.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [PesquisaPrecoController::class, 'index'])->name('index');
    Route::post('/itens', [PesquisaPrecoController::class, 'store'])->name('itens.store');
    Route::delete('/itens/{id}', [PesquisaPrecoController::class, 'destroy'])->name('itens.destroy');
    Route::get('/itens/processo/{processoId}', [PesquisaPrecoController::class, 'listarPorProcesso'])->name('itens.processo');
});

require __DIR__ . '/auth.php';
