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
            // client data
            'user' => [$this->required, $this->array],
            'user.id' => [$this->required, $this->string],
            'user.name' => [$this->required, $this->string],

            // order data
            'order_id' => [$this->nullable, $this->string],

            // table data
            'table' => [$this->required, $this->array],
            'table.id' => [$this->required, $this->string],
            'table.name' => [$this->required, $this->string],

            // workers data
            'workers' => [$this->required, $this->array],
            'workers.admins' => [$this->required, $this->array],
            'workers.admins.*.id' => [$this->required, $this->string],
            'workers.admins.*.name' => [$this->required, $this->string],
            'workers.admins.*.phone' => [$this->required, $this->string],
            'workers.waiters' => [$this->required, $this->array],
            'workers.waiters.*.id' => [$this->required, $this->string],
            'workers.waiters.*.name' => [$this->required, $this->string],
            'workers.waiters.*.phone' => [$this->required, $this->string],

            // assigned waiter data
            'waiter_id' => [$this->nullable, $this->string],

            // evaluation data
            'evaluate' => [$this->required, $this->string],
        ];
    }
}
