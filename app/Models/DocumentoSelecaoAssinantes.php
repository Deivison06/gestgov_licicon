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
        'incidente_id',
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

    /**
     * Grava o cargo de cada assinante junto da seleção (cópia, como já é feito
     * com unidade_nome), para o documento não mudar se a unidade for editada
     * depois. Cargo digitado na tela (Atas) é mantido; se veio vazio, usa o
     * cargo da unidade. Unidade sem cargo => nada é gravado e os documentos
     * seguem mostrando unidade_nome, como antes.
     */
    protected static function booted(): void
    {
        static::saving(function (self $selecao) {
            if (is_array($selecao->assinantes)) {
                $selecao->assinantes = self::comCargoDasUnidades($selecao->assinantes);
            }
        });
    }

    public static function comCargoDasUnidades(array $assinantes): array
    {
        $ids = collect($assinantes)
            ->filter(fn ($a) => is_array($a) && blank($a['cargo'] ?? null) && ! empty($a['unidade_id']))
            ->pluck('unidade_id')->map(fn ($id) => (int) $id)->unique()->values();

        if ($ids->isEmpty()) {
            return $assinantes;
        }

        $cargos = Unidade::whereIn('id', $ids)->whereNotNull('cargo')->where('cargo', '!=', '')->pluck('cargo', 'id');

        return array_map(function ($a) use ($cargos) {
            if (is_array($a) && blank($a['cargo'] ?? null) && isset($cargos[(int) ($a['unidade_id'] ?? 0)])) {
                $a['cargo'] = $cargos[(int) $a['unidade_id']];
            }

            return $a;
        }, $assinantes);
    }

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
