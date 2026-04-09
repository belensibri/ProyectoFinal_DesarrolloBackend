<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

#[Fillable(['name', 'email', 'password', 'tipo_usuario'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'usuario_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'tecnico_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'usuario_id');
    }

    public function faqArticles()
    {
        return $this->hasMany(FaqArticle::class, 'usuario_id');
    }

    public function ticketHistories()
    {
        return $this->hasMany(TicketHistory::class, 'usuario_id');
    }

    public function isUsuario(): bool
    {
        return $this->tipo_usuario === 'USUARIO';
    }

    public function isTecnico(): bool
    {
        return $this->tipo_usuario === 'TECNICO';
    }

    public function isAdministrador(): bool
    {
        return $this->tipo_usuario === 'ADMINISTRADOR';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
