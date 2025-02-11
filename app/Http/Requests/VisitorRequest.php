<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VisitorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reservation_id' => ['required', 'string'],

            // client data
            'user' => ['required', 'array'],
            'user.id' => ['required', 'string'],
            'user.name' => ['required', 'string'],

            'restaurant_id' => [request()->route()->getName() == 'visitor.in' ? 'required' : 'nullable', 'string'],
            'time' => [request()->route()->getName() == 'visitor.in' ? 'required' : 'nullable', 'date_format:Y-m-d H:i:s'],
            'evaluate' => [request()->route()->getName() == 'visitor.evaluate' ? 'required' : 'nullable', 'numeric'],

            // order data
            'order_id' => ['required', 'string'],

            // table data
            'table' => ['required', 'array'],
            'table.id' => ['required', 'string'],
            'table.name' => ['required', 'string'],

            // dishes data
            'dish_list' => ['nullable', 'array'],
            'dish_list.name' => ['required_with:dish_list', 'string'],
            'dish_list.dishes' => ['required_with:dish_list', 'array'],
            'dish_list.dishes.*.id' => ['required_with:dish_list', 'string'],
            'dish_list.dishes.*.name' => ['required_with:dish_list', 'string'],
            'dish_list.dishes.*.price' => ['required_with:dish_list', 'numeric'],
            'dish_list.dishes.*.count' => ['required_with:dish_list', 'integer'],

            // workers data
            'workers' => ['required', 'array'],
            'workers.admins' => ['required', 'array'],
            'workers.admins.*.id' => ['required', 'string'],
            'workers.admins.*.name' => ['required', 'string'],
            'workers.admins.*.phone' => ['required', 'string'],
            'workers.waiters' => ['required', 'array'],
            'workers.waiters.*.id' => ['required', 'string'],
            'workers.waiters.*.name' => ['required', 'string'],
            'workers.waiters.*.phone' => ['required', 'string'],
        ];
    }
}
