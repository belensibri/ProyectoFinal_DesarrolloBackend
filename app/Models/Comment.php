<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    public const ROL_TECNICO = 'tecnico';
    public const ROL_USUARIO = 'usuario';

    protected $fillable = [
        'ticket_id',
        'usuario_id',
        'rol',
        'contenido',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'comentario_id');
    }
}
