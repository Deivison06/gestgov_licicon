<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PrefeituraScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();

        // Qualquer conta vinculada a uma prefeitura (prefeitura_id preenchido) deve ficar
        // restrita a ela, não importa o papel — só a equipe Licicon (sem prefeitura_id)
        // enxerga múltiplas prefeituras. Usar hasRole('prefeitura') aqui era o bug: contas
        // de assinante/secretaria (sem esse papel, mas com prefeitura_id) ficavam sem
        // nenhum isolamento entre prefeituras.
        if ($user && $user->prefeitura_id) {
            $builder->where('prefeitura_id', $user->prefeitura_id);
        }
    }
}