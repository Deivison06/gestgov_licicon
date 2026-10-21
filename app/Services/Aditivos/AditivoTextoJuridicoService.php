<?php

namespace App\Services\Aditivos;

use App\Enums\ResponsabilidadeAtrasoEnum;
use App\Helpers\ValorPorExtenso;
use Carbon\Carbon;

/**
 * Único lugar com a árvore de decisão textual dos documentos de aditivo
 * (tipo × categoria × natureza × contínuo/não-contínuo × compra/serviço ×
 * responsabilidade pelo atraso). Os 9 templates Blade chamam estes métodos
 * em vez de reimplementar a lógica condicional — isso elimina a duplicação
 * que existia entre termo_compras.blade.php e termo_obras.blade.php.
 *
 * Todo número monetário já formatado em pt-BR (number_format "0,00").
 */
class AditivoTextoJuridicoService
{
    private function fmt(float $valor): string
    {
        return number_format($valor, 2, ',', '.');
    }

    /**
     * "R$ 10.000,00 (dez mil reais)" — convenção usada nos demais documentos
     * jurídicos do sistema (ver App\Helpers\ValorPorExtenso).
     */
    private function valorComExtenso(float $valor): string
    {
        return 'R$ '.$this->fmt($valor).' ('.ValorPorExtenso::escrever($valor).')';
    }

    /**
     * Cláusula "Do Objeto" do Termo Aditivo — a maior duplicação hoje entre
     * termo_compras.blade.php e termo_obras.blade.php. Monta os blocos
     * independentes de acréscimo/supressão/prazo, cada um condicionado à
     * natureza da alteração deste aditivo.
     */
    public function clausulaObjetoTermoAditivo(AditivoViewModel $vm): array
    {
        $blocos = [];

        if ($vm->envolvePrazo()) {
            $artigo = $vm->artigoFundamentacaoPrazo();
            $meses = (int) ($vm->incidente->meses_prorrogacao ?? 0);
            $mesesExtenso = strtoupper(ValorPorExtenso::numero($meses));
            $unidade = $vm->ehObras() ? 'dia' : 'mês';
            $unidadePlural = $vm->ehObras() ? 'dias' : 'meses';
            $unidadeTexto = $meses === 1 ? $unidade : $unidadePlural;

            $dataBase = $vm->incidente->data_finalizacao_base
                ? Carbon::parse($vm->incidente->data_finalizacao_base)
                : null;

            if ($dataBase && $meses > 0) {
                $dataFim = $dataBase->copy()->addMonths($meses);
                Carbon::setLocale('pt_BR');
                $periodoTexto = ", compreendendo o período de {$dataBase->translatedFormat('d \d\e F \d\e Y')} a {$dataFim->translatedFormat('d \d\e F \d\e Y')}";
            } else {
                $periodoTexto = '';
            }

            $blocos['prazo'] = "prorrogar o prazo de vigência do instrumento contratual em referência, firmado entre as partes pactuantes, por mais {$meses} ({$mesesExtenso}) {$unidadeTexto}{$periodoTexto}, nos termos do {$artigo} da Lei Federal nº 14.133/2021";
        }

        if ($vm->envolveValor()) {
            $alinea = $vm->alineaArt124();
            $blocos['objeto_alteracao'] = "a alteração quantitativa dos serviços previstos no contrato referente a \"{$vm->objetoContrato}\", nos termos do art. 124, inciso I, alínea \"{$alinea}\", e do art. 125 da Lei nº 14.133/2021";
        }

        if ($vm->temAcrescimo()) {
            $valor = $vm->resumoFinanceiro['valor_acrescido_neste_aditivo'] ?? 0.0;
            $percentual = $vm->incidente->percentual_acrescimo ?? 0;
            $blocos['acrescimo'] = 'Fica acrescida ao valor do contrato a quantia de '.$this->valorComExtenso($valor).', correspondente a '.$this->fmt((float) $percentual).'% do valor inicial atualizado do contrato.';
        }

        if ($vm->temSupressao()) {
            $valor = $vm->resumoFinanceiro['valor_suprimido_neste_aditivo'] ?? 0.0;
            $percentual = $vm->incidente->percentual_supressao ?? 0;
            $blocos['supressao'] = 'Fica suprimida do valor do contrato a quantia de '.$this->valorComExtenso($valor).', correspondente a '.$this->fmt((float) $percentual).'% do valor inicial atualizado do contrato.';
        }

        if ($vm->envolveValor()) {
            $blocos['valor_global'] = 'O valor global do contrato passa a ser de '.$this->valorComExtenso($vm->resumoFinanceiro['valor_final_contrato'] ?? 0.0).'.';
        }

        if ($vm->exigeDotacaoOrcamentaria()) {
            $blocos['dotacao'] = $this->paragrafoDotacao($vm);
        }

        return $blocos;
    }

