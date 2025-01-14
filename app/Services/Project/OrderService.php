<?php

namespace App\Services\Project;

class OrderService
{
    public static function getPlaceholders(array $data): array
    {
        return [
            '{order}' => $data['order_id'] ?? '',
            '{table}' => $data['table']['name'] ?? '',
            '{visitor}' => $data['visitor']['name'] ?? '',
            '{add}' => $data['add'] ?? '',
            '{payType}' => $data['pay_type'] ?? '',
            '{evaluate}' => $data['evaluate'] ?? '',
        ];
    }
}
