<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    protected $fillable = ['nome', 'valor', 'data_pagamento', 'pago'];
}
