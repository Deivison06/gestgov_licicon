<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'prefeitura_id',
        'unidade_id'
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
        ];
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