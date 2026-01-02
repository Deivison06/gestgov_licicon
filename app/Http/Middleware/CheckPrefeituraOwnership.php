<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPrefeituraOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Se não for usuário de prefeitura, permite acesso
        if (!$user->hasRole('prefeitura')) {
            return $next($request);
        }
        
        // Verifica se o usuário tem uma prefeitura associada
        if (!$user->prefeitura_id) {
            abort(403, 'Usuário não possui prefeitura associada.');
        }
        
        // Para rotas que envolvem prefeituras (ex: /prefeituras/{id})
        if ($request->route('prefeitura')) {
            $prefeituraId = $request->route('prefeitura');
            if ($prefeituraId != $user->prefeitura_id) {
                abort(403, 'Acesso negado a esta prefeitura.');
            }
        }
        
        // Para rotas que envolvem processos
        if ($request->route('processo')) {
            $processo = $request->route('processo');
            if ($processo->prefeitura_id != $user->prefeitura_id) {
                abort(403, 'Acesso negado a este processo.');
            }
        }
        
        return $next($request);
    }
}