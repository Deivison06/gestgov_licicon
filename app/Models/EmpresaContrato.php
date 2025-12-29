<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaContrato extends Model
{
    use HasFactory;

    protected $fillable = [
        'razao_social',
        'cnpj',
        'representante',
        'endereco',
        'prefeitura_id',
    ];

    protected $table = 'empresa_contratos';

    public function prefeitura()
    {
        return $this->belongsTo(Prefeitura::class);
    }

    public function contratos()
    {
        return $this->hasMany(ContratoManual::class, 'empresa_id');
    }

    public function getCnpjFormatadoAttribute(): string
    {
        $cnpj = $this->cnpj;
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }
}