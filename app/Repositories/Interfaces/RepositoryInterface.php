<?php

namespace App\Repositories\Interfaces;

/**
 * Contrato genérico de repositório. Declara apenas as operações que TODOS os
 * repositórios compartilham de forma idêntica — sem tipos rígidos de parâmetro/
 * retorno, para não conflitar com as assinaturas específicas já existentes
 * (ex.: findById/update/delete variam entre os repos e permanecem por repo).
 */
interface RepositoryInterface
{
    public function find($id);

    public function findOrFail($id);

    public function all();

    public function paginate(int $perPage = 15);

    public function create(array $data);

    public function getModel();
}