    /**
     * Parágrafo único de dotação orçamentária do Termo Aditivo — só aparece
     * quando há acréscimo (ou valor para o período prorrogado).
     */
    public function paragrafoDotacao(AditivoViewModel $vm): ?string
    {
        if (! $vm->exigeDotacaoOrcamentaria() || $vm->dotacoes->isEmpty()) {
            return null;
        }

        $linhas = $vm->dotacoes->map(function ($d) {
            return "Unidade Orçamentária: {$d->unidade_orcamentaria}; Classificação Funcional Programática: {$d->classificacao_funcional_programatica}; Elemento de Despesa: {$d->elemento_despesa}; Fonte de Recurso: {$d->fonte_recurso}";
        })->implode(' | ');

        return "Parágrafo único. As despesas decorrentes deste Termo Aditivo correrão à conta da seguinte dotação orçamentária: {$linhas}.";
    }

    /**
     * Parecer Jurídico / Parecer Técnico — fundamentação da prorrogação de
     * prazo, condicionada à responsabilidade pelo atraso apurada no Parecer
     * Técnico (espelhada aqui).
     */
    public function paragrafoResponsabilidadeAtrasoParecerJuridico(AditivoViewModel $vm): ?string
    {
        if (! $vm->envolvePrazo() || ! $vm->responsabilidadeAtraso) {
            return null;
        }

        return match ($vm->responsabilidadeAtraso) {
            ResponsabilidadeAtrasoEnum::NAO_IMPUTAVEL_CONTRATADA => 'Conforme o Parecer Técnico da Engenharia, o atraso decorre de fatos não imputáveis à contratada, mostrando-se a prorrogação necessária, razoável e proporcional.',
            ResponsabilidadeAtrasoEnum::IMPUTAVEL_CONTRATADA => 'Conforme o Parecer Técnico da Engenharia, o atraso é imputável à contratada, que deverá ser constituída em mora e sujeitar-se às sanções previstas no contrato, podendo a Administração optar pela extinção do contrato, nos termos do art. 111, parágrafo único, da Lei nº 14.133/2021.',
        };
    }

    /**
     * Atesto condicional da Autorização do Prefeito — só Compras e
     * Serviços-Prazo/Prazo e Valor, e só quando o contrato é contínuo.
     */
    public function atestoVantajosidadeAutorizacao(AditivoViewModel $vm): ?string
    {
        if ($vm->categoria !== 'compras_servicos' || ! $vm->envolvePrazo() || ! $vm->contratoContinuo) {
            return null;
        }

        return 'Atesto, nos termos do art. 107 da Lei nº 14.133/2021, que as condições e os preços do contrato permanecem vantajosos para a Administração e que há créditos orçamentários para a sua continuidade.';
    }

    /**
     * Item condicional "informação de disponibilidade orçamentária" na lista
     * de documentos dos autos do Parecer Jurídico — só quando há acréscimo
     * (ou valor para o período prorrogado).
     */
    public function itemDotacaoDocumentosDosAutos(AditivoViewModel $vm): ?string
    {
        return $vm->exigeDotacaoOrcamentaria() ? 'informação de disponibilidade orçamentária;' : null;
    }
}
