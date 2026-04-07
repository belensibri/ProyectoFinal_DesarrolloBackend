<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket !== null && ($this->user()?->can('update', $ticket) ?? false);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'string'],
            'categoria' => ['sometimes', 'nullable', 'in:backend,frontend,bases_de_datos,devops,testing,seguridad,otro'],
            'prioridad' => ['sometimes', 'in:baja,media,alta'],
        ];
    }
}
