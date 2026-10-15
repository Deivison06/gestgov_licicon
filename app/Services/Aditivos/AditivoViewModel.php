<?php

namespace App\Services\Aditivos;

use App\Enums\FatorAtrasoEnum;
use App\Enums\LimiteLegalObraEnum;
use App\Enums\NaturezaAlteracaoAditivoEnum;
use App\Enums\ResponsabilidadeAtrasoEnum;
use App\Enums\SituacaoExecucaoContratualEnum;
use App\Enums\TipoObjetoContratoEnum;
use App\Models\IncidenteContratual;
use Illuminate\Support\Collection;

/**
 * Dados já resolvidos para a geração dos 9 documentos de aditivo (Capa,
 * Solicitação, Aceite e Documentação, Parecer Técnico, Dotação Orçamentária,
 * Parecer Jurídico, Autorização, Termo Obras, Termo Compras). Centraliza aqui
 * os valores decididos (enums, booleanos, números) para que os templates
 * Blade fiquem só com @if/{{ }} — a redação dos textos em si mora em
 * AditivoTextoJuridicoService.
 *
 * Montado por AditivoViewModelFactory::build().
 */
class AditivoViewModel
{
    public function __construct(
        public readonly IncidenteContratual $incidente,
        public readonly string $tipo,          // prazo|valor|prazo_valor
        public readonly string $categoria,     // compras_servicos|obras
        public readonly ?NaturezaAlteracaoAditivoEnum $naturezaAlteracao,
        public readonly ?TipoObjetoContratoEnum $tipoObjetoContrato,
        public readonly ?bool $contratoContinuo,
        public readonly ?ResponsabilidadeAtrasoEnum $responsabilidadeAtraso,
        public readonly ?LimiteLegalObraEnum $limiteLegalObras,
        public readonly ?SituacaoExecucaoContratualEnum $situacaoExecucao,
        /** @var list<FatorAtrasoEnum> */
        public readonly array $fatoresAtraso,
        public readonly int $numeroOrdinal,
        public readonly array $resumoFinanceiro, // de AditivoCalculoFinanceiroService::resumoFinanceiro(), vazio se tipo === prazo
        public readonly bool $temValorPeriodoProrrogado,
        public readonly ?float $valorPeriodoProrrogado,
        public readonly string $objetoContrato,
        public readonly string $numeroContrato,
        public readonly Collection $dotacoes, // Collection<IncidenteContratualDotacao>
    ) {}

    public function envolvePrazo(): bool
    {
        return in_array($this->tipo, ['prazo', 'prazo_valor'], true);
    }

    public function envolveValor(): bool
    {
        return in_array($this->tipo, ['valor', 'prazo_valor'], true);
    }

    public function ehObras(): bool
    {
        return $this->categoria === 'obras';
    }

    public function temAcrescimo(): bool
    {
        return $this->envolveValor() && $this->naturezaAlteracao?->temAcrescimo() === true;
    }

    public function temSupressao(): bool
    {
        return $this->envolveValor() && $this->naturezaAlteracao?->temSupressao() === true;
    }

    /**
     * Dispara a exigência do ato "Informação de Disponibilidade Orçamentária".
     */
    public function exigeDotacaoOrcamentaria(): bool
    {
        return $this->temAcrescimo() || $this->temValorPeriodoProrrogado;
    }

    /**
     * art. 107 (contratos contínuos) ou art. 111 (demais) da Lei nº 14.133/2021
     * — só tem sentido quando o aditivo envolve prazo.
     */
    public function artigoFundamentacaoPrazo(): ?string
    {
        if (! $this->envolvePrazo()) {
            return null;
        }

        return $this->contratoContinuo ? 'art. 107' : 'art. 111';
    }

    /**
     * Alínea do art. 124, I, da Lei nº 14.133/2021 — sempre "b" (alteração
     * quantitativa). A opção "a" (qualitativa) foi removida por decisão do
     * cliente (ver docs/ajuste) e não é mais suportada pelo sistema.
     */
    public function alineaArt124(): string
    {
        return 'b';
    }

    public function numeroOrdinalFormatado(): string
    {
        return $this->numeroOrdinal.'º';
    }

    public function tituloTipoAlteracao(): string
    {
        $partes = [];

        if ($this->envolvePrazo()) {
            $partes[] = 'PRORROGAÇÃO DE PRAZO';
        }

        if ($this->envolveValor()) {
            $partes[] = match (true) {
                $this->temAcrescimo() && $this->temSupressao() => 'ACRÉSCIMO E SUPRESSÃO DE VALOR',
                $this->temAcrescimo() => 'ACRÉSCIMO DE VALOR',
                $this->temSupressao() => 'SUPRESSÃO DE VALOR',
                default => 'ALTERAÇÃO DE VALOR',
            };
        }

        return implode(' E ', $partes);
    }

    public function tituloDocumento(): string
    {
        return $this->numeroOrdinalFormatado().' TERMO ADITIVO DE '.$this->tituloTipoAlteracao()." AO CONTRATO Nº {$this->numeroContrato}";
    }
}
