<?php

namespace App\Services;

use App\Models\Homologacao;
use App\Models\Lote;
use App\Models\Processo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HomologacaoService
{
    /**
     * Tipos de documento gerados por homologação (na ordem de exibição).
     * Demais tipos (atos_sessao, proposta, etc.) seguem únicos do processo.
     */
    public const TIPOS_POR_HOMOLOGACAO = [
        'recurso_contratacoes_decisao_recursos',
        'termo_adjudicacao',
        'parecer_controle_interno',
        'termo_homologacao',
        'ata_registro_precos',
        'publicacoes',
    ];

    /**
     * Campos copiados da homologação anterior ao criar uma nova (herança).
     */
    private const CAMPOS_HERDADOS = [
        'orgao_responsavel',
        'cargo_responsavel',
        'cnpj',
        'endereco',
        'responsavel',
        'cpf_responsavel',
        'razao_social',
        'cnpj_empresa_vencedora',
        'endereco_empresa_vencedora',
        'representante_legal_empresa',
        'cpf_representante',
        'cargo_controle_interno',
        'merenda_escolar',
        'veiculos',
    ];

    public function tiposPorHomologacao(): array
    {
        return self::TIPOS_POR_HOMOLOGACAO;
    }

    public function ehTipoPorHomologacao(string $tipo): bool
    {
        return \in_array($tipo, self::TIPOS_POR_HOMOLOGACAO, true);
    }

    public function lotesPendentes(Processo $processo): Collection
    {
        return $processo->lotesPendentesHomologacao()
            ->with('vencedor')
            ->orderBy('vencedor_id')
            ->orderBy('lote')
            ->orderBy('ordem')
            ->get();
    }

    public function temLotesPendentes(Processo $processo): bool
    {
        return $processo->temLotesPendentesHomologacao();
    }

    public function temHomologacao(Processo $processo): bool
    {
        return $processo->homologacoes()->exists();
    }

    public function temHomologacaoEmEdicao(Processo $processo): bool
    {
        return $processo->homologacoes()
            ->where('status', Homologacao::STATUS_EM_EDICAO)
            ->exists();
    }

    /**
     * Regra de liberação do botão "Gerar Nova Homologação":
     *  - Permite quando ainda não existe nenhuma homologação OU
     *  - Quando há pendência (lotes não vinculados OU homologação em edição).
     *  - Bloqueia somente quando já existe homologação E está tudo concluído.
     */
    public function podeCriarNovaHomologacao(Processo $processo): bool
    {
        if (!$this->temHomologacao($processo)) {
            return true;
        }

        return $this->temLotesPendentes($processo)
            || $this->temHomologacaoEmEdicao($processo);
    }

    /**
     * Cria nova homologação vinculando todos os lotes pendentes do processo.
     *
     * Regras:
     *  - Falha se não houver lotes pendentes.
     *  - Lotes já vinculados a outra homologação não são afetados (regra (d)).
     *  - Campos herdam da última homologação; quando for a primeira, herdam
     *    da Finalizacao do processo para não perder dados já preenchidos.
     */
    public function criarNovaHomologacao(Processo $processo): Homologacao
    {
        return DB::transaction(function () use ($processo) {
            $lotesPendentesIds = $processo->lotesPendentesHomologacao()->pluck('id');

            if ($lotesPendentesIds->isEmpty()) {
                throw new \DomainException('Não há lotes pendentes de homologação para este processo.');
            }

            $proximoSequencial = ((int) $processo->homologacoes()->max('numero_sequencial')) + 1;

            $ultimaHomologacao = $processo->homologacoes()
                ->orderByDesc('numero_sequencial')
                ->first();

            $atributos = [
                'processo_id' => $processo->id,
                'numero_sequencial' => $proximoSequencial,
                'status' => Homologacao::STATUS_EM_EDICAO,
            ];

            $fonteHeranca = $ultimaHomologacao ?: $processo->finalizacao;
            if ($fonteHeranca) {
                foreach (self::CAMPOS_HERDADOS as $campo) {
                    $valor = $fonteHeranca->{$campo} ?? null;
                    if ($valor !== null && $valor !== '') {
                        $atributos[$campo] = $valor;
                    }
                }
            }

            $homologacao = Homologacao::create($atributos);

            Lote::whereIn('id', $lotesPendentesIds)
                ->update(['homologacao_id' => $homologacao->id]);

            return $homologacao->refresh();
        });
    }
}
