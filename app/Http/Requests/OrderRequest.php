<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // client data
            'user' => ['required', 'array'],
            'user.id' => ['required', 'string'],
            'user.name' => ['required', 'string'],

            // order data
            'order_id' => ['required', 'string'],

            // table data
            'table' => ['required', 'array'],
            'table.id' => ['required', 'string'],
            'table.name' => ['required', 'string'],

            // dishes data
            'dish_list' => ['required', 'array'],
            'dish_list.name' => ['required', 'string'],
            'dish_list.dishes' => ['required', 'array'],
            'dish_list.dishes.*.id' => ['required', 'string'],
            'dish_list.dishes.*.name' => ['required', 'string'],
            'dish_list.dishes.*.price' => ['required', 'numeric'],
            'dish_list.dishes.*.count' => ['required', 'integer'],
            'dish_list.add_dishes' => [request()->route()->getName() == 'order.add' ? 'required' : 'nullable', 'array'],
            'dish_list.add_dishes.*.id' => [request()->route()->getName() == 'order.add' ? 'required' : 'nullable', 'string'],
            'dish_list.add_dishes.*.name' => [request()->route()->getName() == 'order.add' ? 'required' : 'nullable', 'string'],
            'dish_list.add_dishes.*.price' => [request()->route()->getName() == 'order.add' ? 'required' : 'nullable', 'numeric'],
            'dish_list.add_dishes.*.count' => [request()->route()->getName() == 'order.add' ? 'required' : 'nullable', 'integer'],

            // workers data
            'waiter_id' => ['required', 'string'],
        ];
    }
}
