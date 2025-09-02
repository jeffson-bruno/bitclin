<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encaminhamento extends Model
{
    protected $fillable = [
        'paciente_id','de_user_id','para_medico_id','para_especialidade_id','observacoes'
    ];

    public function paciente(){ return $this->belongsTo(Paciente::class); }
    public function de(){ return $this->belongsTo(User::class, 'de_user_id'); }
    public function paraMedico(){ return $this->belongsTo(User::class, 'para_medico_id'); }
    public function especialidade(){ return $this->belongsTo(Especialidade::class, 'para_especialidade_id'); }
}
