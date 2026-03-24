<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'tecnico_id',
        'department_id',
        'titulo',
        'descripcion',
        'estado',
        'prioridad',
        'fecha_creacion',
        'fecha_cierre'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function ticket_histories()
    {
        return $this->hasMany(TicketHistory::class);
    }
}
