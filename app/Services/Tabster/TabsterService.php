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
            'event_type' => 'PICK_UP_WORKER ',
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
            'event_type' => 'PICK_UP_RESERVATION ',
            'date' => $notification->updated_at->toIso8601String(),
            'payload' => [
                'waiter_id' => $notification->data['assigned_waiter_id'],
                'reservation_id' => $notification->data['reservation_id'],
                'restaurant_id' => $notification->data['restaurant_id'],
            ]
        ];
        $response = $tabsterApi->post('/telegram/events', $requestData);
        return [$response, $requestData];
    }

    public static function getPlaceholdersFromNotification(Notification $notification)
    {
        $dishes = '';
        foreach ($notification->data['dish_list']['dishes'] as $key => $dish) {
            $dishes .= $key + 1 . '. ' . $dish['name'] . PHP_EOL;
        }
        if (!$dishes) {
            $dishes = Text::getPrepared('noPreOrderDishes');
        }

        $data = self::getPlaceholdersFromData($notification->data);
        $data['{dishes}'] = $dishes;
        $data['{come_time}'] = $notification->data['come_time'];
        return $data;
    }

    public static function getPlaceholdersFromData(array $data)
    {
        $dishes = '';
        $addDishes = '';

        if (isset($notification->data['dish_list'])) {
            if (isset($notification->data['dish_list']['dishes'])) {
                foreach ($notification->data['dish_list']['dishes'] as $key => $dish) {
                    $dishes .= $key + 1 . '. ' . $dish['name'] . PHP_EOL;
                }
            }
            if (isset($notification->data['dish_list']['add_dishes'])) {
                foreach ($notification->data['dish_list']['add_dishes'] as $key => $dish) {
                    $addDishes .= $key + 1 . '. ' . $dish['name'] . PHP_EOL;
                }
            }
        }

        return [
            '{visitor_name}' => $data['user']['name'],
            '{visitor_phone}' => $data['user']['phone'],
            '{pay_type}' => $data['pay_type'] ?? '',
            '{table}' => $data['table']['name'],
            '{dishes}' => $dishes,
            '{add_dishes}' => $addDishes,
        ];
    }
}
