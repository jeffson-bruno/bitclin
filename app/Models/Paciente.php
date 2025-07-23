<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $fillable = [
    'nome',
    'cpf',
    'telefone',
    'estado_civil',
    'data_nascimento',
    'endereco',
    'observacoes',
    'procedimento',
    'preco',
    'pago',
    'forma_pagamento',
    'data_pagamento',
    'medico_id',
    'exame_id',
    'turno_exame',
    'dia_semana_exame',
];
public function medico()
{
    return $this->belongsTo(User::class, 'medico_id');
}

public function exame()
{
    return $this->belongsTo(Exame::class, 'exame_id');
}

public function especialidade()
{
    return $this->belongsTo(Especialidade::class, 'especialidade_id');
}

// Scopes personalizados
public function scopeConsultasHoje($query)
{
    return $query->where('procedimento', 'consulta')
                 ->whereDate('data_consulta', now()->toDateString());
}

public function scopeExamesSemana($query)
{
    return $query->where('procedimento', 'exame')
                 ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
}



}
