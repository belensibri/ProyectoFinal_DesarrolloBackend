<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_EN_PROCESO = 'en_proceso';
    public const ESTADO_CERRADO = 'cerrado';

    protected $fillable = [
        'usuario_id',
        'tecnico_id',
        'titulo',
        'descripcion',
        'categoria',
        'estado',
        'prioridad',
        'fecha_creacion',
        'fecha_cierre',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
            'fecha_cierre' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function faqArticle()
    {
        return $this->hasOne(FaqArticle::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function ticketHistories()
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isAdministrador()) {
            return $query;
        }

        if ($user->isTecnico()) {
            return $query->where(function ($builder) use ($user) {
                $builder
                    ->where('tecnico_id', $user->id)
                    ->orWhere(function ($nested) {
                        $nested
                            ->whereNull('tecnico_id')
                            ->where('estado', self::ESTADO_ACTIVO);
                    });
            });
        }

        return $query->where('usuario_id', $user->id);
    }

    public function isClosed(): bool
    {
        return $this->estado === self::ESTADO_CERRADO;
    }

    public function isActive(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    public function isInProgress(): bool
    {
        return $this->estado === self::ESTADO_EN_PROCESO;
    }
}
