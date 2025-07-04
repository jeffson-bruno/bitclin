<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidade extends Model
{
    protected $fillable = ['nome'];

    public function medicos()
    {
        return $this->hasMany(User::class);
    }
}
