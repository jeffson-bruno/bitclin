<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles;
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'usuario', // novo campo
        'password',
        'role',
        'especialidade_id',
        'registro_tipo',
        'registro_numero',
        'registro_uf',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define o campo usado como login no sistema.
     *
     * @return string
     */
    public function username(): string
    {
        return 'usuario';
    }

    public function especialidade()
    {
        return $this->belongsTo(Especialidade::class);
    }

    // Helpers para o registro profissional (opcionais)
    public function setRegistroUfAttribute($value): void
    {
        $this->attributes['registro_uf'] = $value ? strtoupper($value) : null;
    }

    public function getRegistroProfissionalAttribute(): ?string
    {
        if (!$this->registro_tipo || !$this->registro_numero) return null;
        $uf = $this->registro_uf ? '-' . strtoupper($this->registro_uf) : '';
        return "{$this->registro_tipo}{$uf} {$this->registro_numero}";
    }

}
