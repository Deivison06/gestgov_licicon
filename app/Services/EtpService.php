<?php

namespace App\Services;

use App\Repositories\EtpRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;

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
             if ($cotacaoFile) {
                 $data['cotacao_path'] = $cotacaoFile->store('etps/cotacoes', 'public');
             }

             $etp = $this->repository->create($data);

             if (isset($data['itens_ids']) && is_array($data['itens_ids']) && $data['tipo_contratacao'] === 'lote') {
                 $this->repository->syncItens($etp, $data['itens_ids']);
             }

             return $etp;
         } catch (Exception $e) {
             throw new Exception("Erro ao criar o ETP: " . $e->getMessage());
         }
    }

    public function findById($id)
    {
         return $this->repository->findById($id);
    }

    public function updateStatus($id, $status)
    {
        $validStatuses = ['pendente', 'em_analise', 'aprovado', 'recusado', 'em_processo'];
        
        if (!in_array($status, $validStatuses)) {
             throw new Exception("Status inválido.");
        }

        $etp = $this->repository->update($id, ['status' => $status]);

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
