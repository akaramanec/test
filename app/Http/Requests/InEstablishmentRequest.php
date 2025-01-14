<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InEstablishmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'visitor_id' => ['required', 'string'],
            'establishment_id' => ['required', 'string'],
            'come_time' => ['required', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
