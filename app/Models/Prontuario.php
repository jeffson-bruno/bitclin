<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prontuario extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'queixa_principal',
        'historia_doenca',
        'historico_progressivo',
        'historico_familiar',
        'habitos_vida',
        'revisao_sistemas',
        'receitas',
        'atestados',
        'exames',
    ];

    protected $casts = [
        'receitas' => 'array',
        'atestados' => 'array',
        'exames' => 'array',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }
}
