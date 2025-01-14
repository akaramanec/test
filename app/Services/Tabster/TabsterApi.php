<?php

namespace App\Services\Tabster;

use App\Models\Logger;
use App\Models\Project\Establishment;
use App\Models\Project\Order;
use App\Models\Project\Reservation;
use App\Models\Project\Table;
use App\Models\Project\Visitor;
use Faker\Factory;

class TabsterApi
{

    protected string $url;
    protected string $token;
    public $faker;

    public function __construct($fakerLocal = null)
    {
        if ($fakerLocal) {
            $this->faker = Factory::create($fakerLocal);
        }
        $this->url = config('tabster.url');
        $this->token = config('tabster.token');
    }

    private function curl($options = [])
    {
        $curl = curl_init($options['url']);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $options['headers']);
        if ($options['mode'] == 'post') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options['data']));
            curl_setopt($curl, CURLOPT_POST, true);
        }
        if ($options['mode'] == 'get') {
            curl_setopt($curl, CURLOPT_HTTPGET, true);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $output = curl_exec($curl);

        if ($output === false) {
            Logger::commit(['curl false', curl_error($curl)], __METHOD__);
        }
        curl_close($curl);
        if (is_string($output) && is_array($a = json_decode($output, true))) {
            return $a;
        } else {
            return $output;
        }
    }

    private function headers()
    {
        return [
            'Accept: application/json',
            'Cache-Control: no-cache',
            'Content-Type: application/json; charset=UTF-8',
        ];
    }

    public function post($uri, $data)
    {
        return $this->curl([
            'headers' => $this->headers(),
            'mode' => 'post',
            'url' => $this->url . $uri,
            'data' => $data,
        ]);
    }

    public function get($uri)
    {
        return $this->curl([
            'headers' => $this->headers(),
            'mode' => 'get',
            'url' => $this->url . $uri,
        ]);
    }
}
