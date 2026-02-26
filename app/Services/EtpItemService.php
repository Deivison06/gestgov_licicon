<?php

namespace App\Services;

use App\Repositories\EtpItemRepository;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\EtpItem;

class EtpItemService
{
    protected $repository;

    public function __construct(EtpItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllPaged($perPage = 15, $descricao = null)
    {
        return $this->repository->getAll($perPage, $descricao);
    }


    public function getAllForSelect()
    {
         return EtpItem::orderBy('descricao_item', 'asc')->get();
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

    /**
     * Importa itens de uma planilha Excel para o ETP
     * 
     * @param UploadedFile $file
     * @return array Itens importados
     * @throws Exception
     */
    public function importarItensDaPlanilha($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            $itensImportados = [];
            $linhaAtual = 1;

            // Validar cabeçalho
            if (count($rows) < 2) {
                throw new Exception('Arquivo vazio ou sem dados.');
            }

            $cabecalho = array_map('strtolower', array_map('trim', $rows[0] ?? []));
            $cabecalhoEsperado = ['descricao', 'unidade', 'quantidade'];
            
            // Verificar se o cabeçalho está correto (ignorando case e espaços)
            foreach ($cabecalhoEsperado as $i => $campo) {
                if (!isset($cabecalho[$i]) || strpos($cabecalho[$i], $campo) === false) {
                    throw new Exception("Cabeçalho inválido. Use: Descricao | Unidade | Quantidade");
                }
            }

            // Processar linhas
            foreach ($rows as $index => $row) {
                $linhaAtual = $index + 1;
                
                // Pular cabeçalho
                if ($index === 0) continue;

                $descricao = trim($row[0] ?? '');
                $unidade = trim($row[1] ?? '');
                $quantidade = trim($row[2] ?? '');

                // Pular linha vazia
                if (empty($descricao) && empty($unidade) && empty($quantidade)) {
                    continue;
                }

                // Validações
                if (empty($descricao)) {
                    throw new Exception("Linha {$linhaAtual}: Descrição é obrigatória.");
                }

                if (empty($unidade)) {
                    throw new Exception("Linha {$linhaAtual}: Unidade é obrigatória.");
                }

                if (!is_numeric($quantidade) || $quantidade <= 0) {
                    throw new Exception("Linha {$linhaAtual}: Quantidade deve ser um número maior que zero.");
                }

                // Verificar se o item já existe
                $item = EtpItem::where('descricao_item', $descricao)->first();
                
                if (!$item) {
                    // Criar novo item
                    $item = $this->repository->create(['descricao_item' => $descricao]);
                }

                // Adicionar à lista de itens importados
                $itensImportados[] = [
                    'item_id' => $item->id,
                    'descricao' => $item->descricao_item,
                    'unidade' => $unidade,
                    'quantidade' => (int) $quantidade
                ];
            }

            if (empty($itensImportados)) {
                throw new Exception('Nenhum item válido encontrado na planilha.');
            }

            return $itensImportados;

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function importarExcel($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            $importedCount = 0;

            foreach ($rows as $index => $row) {
                if ($index === 0 && (empty($row[0]) || stripos($row[0], 'descri') !== false)) {
                    continue;
                }

                $descricao = trim($row[0] ?? '');
                
                if (!empty($descricao)) {
                    $exists = EtpItem::where('descricao_item', $descricao)->exists();
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