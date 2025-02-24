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
            'order_id' => ['nullable', 'string'],

            // table data
            'table' => ['required', 'array'],
            'table.id' => ['required', 'string'],
            'table.name' => ['required', 'string'],

            // dishes data
            'dish_list' => [request()->route()->getName() == 'order.call' ? 'nullable' : 'required', 'array'],
            'dish_list.name' => [request()->route()->getName() == 'order.call' ? 'nullable' : 'required', 'string'],
            'dish_list.dishes' => [request()->route()->getName() == 'order.call' ? 'nullable' : 'required', 'array'],
            'dish_list.dishes.*.id' => [request()->route()->getName() == 'order.call' ? 'nullable' : 'required', 'string'],
            'dish_list.dishes.*.name' => [request()->route()->getName() == 'order.call' ? 'nullable' : 'required', 'string'],
            'dish_list.dishes.*.price' => [request()->route()->getName() == 'order.call' ? 'nullable' : 'required', 'numeric'],
            'dish_list.dishes.*.count' => [request()->route()->getName() == 'order.call' ? 'nullable' : 'required', 'integer'],
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
