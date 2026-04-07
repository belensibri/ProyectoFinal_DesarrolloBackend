<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descripcion_problema',
        'resolucion',
        'causa_raiz',
        'tipo_resolucion',
        'es_reutilizable',
        'categoria',
        'usuario_id'
        ,
        'ticket_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
