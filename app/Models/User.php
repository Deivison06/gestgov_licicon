<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPrefeitura;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, BelongsToPrefeitura;
    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'prefeitura_id',
        'unidade_id',
        'numero_portaria',
        'data_portaria',
        'is_assinante',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'data_portaria' => 'date',
            'is_assinante' => 'boolean',
        ];
    }

    public function scopeAssinantes($query)
    {
        return $query->where('is_assinante', true);
    }

    // Relacionamento com Prefeitura
    public function prefeitura()
    {
        return $this->belongsTo(Prefeitura::class);
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'unidade_id');
    }
}