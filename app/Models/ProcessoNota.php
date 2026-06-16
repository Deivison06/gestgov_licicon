<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessoNota extends Model
{
    protected $fillable = [
        'processo_id',
        'user_id',
        'status_em_vigor',
        'texto',
        'anexo_path',
        'anexo_nome',
    ];

    public function hasAnexo(): bool
    {
        return ! empty($this->anexo_path);
    }

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
