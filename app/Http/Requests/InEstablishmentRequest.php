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
    
    protected string $requiredWith = 'required_with';
    protected string $requiredWithDishList;
    protected string $min0 = 'min:0';

    public function __construct()
    {
        parent::__construct();
        $this->requiredWithDishList = $this->requiredWith . ':dish_list';
    }

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
            'dish_list' => [$this->nullable, $this->array],
            'dish_list.name' => [$this->requiredWithDishList, $this->string],
            'dish_list.dishes' => [$this->requiredWithDishList, $this->array, $this->min0],
            'dish_list.dishes.*.id' => [$this->requiredWithDishList, $this->string],
            'dish_list.dishes.*.name' => [$this->requiredWithDishList, $this->string],
            'dish_list.dishes.*.price' => [$this->requiredWithDishList, $this->numeric],
            'dish_list.dishes.*.count' => [$this->requiredWithDishList, $this->integer],

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
        ];
    }
}
