<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Solicitacao extends Model
{
    protected $table = 'solicitacoes';

    protected $fillable = [
        'user_id',
        'prefeitura_id',
        'tipo',
        'assunto',
        'status',
    ];

    /**
     * Usuário que criou a solicitação
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Prefeitura vinculada
     */
    public function prefeitura(): BelongsTo
    {
        return $this->belongsTo(Prefeitura::class);
    }

    /**
     * Histórico de mensagens da solicitação
     */
    public function mensagens(): HasMany
    {
        return $this->hasMany(SolicitacaoMensagem::class, 'solicitacao_id')->orderBy('created_at', 'asc');
    }

    /**
     * Helpers para verificar status
     */
    public function estaAberta(): bool
    {
        return $this->status === 'aberta';
    }

    public function estaFinalizada(): bool
    {
        return $this->status === 'finalizada';
    }
}
