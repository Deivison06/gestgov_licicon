<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoSelecaoAssinantes extends Model
{
    use HasFactory;

    protected $table = 'documento_selecao_assinantes';

    public const MODO_PARALELO   = 'paralelo';
    public const MODO_SEQUENCIAL = 'sequencial';

    protected $fillable = [
        'processo_id',
        'tipo_documento',
        'homologacao_id',
        'vencedor_id',
        'modo',
        'prazo_dias',
        'assinantes',
        'atualizado_por_user_id',
    ];

    protected $casts = [
        'assinantes' => 'array',
        'prazo_dias' => 'integer',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function homologacao(): BelongsTo
    {
        return $this->belongsTo(Homologacao::class);
    }

    public function vencedor(): BelongsTo
    {
        return $this->belongsTo(Vencedor::class);
    }

    public function atualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atualizado_por_user_id');
    }
}
