<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $fillable = [
    'nome',
    'cpf',
    'telefone',
    'data_nascimento',
    'endereco',
    'observacoes',
    'procedimento',
    'preco',
    'pago',
    'forma_pagamento',
    'data_pagamento',
];

}
