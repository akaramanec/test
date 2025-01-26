<?php

namespace App\Services\Tabster;

use App\Models\Bot\Customer;
use App\Models\Bot\Text;
use App\Models\Logger;
use App\Models\Project\Notification;
use Faker\Factory;

class TabsterService
{

    public static function getWorker(string $phone)
    {
        return [
            'phone' => $phone,
            'name' => 'Иван',
            'surname' => 'Иванов',
            'patronymic' => 'Иванович',
            'role' => 'admin',
            'establishment_id' => 1,
        ];
    }

    public static function getVisitor($id, $fake = null)
    {
        if ($fake) {
            $fake = Factory::create('uk_UA');
            return [
                'id' => $id,
                'phone' => $fake->phoneNumber,
                'name' => $fake->name,
            ];
        }
        return (new TabsterApi())->get('/visitors/' . $id);
    }

    public static function getEstablishment($id, $fake = null)
    {
        if ($fake) {
            return [
                'id' => $id,
                'name' => "CremeSoda",
                'address' => "Gdeto nedaleko str., 111",
                'phone' => "380999999999",
            ];
        }
        return (new TabsterApi())->get('/establishments/');
    }

    public static function getWorkers($id, $fake = null)
    {
        if ($fake) {
            return Customer::whereStatus(Customer::STATUS_ACTIVE)->whereNotNull('external_id')->pluck('external_id');
        }
        return (new TabsterApi())->get('/establishments/' . $id . '/workers');
    }

    public static function getOrder($orderId, $fake = null)
    {
        if ($fake) {
            return [
                'id' => $orderId,
                'dishes' => "Mnogo mnogo vkusnoy edi",
                'status' => "new",
                'table' => [
                    'id' => 1,
                    'name' => "Table 1",
                ]
            ];
        }
        return (new TabsterApi())->get('/orders/' . $orderId);
    }

    public static function getReserve($reserveId, $fake = null)
    {
        if ($fake) {
            $fake = Factory::create('uk_UA');
            return [
                'id' => $reserveId,
                'dishes' => "Mnogo mnogo vkusnoy edi",
                'status' => "new",
                'establishment_id' => "establishment123",
                'phone' => $fake->phoneNumber,
                'table' => [
                    'id' => "table123",
                    'name' => "Table 1",
                ]
            ];
        }
        return (new TabsterApi())->get('/reserves/' . $reserveId);
    }

    public static function assignWaiter(Notification $notification)
    {
        $waiter = $notification->data['assigned_waiter_id'];
        return (new TabsterApi())->post('/orders/' . $notification->data['order_id'] . '/worker', [
            'waiter_id' => $waiter
        ]);
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

    public static function composeOrderData(array $data)
    {
        $order = TabsterService::getOrder($data['order_id'], config('app.url') === 'https://base-bot.boto.kyiv.ua');
        if (isset($data['waiter_id'])) $order['waiter_id'] = $data['waiter_id'];
        if (isset($data['admin_id'])) $order['admin_id'] = $data['admin_id'];
        if (isset($data['add'])) $order['add'] = $data['add'];
        if (isset($data['pay_type'])) $order['pay_type'] = $data['pay_type'];
        if (isset($data['add'])) $order['add'] = $data['add'];
        if (!isset($order['visitor'])) {
            $visitor = TabsterService::getVisitor($data['visitor_id'],
                config('app.url') === 'https://base-bot.boto.kyiv.ua');
            $order['visitor'] = $visitor;
        }
        return $order;
    }

    public static function composeReserveData(array $data)
    {
        $reserve = TabsterService::getReserve($data['reserve_id'], config('app.url') === 'https://base-bot.boto.kyiv.ua');
        if (isset($data['admin_id'])) {
            $reserve['admin_id'] = $data['admin_id'];
        } else {
            $reserve['worker_ids'] = TabsterService::getWorkers($reserve['establishment_id'], config('app.url') === 'https://base-bot.boto.kyiv.ua');
        }
        if (!isset($reserve['visitor'])) {
            $visitor = TabsterService::getVisitor($data['visitor_id'],
                config('app.url') === 'https://base-bot.boto.kyiv.ua');
            $reserve['visitor'] = $visitor;
        }
        return $reserve;
    }
}
