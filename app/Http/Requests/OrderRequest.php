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
            'dish_list' => [$callRequired, $this->array],
            'dish_list.name' => [$callRequired, $this->string],

            'dish_list.dishes' => [$callRequired, $this->array],
            'dish_list.dishes.*.id' => [$callRequired, $this->string],
            'dish_list.dishes.*.name' => [$callRequired, $this->string],
            'dish_list.dishes.*.price' => [$callRequired, $this->numeric],
            'dish_list.dishes.*.count' => [$callRequired, $this->integer],
            
            'dish_list.add_dishes' => [$addRequired, $this->array],
            'dish_list.add_dishes.*.id' => [$addRequired, $this->string],
            'dish_list.add_dishes.*.name' => [$addRequired, $this->string],
            'dish_list.add_dishes.*.price' => [$addRequired, $this->numeric],
            'dish_list.add_dishes.*.count' => [$addRequired, $this->integer],

            // workers data
            'waiter_id' => [$this->required, $this->string],

            // payment data
            'pay_type' => [$payRequired, $this->string],
        ];
    }
}
