<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function rules(): array
    {
        $routeName = request()->route()->getName();
        $isPayOrPaid = $routeName == 'order.pay' || $routeName == 'order.paid';
        $isOrderCall = $routeName == 'order.call';
        $isOrderAdd = $routeName == 'order.add';

        $required = 'required';
        $nullable = 'nullable';
        $array = 'array';
        $string = 'string';
        $numeric = 'numeric';
        $integer = 'integer';

        $callRequired = $isOrderCall ? $nullable : $required;
        $addRequired = $isOrderAdd ? $required : $nullable;
        $payRequired = $isPayOrPaid ? $required : $nullable;

        return [
            // client data
            'user' => [$required, $array],
            'user.id' => [$required, $string],
            'user.name' => [$required, $string],

            // order data
            'order_id' => [$nullable, $string],

            // table data
            'table' => [$required, $array],
            'table.id' => [$required, $string],
            'table.name' => [$required, $string],

            // dishes data
            'dish_list' => [$callRequired, $array],
            'dish_list.name' => [$callRequired, $string],

            'dish_list.dishes' => [$callRequired, $array],
            'dish_list.dishes.*.id' => [$callRequired, $string],
            'dish_list.dishes.*.name' => [$callRequired, $string],
            'dish_list.dishes.*.price' => [$callRequired, $numeric],
            'dish_list.dishes.*.count' => [$callRequired, $integer],
            
            'dish_list.add_dishes' => [$addRequired, $array],
            'dish_list.add_dishes.*.id' => [$addRequired, $string],
            'dish_list.add_dishes.*.name' => [$addRequired, $string],
            'dish_list.add_dishes.*.price' => [$addRequired, $numeric],
            'dish_list.add_dishes.*.count' => [$addRequired, $integer],

            // workers data
            'waiter_id' => [$required, $string],

            // payment data
            'pay_type' => [$payRequired, $string],
        ];
    }
}
