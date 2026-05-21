<?php

namespace App\Services;

use App\Repositories\EtpRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EtpService
{
    protected $repository;

    public function __construct(EtpRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getByPrefeituraId($prefeituraId, $filters = [], $perPage = 15)
    {
        return $this->repository->getByPrefeituraId($prefeituraId, $filters, $perPage);
    }

    public function getAllWithFilters($filters = [], $perPage = 15)
    {
        return $this->repository->getAllWithFilters($filters, $perPage);
    }

    public function store(array $data, ?UploadedFile $cotacaoFile = null)
    {
        try {
            // Cria o diretório se não existir
            if ($cotacaoFile) {
                $destino = public_path('uploads/etps/cotacoes');
                if (!file_exists($destino)) {
                    mkdir($destino, 0755, true);
                }

                $nomeArquivo = time() . '_' . $cotacaoFile->getClientOriginalName();
                $cotacaoFile->move($destino, $nomeArquivo);
                $data['cotacao_path'] = 'uploads/etps/cotacoes/' . $nomeArquivo;
            }

            $itens = $data['itens'] ?? [];
            $lotes = $data['lotes'] ?? [];
            
            unset($data['itens'], $data['lotes']);

            $etp = $this->repository->create($data);

            // Sync itens para tipos que não usam lote
            if (in_array($data['tipo_contratacao'] ?? null, ['item', 'servicos', 'compras', 'obras']) && !empty($itens)) {
                $itensFormatados = [];
                foreach ($itens as $itemId => $itemData) {
                    $itensFormatados[$itemId] = [
                        'unidade' => $itemData['unidade'] ?? 'UN',
                        'quantidade' => $itemData['quantidade'] ?? 0,
                    ];
                }
                $this->repository->syncItens($etp, $itensFormatados);
            }

            // Processa lotes
            if (($data['tipo_contratacao'] ?? null) === 'lote' && !empty($lotes)) {
                foreach ($lotes as $loteData) {
                    $itensLote = $loteData['itens'] ?? [];
                    unset($loteData['itens']);

                    $lote = $this->repository->createLote($etp, $loteData);

                    $itensFormatados = [];
                    foreach ($itensLote as $itemData) {
                        if (isset($itemData['item_id'])) {
                            $itensFormatados[$itemData['item_id']] = [
                                'unidade' => $itemData['unidade'] ?? 'UN',
                                'quantidade' => $itemData['quantidade'] ?? 0,
                            ];
                        }
                    }

                    if (!empty($itensFormatados)) {
                        $this->repository->syncItensLote($lote, $itensFormatados);
                    }
                }
            }

            return $etp;

        } catch (Exception $e) {
            throw new Exception("Erro ao criar ETP: " . $e->getMessage());
        }
    }

    /**
     * Update an existing ETP
     */
    public function update($id, array $data, ?UploadedFile $cotacaoFile = null)
    {
        try {
            $etp = $this->findById($id);

            if ($cotacaoFile) {

                // Remove arquivo antigo
                if ($etp->cotacao_path) {
                    $caminhoAntigo = public_path($etp->cotacao_path);

                    if (file_exists($caminhoAntigo)) {
                        unlink($caminhoAntigo);
                    }
                }

                // Gera nome único
                $nomeArquivo = Str::uuid() . '.' . $cotacaoFile->getClientOriginalExtension();

                $destino = public_path('uploads/etps/cotacoes');

                if (!file_exists($destino)) {
                    mkdir($destino, 0755, true);
                }

                // Move arquivo
                $cotacaoFile->move($destino, $nomeArquivo);

                // Salva caminho relativo no banco
                $data['cotacao_path'] = 'uploads/etps/cotacoes/' . $nomeArquivo;
            }

            $itens = $data['itens'] ?? [];
            $lotes = $data['lotes'] ?? [];
            
            unset($data['itens'], $data['lotes']);

            // Update ETP
            $etp = $this->repository->update($id, $data);

            // Limpa relacionamentos existentes garantindo que nada órfão fique para trás
            $etp->itens()->detach();
            foreach ($etp->lotes as $loteOld) {
                $loteOld->itens()->detach();
                $loteOld->delete();
            }

            // Sync itens para tipos que não usam lote
            if (in_array($data['tipo_contratacao'] ?? null, ['item', 'servicos', 'compras', 'obras']) && !empty($itens)) {
                $itensFormatados = [];
                foreach ($itens as $itemId => $itemData) {
                    $itensFormatados[$itemId] = [
                        'unidade' => $itemData['unidade'] ?? 'UN',
                        'quantidade' => $itemData['quantidade'] ?? 0,
                    ];
                }
                $this->repository->syncItens($etp, $itensFormatados);
            }

            // Processa lotes
            if (($data['tipo_contratacao'] ?? null) === 'lote' && !empty($lotes)) {
                foreach ($lotes as $loteData) {
                    $itensLote = $loteData['itens'] ?? [];
                    unset($loteData['itens']);

                    $lote = $this->repository->createLote($etp, $loteData);

                    $itensFormatados = [];
                    foreach ($itensLote as $itemData) {
                        if (isset($itemData['item_id'])) {
                            $itensFormatados[$itemData['item_id']] = [
                                'unidade' => $itemData['unidade'] ?? 'UN',
                                'quantidade' => $itemData['quantidade'] ?? 0,
                            ];
                        }
                    }

                    if (!empty($itensFormatados)) {
                        $this->repository->syncItensLote($lote, $itensFormatados);
                    }
                }
            }

            return $etp;

        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar ETP: " . $e->getMessage());
        }
    }

    /**
     * Delete an ETP
     */
    public function delete($id)
    {
        try {
            $etp = $this->findById($id);

            // Delete associated file
            if ($etp->cotacao_path) {
                Storage::disk('public')->delete($etp->cotacao_path);
            }

            // Delete relationships
            $etp->itens()->detach();
            $etp->lotes()->delete();

            // Delete ETP
            return $etp->delete();

        } catch (Exception $e) {
            throw new Exception("Erro ao excluir ETP: " . $e->getMessage());
        }
    }

    public function findById($id)
    {
        return $this->repository->findById($id);
    }

    public function updateStatus($id, $status, $motivo_recusa = null)
    {
        $validStatuses = ['pendente', 'em_analise', 'aprovado', 'recusado', 'em_processo'];
        
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Status inválido.");
        }

        $updateData = ['status' => $status];
        if ($status === 'recusado' && $motivo_recusa) {
            $updateData['motivo_recusa'] = $motivo_recusa;
        }

        $etp = $this->repository->update($id, $updateData);

        return $etp;
    }

    public function vincularProcesso($etpId, $processoId)
    {
        $etp = $this->findById($etpId);

        if ($etp->status !== 'aprovado' && $etp->status !== 'em_processo') {
            throw new Exception("Somente ETPs aprovados podem ser vinculados a um processo.");
        }

        return $this->repository->update($etpId, [
            'processo_id' => $processoId,
            'status' => 'em_processo'
        ]);
    }
}