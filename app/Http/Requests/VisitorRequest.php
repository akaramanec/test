<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VisitorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'visitor_id' => ['required', 'string'],
            'order_id' => ['required', 'string'],
            'reserve_id' => ['required', 'string'],
            'establishment_id' => ['required', 'string'],
        ];
    }
}
