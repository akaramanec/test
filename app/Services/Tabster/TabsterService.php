<?php

namespace App\Services\Tabster;

use App\Models\Logger;
use App\Models\Project\Establishment;
use App\Models\Project\Order;
use App\Models\Project\Reservation;
use App\Models\Project\Table;
use App\Models\Project\Visitor;
use Faker\Factory;

class TabsterService
{

    public $faker;
    public function __construct($fakerLocal = false)
    {
        if ($fakerLocal) {
            $this->faker = Factory::create($fakerLocal);
        }
    }

    public static function getCustomerData(string $phone)
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

    public static function assignReserve(Reservation $reservation)
    {

    }

    public function getVisitor($id)
    {
        if ($this->faker) {
            return [
                'id' => $id,
                'phone' => $this->faker->phoneNumber,
                'name' => $this->faker->name,
            ];
        }
        return $this->get('visitors/' . $id);
    }
}
