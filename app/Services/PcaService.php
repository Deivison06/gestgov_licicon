<?php

namespace App\Services;

use App\Repositories\PcaRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PcaService
{
    protected $repository;

    public function __construct(PcaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getByPrefeituraId(int $prefeituraId, array $filters = [])
    {
        return $this->repository->getByPrefeituraId($prefeituraId, $filters);
    }
    
    public function getAll(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $equipeElaboracao = $data['equipe_elaboracao'] ?? [];
            if (is_string($equipeElaboracao)) {
                $equipeElaboracao = json_decode($equipeElaboracao, true);
            }

            $pcaData = [
                'prefeitura_id' => $data['prefeitura_id'],
                'numero_pca' => $data['numero_pca'] ?? null,
                'exercicio' => $data['exercicio'],
                'equipe_elaboracao' => $equipeElaboracao,
                'periodo_elaboracao_inicio' => $data['periodo_elaboracao_inicio'] ?? null,
                'periodo_elaboracao_fim' => $data['periodo_elaboracao_fim'] ?? null,
                'status' => $data['status'] ?? 'pendente',
            ];

            $pca = $this->repository->create($pcaData);

            if (!empty($data['itens']) && is_array($data['itens'])) {
                $this->repository->syncItens($pca, $data['itens']);
            }

            DB::commit();
            return $pca;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar PCA: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $pca = $this->repository->findById($id);
            if (!$pca) {
                throw new \Exception('PCA não encontrado.');
            }

            $equipeElaboracao = $data['equipe_elaboracao'] ?? [];
            if (is_string($equipeElaboracao)) {
                $equipeElaboracao = json_decode($equipeElaboracao, true);
            }

            $pcaData = [
                'numero_pca' => $data['numero_pca'] ?? $pca->numero_pca,
                'exercicio' => $data['exercicio'],
                'equipe_elaboracao' => $equipeElaboracao,
                'periodo_elaboracao_inicio' => $data['periodo_elaboracao_inicio'] ?? null,
                'periodo_elaboracao_fim' => $data['periodo_elaboracao_fim'] ?? null,
                'status' => $data['status'] ?? $pca->status,
            ];
            
            if (isset($data['prefeitura_id'])) {
                $pcaData['prefeitura_id'] = $data['prefeitura_id'];
            }

            $this->repository->update($id, $pcaData);

            $itens = $data['itens'] ?? [];
            $this->repository->syncItens($pca, $itens);

            DB::commit();
            return $this->repository->findById($id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar PCA: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function delete(int $id)
    {
        DB::beginTransaction();
        try {
            $pca = $this->repository->findById($id);
            if (!$pca) {
                throw new \Exception('PCA não encontrado.');
            }
            
            if (!in_array($pca->status, ['pendente', 'em_analise'])) {
                throw new \Exception('Apenas PCAs pendentes ou em análise podem ser excluídos.');
            }

            $pca->delete();
            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir PCA: ' . $e->getMessage());
            throw $e;
        }
    }
}
