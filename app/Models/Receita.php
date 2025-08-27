<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receita extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'crm',
        'conteudo',
        'arquivo',
        'data_receita',
    ];

    protected $casts = [
        'data_receita' => 'datetime',
        'conteudo'     => 'array', 
    ];

    // Relacionamentos
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    public function prontuario()
    {
        return $this->belongsTo(Prontuario::class);
    }

}
