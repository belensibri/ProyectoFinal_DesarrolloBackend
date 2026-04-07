<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReassignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket !== null && ($this->user()?->can('reassign', $ticket) ?? false);
    }

    public function rules(): array
    {
        return [
            'tecnico_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tipo_usuario', 'TECNICO')),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $ticket = $this->route('ticket');

                if ($ticket !== null && (int) $this->input('tecnico_id') === (int) $ticket->tecnico_id) {
                    $validator->errors()->add('tecnico_id', 'Debes seleccionar un técnico distinto al actualmente asignado.');
                }
            },
        ];
    }
}
