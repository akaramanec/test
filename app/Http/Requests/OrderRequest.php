<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
        $routeName = request()->route()->getName();
        $isPayOrPaid = $routeName == 'order.pay' || $routeName == 'order.paid';
        $isOrderCall = $routeName == 'order.call';
        $isOrderAdd = $routeName == 'order.add';

        $callRequired = $isOrderCall ? $this->nullable : $this->required;
        $addRequired = $isOrderAdd ? $this->required : $this->nullable;
        $payRequired = $isPayOrPaid ? $this->required : $this->nullable;

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
            'dish_list' => ['sometimes', $callRequired, $this->array],
            'dish_list.name' => ['sometimes', $callRequired, $this->string],

            'dish_list.dishes' => ['sometimes', $callRequired, $this->array, $this->min0],
            'dish_list.dishes.*.id' => ['sometimes', $callRequired, $this->string],
            'dish_list.dishes.*.name' => ['sometimes', $callRequired, $this->string],
            'dish_list.dishes.*.price' => ['sometimes', $callRequired, $this->numeric],
            'dish_list.dishes.*.count' => ['sometimes', $callRequired, $this->integer],

            'dish_list.add_dishes' => ['sometimes', $addRequired, $this->array, $this->min0],
            'dish_list.add_dishes.*.id' => ['sometimes', $addRequired, $this->string],
            'dish_list.add_dishes.*.name' => ['sometimes', $addRequired, $this->string],
            'dish_list.add_dishes.*.price' => ['sometimes', $addRequired, $this->numeric],
            'dish_list.add_dishes.*.count' => ['sometimes', $addRequired, $this->integer],

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

            // payment data
            'pay_type' => [$payRequired, $this->string],
        ];
    }
}
