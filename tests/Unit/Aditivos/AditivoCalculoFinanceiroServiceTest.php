<?php

namespace Tests\Unit\Aditivos;

use App\Models\ContratoManual;
use App\Models\EmpresaContrato;
use App\Models\IncidenteContratual;
use App\Models\Prefeitura;
use App\Models\Unidade;
use App\Services\Aditivos\AditivoCalculoFinanceiroService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura do cálculo financeiro de aditivos (percentuais acumulados de
 * acréscimo/supressão sempre sobre o valor inicial atualizado, nunca
 * compostos, e numeração ordinal) exigido pela atualização do módulo de
 * Termos Aditivos (apontamentos do Tribunal de Contas).
 */
class AditivoCalculoFinanceiroServiceTest extends TestCase
{
    use RefreshDatabase;

    private AditivoCalculoFinanceiroService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AditivoCalculoFinanceiroService;
    }

    private function criarContratoManual(float $valorTotalOriginal = 100000.0): ContratoManual
    {
        $prefeitura = Prefeitura::create([
            'nome' => 'Prefeitura Teste',
            'cnpj' => '12.345.678/0001-90',
            'endereco' => 'Rua Teste, 123',
            'telefone' => '(86) 99999-9999',
            'email' => 'teste@prefeitura.pi.gov.br',
            'autoridade_competente' => 'Prefeito Teste',
        ]);

        $unidade = Unidade::create([
            'prefeitura_id' => $prefeitura->id,
            'nome' => 'Secretaria de Teste',
            'servidor_responsavel' => 'Servidor Teste',
        ]);

        $empresa = EmpresaContrato::create([
            'razao_social' => 'Empresa Teste LTDA',
            'cnpj' => '98.765.432/0001-10',
            'endereco' => 'Av. Teste, 456',
            'prefeitura_id' => $prefeitura->id,
        ]);

        return ContratoManual::create([
            'empresa_id' => $empresa->id,
            'prefeitura_id' => $prefeitura->id,
            'unidade_id' => $unidade->id,
            'numero_processo' => '001/2026',
            'numero_contrato' => '001/2026',
            'tipo_contrato' => 'Serviço',
            'objeto' => 'Objeto de teste',
            'valor_total' => $valorTotalOriginal,
            'valor_total_original' => $valorTotalOriginal,
        ]);
    }

    private function criarAditivo(ContratoManual $contrato, array $dados): IncidenteContratual
    {
        return IncidenteContratual::create(array_merge([
            'contratavel_id' => $contrato->id,
            'contratavel_type' => ContratoManual::class,
            'tipo' => 'valor',
            'categoria' => 'compras_servicos',
            'valor_total_base' => $contrato->valor_total,
        ], $dados));
    }

    public function test_valor_inicial_atualizado_usa_valor_total_original_do_contrato_manual(): void
    {
        $contrato = $this->criarContratoManual(valorTotalOriginal: 100000.0);
        $contrato->valor_total = 150000.0; // já mutado por um aditivo anterior
        $contrato->save();

        $this->assertSame(100000.0, $this->service->valorInicialAtualizado($contrato));
    }

    public function test_resumo_financeiro_sem_aditivos_anteriores(): void
    {
        $contrato = $this->criarContratoManual(valorTotalOriginal: 100000.0);

        $aditivo = $this->criarAditivo($contrato, [
            'natureza_alteracao' => 'acrescimo',
            'percentual_acrescimo' => 10.0,
            'percentual_supressao' => 0.0,
        ]);

        $resumo = $this->service->resumoFinanceiro($aditivo, $contrato);

        $this->assertSame(100000.0, $resumo['valor_inicial_atualizado']);
        $this->assertSame(10000.0, $resumo['valor_acrescido_neste_aditivo']);
        $this->assertSame(0.0, $resumo['valor_suprimido_neste_aditivo']);
        $this->assertSame(10.0, $resumo['percentual_acumulado_acrescimo']);
        $this->assertSame(0.0, $resumo['percentual_acumulado_supressao']);
        $this->assertSame(110000.0, $resumo['valor_final_contrato']);
    }

    public function test_percentuais_acumulam_sobre_valor_inicial_sem_compor(): void
    {
        $contrato = $this->criarContratoManual(valorTotalOriginal: 100000.0);

        // Aditivo 1: +10% (criado primeiro, id menor)
        $this->criarAditivo($contrato, [
            'natureza_alteracao' => 'acrescimo',
            'percentual_acrescimo' => 10.0,
            'percentual_supressao' => 0.0,
        ]);

        // Aditivo 2: +5% e -3% (acréscimo e supressão combinados)
        $atual = $this->criarAditivo($contrato, [
            'natureza_alteracao' => 'acrescimo_e_supressao',
            'percentual_acrescimo' => 5.0,
            'percentual_supressao' => 3.0,
        ]);

        $resumo = $this->service->resumoFinanceiro($atual, $contrato);

        // Acumulado = 10 + 5 = 15% (soma simples, nunca composta)
        $this->assertSame(15.0, $resumo['percentual_acumulado_acrescimo']);
        $this->assertSame(3.0, $resumo['percentual_acumulado_supressao']);

        // Valor final = 100000 + 15% - 3% = 112000
        $this->assertSame(112000.0, $resumo['valor_final_contrato']);
    }

    public function test_numero_ordinal_conta_todos_os_aditivos_do_mesmo_contrato(): void
    {
        $contrato = $this->criarContratoManual();

        $primeiro = $this->criarAditivo($contrato, ['tipo' => 'prazo', 'meses_prorrogacao' => 6]);
        $segundo = $this->criarAditivo($contrato, ['tipo' => 'prazo', 'meses_prorrogacao' => 3]);

        $this->assertSame(1, $this->service->numeroOrdinal($primeiro, $contrato));
        $this->assertSame(2, $this->service->numeroOrdinal($segundo, $contrato));
    }

    public function test_numero_ordinal_nao_conta_aditivos_de_outro_contrato(): void
    {
        $contratoA = $this->criarContratoManual();
        $contratoB = $this->criarContratoManual();

        $this->criarAditivo($contratoA, ['tipo' => 'prazo', 'meses_prorrogacao' => 6]);
        $aditivoB = $this->criarAditivo($contratoB, ['tipo' => 'prazo', 'meses_prorrogacao' => 6]);

        $this->assertSame(1, $this->service->numeroOrdinal($aditivoB, $contratoB));
    }
}
