<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'contenido',
        'categoria',
        'usuario_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
