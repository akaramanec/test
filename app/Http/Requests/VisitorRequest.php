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

            'come_time' => [request()->route()->getName() == 'visitor.in' ? 'required' : 'nullable', 'date_format:Y-m-d H:i:s'],
            'evaluate' => [request()->route()->getName() == 'visitor.evaluate' ? 'required' : 'nullable', 'numeric'],

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
