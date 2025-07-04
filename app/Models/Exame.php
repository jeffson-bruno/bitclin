<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use HasFactory;

class Exame extends Model
{
    

    protected $fillable = ['nome', 'valor'];
}
