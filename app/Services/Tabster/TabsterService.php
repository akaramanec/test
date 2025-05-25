<?php

namespace App\Services\Tabster;

use App\Models\Bot\Text;
use App\Models\Project\Notification;

class TabsterService
{
    public static function getWorker($phone)
    {

        $tabsterApi = new TabsterApi();
        $requestData = [
            'event_type' => 'PICK_UP_WORKER',
            'date' => now()->toIso8601String(),
            'payload' => [
                'phone' => $phone,
            ]
        ];
        return $tabsterApi->post('/telegram/events', $requestData);
    }

    public static function assignWaiter(Notification $notification)
    {
        $tabsterApi = new TabsterApi();
        $requestData = [
            'event_id' => $notification->uuid,
            'event_type' => 'PICK_UP_RESERVATION',
            'date' => $notification->updated_at->toIso8601String(),
            'payload' => [
                'waiter_id' => $notification->data['assigned_employer_id'],
                'reservation_id' => $notification->data['reservation_id'],
                'restaurant_id' => $notification->data['restaurant_id'],
                'phone' => $notification->data['user']['phone'] ?? null,
            ]
        ];
        $response = $tabsterApi->post('/telegram/events', $requestData);
        return [$response, $requestData];
    }

    public static function getPlaceholdersFromNotification(Notification $notification)
    {
        $data = self::getPlaceholdersFromData($notification->data);
        $data['{time}'] = $notification->data['time'];
        return $data;
    }

    public static function getPlaceholdersFromData(array $data)
    {
        $dishes = '';
        $addDishes = '';

        if (isset($data['dish_list']['dishes'])) {
            foreach ($data['dish_list']['dishes'] as $key => $dish) {
                $dishes .= ($key + 1) . '. ' . $dish['name'] . " (x" . $dish['count'] . ")" . PHP_EOL;
            }
        }
        if (isset($data['dish_list']['add_dishes'])) {
            foreach ($data['dish_list']['add_dishes'] as $key => $dish) {
                $addDishes .= ($key + 1) . '. ' . $dish['name'] . " (x" . $dish['count'] . ")" . PHP_EOL;
            }
        }

        $dishList = '';
        if (!empty($dishes)) {
            $dishList = Text::getPrepared('preOrderDish', ['{dishes}' => $dishes]);
        }

        if (!empty($addDishes)) {
            if ($dishList) $dishList .= PHP_EOL;
            $dishList .= Text::getPrepared('orderAddDish', ['{add_dishes}' => $addDishes]);
        }

        $time = isset($data['time']) ? date('H:i', strtotime($data['time'])) : now()->format('H:i');
        return [
            '{visitor_name}' => $data['user']['name'],
            '{visitor_phone}' => $data['user']['phone'] ?? '',
            '{pay_type}' => isset($data['pay_type']) ? Text::getPrepared($data['pay_type']) : '',
            '{table}' => $data['table']['name'],
            '{zone}' => $data['table']['zone'],
            '{dish_list}' => $dishList,
            '{time}' => $time,
            '{evaluate}' => $data['evaluate'] ?? '',
        ];
    }
}
