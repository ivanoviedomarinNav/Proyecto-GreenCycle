<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTreeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autenticación ya la exige el middleware de la ruta
    }

    public function rules(): array
    {
        return [
            'seed_type_id' => ['required', 'integer', 'exists:seed_types,id'],
        ];
    }
}
