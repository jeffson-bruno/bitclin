<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retorno extends Model
{
    protected $fillable = [
        'paciente_id','medico_id','data_retorno','motivo','observacoes','status'
    ];

    protected $casts = [
        'data_retorno' => 'datetime',
    ];

    public function paciente() { return $this->belongsTo(Paciente::class); }
    public function medico()   { return $this->belongsTo(User::class, 'medico_id'); }
}
