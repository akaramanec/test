<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InEstablishmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reservation_id' => ['required', 'string'],

            // client data
            'user' => ['required', 'array'],
            'user.id' => ['required', 'string'],
            'user.name' => ['required', 'string'],
            'come_time' => ['required', 'date_format:Y-m-d H:i:s'],

            // order data
            'order_id' => ['required', 'string'],

            // table data
            'table' => ['required', 'array'],
            'table.id' => ['required', 'string'],
            'table.name' => ['required', 'string'],

            // dishes data
            'dish_list' => ['required', 'array'],
            'dish_list.name' => ['required', 'string'],
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
