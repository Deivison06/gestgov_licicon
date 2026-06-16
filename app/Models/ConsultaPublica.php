<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log de consultas na página pública /autenticar/{codigo}.
 * Append-only.
 */
class ConsultaPublica extends Model
{
    use HasFactory;

    protected $table = 'consultas_publicas';

    // Não há created_at/updated_at — usa consultado_em.
    public $timestamps = false;

    protected $fillable = [
        'codigo_verificador',
        'documento_versao_id',
        'ip',
        'user_agent',
        'sucesso',
        'consultado_em',
    ];

    protected $casts = [
        'sucesso'       => 'boolean',
        'consultado_em' => 'datetime',
    ];

    public function versao(): BelongsTo
    {
        return $this->belongsTo(DocumentoVersao::class, 'documento_versao_id');
    }
}
