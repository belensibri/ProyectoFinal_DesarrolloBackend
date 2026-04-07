<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Ticket::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'categoria' => ['nullable', 'in:backend,frontend,bases_de_datos,devops,testing,seguridad,otro'],
            'prioridad' => ['required', 'in:baja,media,alta'],
        ];
    }
}
