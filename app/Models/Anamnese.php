<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anamnese extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'queixa_principal',
        'historia_doenca',
        'historico_medico',
        'historico_familiar',
        'habitos_vida',
        'revisao_sistemas',
        'observacoes',
        'conteudo',
        'origem',
        'pressao_arterial',
        'user_id',
    ];

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

    public function autor() { 
        return $this->belongsTo(User::class, 'user_id'); 
    }

    public function paciente() { 
        return $this->belongsTo(Paciente::class, 'paciente_id'); 
    }

}

