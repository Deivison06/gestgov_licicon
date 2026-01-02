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
        
        if ($user && $user->hasRole('prefeitura') && $user->prefeitura_id) {
            $builder->where('prefeitura_id', $user->prefeitura_id);
        }
    }
}