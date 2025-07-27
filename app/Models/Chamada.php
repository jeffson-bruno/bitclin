<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chamada extends Model
{
    use HasFactory;

    protected $fillable = [
        'senha_id',
        'medico_id',
        'tentativa',
    ];

    public function senha()
    {
        return $this->belongsTo(\App\Models\SenhaAtendimento::class, 'senha_id');
    }

}

