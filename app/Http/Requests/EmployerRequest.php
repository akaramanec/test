<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', Rule::exists('employers', 'phone')],
            'name' => ['nullable', 'string'],
            'restaurant_id' => ['nullable'],
        ];
    }
}
