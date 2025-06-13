<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreOrderRequest extends FormRequest
{
    protected string $required = 'required';
    protected string $nullable = 'nullable';
    protected string $array = 'array';
    protected string $string = 'string';
    protected string $numeric = 'numeric';
    protected string $integer = 'integer';
    protected string $min0 = 'min:0';

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

            // dishes data
            'dish_list' => ['sometimes', $this->required, $this->array],
            'dish_list.name' => ['sometimes', $this->required, $this->string],

            'dish_list.dishes' => ['sometimes', $this->required, $this->array, $this->min0],
            'dish_list.dishes.*.id' => ['sometimes', $this->required, $this->string],
            'dish_list.dishes.*.name' => ['sometimes', $this->required, $this->string],
            'dish_list.dishes.*.price' => ['sometimes', $this->required, $this->numeric],
            'dish_list.dishes.*.count' => ['sometimes', $this->required, $this->integer],

            'food_serving_status' => [$this->required, $this->string],

            // workers data
            'workers' => [$this->required, $this->array],
            'workers.admins' => [$this->required, $this->array],
            'workers.admins.*.id' => [$this->required, $this->string],
            'workers.admins.*.name' => [$this->required, $this->string],
            'workers.admins.*.phone' => [$this->required, $this->string],

            // payment data
            'pay_type' => [$this->required, $this->string],
            // time data
            'time' => [$this->required, $this->string],
        ];
    }
}
