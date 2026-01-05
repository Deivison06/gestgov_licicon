<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IntegracaoAlmoxarifadoController;
use App\Http\Middleware\CheckAlmoxarifadoToken;

Route::middleware([CheckAlmoxarifadoToken::class])->group(function () {

    // Endpoint: http://seudominio.com/api/integracao/contratos-disponiveis
    Route::get('/integracao/contratos-disponiveis', [IntegracaoAlmoxarifadoController::class, 'listarContratos']);

});
