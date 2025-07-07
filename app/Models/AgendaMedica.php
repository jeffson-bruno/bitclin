<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaMedica extends Model
{
    use HasFactory;

    protected $fillable = ['medico_id', 'data', 'hora_inicio', 'hora_fim'];

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }
}
