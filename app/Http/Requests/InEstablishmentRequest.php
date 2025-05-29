<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InEstablishmentRequest extends FormRequest
{
    protected string $required = 'required';
    protected string $nullable = 'nullable';
    protected string $array = 'array';
    protected string $string = 'string';
    protected string $numeric = 'numeric';
    protected string $integer = 'integer';
    protected string $dateFormat = 'date_format:Y-m-d H:i:s';

    protected string $min0 = 'min:0';


    public function rules(): array
    {
        return [
            'reservation_id' => [$this->required, $this->string],

            // client data
            'user' => [$this->required, $this->array],
            'user.id' => [$this->required, $this->string],
            'user.name' => [$this->required, $this->string],
            'come_time' => [$this->required, $this->dateFormat],

            // order data
            'order_id' => [$this->required, $this->string],

            // table data
            'table' => [$this->required, $this->array],
            'table.id' => [$this->required, $this->string],
            'table.name' => [$this->required, $this->string],

            // dishes data
            'dish_list' => ['sometimes', $this->nullable, $this->array],
            'dish_list.name' => ['sometimes', $this->required, $this->string],
            'dish_list.dishes' => ['sometimes', $this->nullable, $this->array, $this->min0],
            'dish_list.dishes.*.id' => ['sometimes', $this->required, $this->string],
            'dish_list.dishes.*.name' => ['sometimes', $this->required, $this->string],
            'dish_list.dishes.*.price' => ['sometimes', $this->required, $this->numeric],
            'dish_list.dishes.*.count' => ['sometimes', $this->required, $this->integer],

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
        ];
    }
}
