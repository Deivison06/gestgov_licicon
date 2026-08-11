<?php

namespace App\Services;

use App\Models\AtaRegistroPreco;
use App\Models\Homologacao;
use App\Models\HomologacaoDesistencia;
use App\Models\Lote;
use App\Models\Vencedor;
use App\Support\FileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Registra a desistência/abandono da assinatura da Ata de Registro de Preços por
 * uma empresa vencedora dentro de uma Homologação.
 *
 * A empresa não é apagada de lugar nenhum: o registro fica no processo com as
 * provas de convocação anexadas, a Ata (se já gerada) é apenas marcada como
 * invalidada e o saldo dos lotes daquele vencedor é zerado para liberar o
 * caminho para a homologação parcial seguinte (próxima empresa classificada).
 */
class HomologacaoDesistenciaService
{
    public function __construct(
        protected FinalizacaoPdfService $pdfService,
    ) {}

    /**
     * @param  array{data_solicitacao_assinatura: string, observacao?: string|null}  $dados
     * @param  UploadedFile[]  $arquivos
     */
    public function registrar(Homologacao $homologacao, Vencedor $vencedor, array $dados, array $arquivos): HomologacaoDesistencia
    {
        $lotes = Lote::where('homologacao_id', $homologacao->id)
            ->where('vencedor_id', $vencedor->id)
            ->get();

        if ($lotes->isEmpty()) {
            throw new \DomainException('Este vencedor não possui lotes nesta homologação.');
        }

        if (HomologacaoDesistencia::where('homologacao_id', $homologacao->id)->where('vencedor_id', $vencedor->id)->exists()) {
            throw new \DomainException('Já existe uma desistência registrada para este vencedor nesta homologação.');
        }

        return DB::transaction(function () use ($homologacao, $vencedor, $dados, $arquivos, $lotes) {
            $snapshot = $lotes->map(fn (Lote $lote) => [
                'lote_id' => $lote->id,
                'lote' => $lote->lote,
                'item' => $lote->item,
                'descricao' => $lote->descricao,
                'quantidade' => (float) $lote->quantidade,
                'vl_unit' => (float) $lote->vl_unit,
                'vl_total' => (float) $lote->vl_total,
            ])->all();

            $desistencia = HomologacaoDesistencia::create([
                'homologacao_id' => $homologacao->id,
                'vencedor_id' => $vencedor->id,
                'user_id' => auth()->id(),
                'data_solicitacao_assinatura' => $dados['data_solicitacao_assinatura'],
                'observacao' => $dados['observacao'] ?? null,
                'quantidade_lotes_snapshot' => $snapshot,
            ]);

            foreach ($arquivos as $arquivo) {
                $caminho = FileStorage::salvar(
                    $arquivo,
                    "uploads/homologacoes/desistencias/{$desistencia->id}",
                    'comprovacao'
                );

                $desistencia->anexos()->create([
                    'caminho' => $caminho,
                    'nome_original' => $arquivo->getClientOriginalName(),
                ]);
            }

            // Zera o saldo DISPONÍVEL dos lotes sem invalidar quantidade já
            // efetivamente consumida em contratos gerados a partir deles (mesma
            // proteção usada em ContratacaoController::destroy).
            foreach ($lotes as $lote) {
                $lote->quantidade = max((float) $lote->quantidade_contratada, 0);
                $lote->save();
            }

            // A Ata já gerada (se houver) permanece intacta — só é sinalizada
            // como invalidada, nunca apagada.
            AtaRegistroPreco::where('homologacao_id', $homologacao->id)
                ->where('vencedor_id', $vencedor->id)
                ->update(['invalidada_em' => now()]);

            $caminhoPdf = $this->pdfService->gerarTermoDesistencia($homologacao->processo, $homologacao, $vencedor, $desistencia);

            $desistencia->update([
                'caminho_pdf' => $caminhoPdf,
                'gerado_em' => now(),
            ]);

            return $desistencia->refresh();
        });
    }
}
