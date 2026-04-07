<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comentario_id',
        'ticket_id',
        'usuario_id',
        'ruta_archivo',
        'nombre',
        'mime_type',
        'size',
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comentario_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function isImage(): bool
    {
        if (filled($this->mime_type) && Str::startsWith($this->mime_type, 'image/')) {
            return true;
        }

        return in_array(
            Str::lower(pathinfo($this->ruta_archivo, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png'],
            true,
        );
    }

    public function getDisplayName(): string
    {
        return $this->nombre ?: basename($this->ruta_archivo);
    }

    public function getPublicUrl(): string
    {
        return Storage::disk('public')->url($this->ruta_archivo);
    }

    public function getImageSource(): ?string
    {
        if (! $this->isImage()) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->ruta_archivo)) {
            return $this->getPublicUrl();
        }

        $contents = Storage::disk('public')->get($this->ruta_archivo);
        $mimeType = $this->mime_type ?: Storage::disk('public')->mimeType($this->ruta_archivo) ?: 'image/png';

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }
}
