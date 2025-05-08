<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluationRequest extends FormRequest
{
    protected string $required = 'required';
    protected string $string = 'string';

    public function rules(): array
    {
        return [
            'visitor_id' => [$this->required, $this->string],
            'order_id' => [$this->required, $this->string],
            'evaluate' => [$this->required, $this->string],
        ];
    }
}
