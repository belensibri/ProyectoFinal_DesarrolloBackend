<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => 'sometimes|exists:departments,id',
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'prioridad' => 'sometimes|in:baja,media,alta'
        ];
    }
}
