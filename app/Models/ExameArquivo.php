<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ExameArquivo extends Model
{
    protected $fillable = [
        'paciente_id','uploaded_by','original_name','mime_type','size_bytes','path',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        // usa o disco public com URL relativa (/storage/...)
        return Storage::disk('public')->url($this->path);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
