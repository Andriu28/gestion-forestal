<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProducerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'cedula' => ['required', 'string', 'max:20', 'regex:/^[VEPJG]\d{5,8}$/', 'unique:producers,cedula'],
            'lastname' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del productor es obligatorio.',
        ];
    }
}