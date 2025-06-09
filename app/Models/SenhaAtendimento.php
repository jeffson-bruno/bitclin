<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenhaAtendimento extends Model
{
    use HasFactory;

    protected $table = 'senhas_atendimento';

    protected $fillable = [
        'paciente_id',
        'tipo',
        'codigo',
        'data_emissao',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
