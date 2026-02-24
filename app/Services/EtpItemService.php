<?php

namespace App\Services;

use App\Repositories\EtpItemRepository;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EtpItemService
{
    protected $repository;

    public function __construct(EtpItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllPaged($perPage = 15)
    {
        return $this->repository->getAll($perPage);
    }

    public function getAllForSelect()
    {
         return \App\Models\EtpItem::orderBy('descricao_item', 'asc')->get();
    }

    public function store(array $data)
    {
        try {
            return $this->repository->create($data);
        } catch (Exception $e) {
            throw new Exception("Erro ao criar o item: " . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        try {
            return $this->repository->update($id, $data);
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar o item: " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            return $this->repository->delete($id);
        } catch (Exception $e) {
            throw new Exception("Erro ao excluir o item: " . $e->getMessage());
        }
    }

    public function findById($id)
    {
        return $this->repository->findById($id);
    }

    public function importarExcel($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            $importedCount = 0;

            // Remove header automatically if first row looks like header
            // Assume first column contains item description
            foreach ($rows as $index => $row) {
                if ($index === 0 && (empty($row[0]) || stripos($row[0], 'descri') !== false || stripos($row[0], 'item') !== false)) {
                    continue; // Skip header
                }

                $descricao = trim($row[0] ?? '');
                
                if (!empty($descricao)) {
                    // Check if exists to avoid duplicates (optional but good)
                    $exists = \App\Models\EtpItem::where('descricao_item', $descricao)->exists();
                    if (!$exists) {
                        $this->repository->create(['descricao_item' => $descricao]);
                        $importedCount++;
                    }
                }
            }

            return $importedCount;
        } catch (Exception $e) {
            throw new Exception("Erro ao processar a planilha: " . $e->getMessage());
        }
    }
}
