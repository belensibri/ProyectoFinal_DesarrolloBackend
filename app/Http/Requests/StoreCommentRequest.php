<?php

namespace App\Http\Requests;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Ticket|null $ticket */
        $ticket = $this->route('ticket');

        return $ticket !== null
            && ($this->user()?->can('create', [Comment::class, $ticket, $this->input('rol')]) ?? false);
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()?->isTecnico()) {
            $this->merge(['rol' => Comment::ROL_TECNICO]);
        }

        if ($this->user()?->isUsuario()) {
            $this->merge(['rol' => Comment::ROL_USUARIO]);
        }
    }

    public function rules(): array
    {
        return [
            'contenido' => ['required', 'string'],
            'rol' => ['required', 'in:tecnico,usuario'],
        ];
    }
}
