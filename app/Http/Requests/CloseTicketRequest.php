<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket !== null && ($this->user()?->can('close', $ticket) ?? false);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion_problema' => ['nullable', 'string'],
            'resolucion' => ['required', 'string'],
            'causa_raiz' => ['required', 'string'],
            'tipo_resolucion' => ['required', 'in:workaround,solucion_definitiva'],
            'es_reutilizable' => ['required', 'boolean'],
            'categoria' => ['nullable', 'in:backend,frontend,bases_de_datos,devops,testing,seguridad,otro'],
        ];
    }
}
