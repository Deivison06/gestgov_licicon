<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoMensagem extends Model
{
    protected $table = 'solicitacao_mensagens';

    protected $fillable = [
        'solicitacao_id',
        'user_id',
        'mensagem',
        'anexo_path',
        'lida_em',
    ];

    protected $casts = [
        'lida_em' => 'datetime',
    ];

    /**
     * Solicitação pai
     */
    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(Solicitacao::class);
    }

    /**
     * Usuário que enviou a mensagem
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
