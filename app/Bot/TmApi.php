<?php

namespace App\Bot;

use App\Models\Logger;

class TmApi
{
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

    private function url()
    {
        return 'https://api.telegram.org/bot'.config('app.token_tm').'/';
    }

    private function headers()
    {
        return [
            'Accept: application/json',
            'Cache-Control: no-cache',
            'Content-Type: application/json; charset=UTF-8',
        ];
    }

    public function multipartFormData($url, $data)
    {
        return $this->curl([
            'headers' => [
                'Accept: application/json',
                'Cache-Control: no-cache',
                'Content-Type: multipart/form-data; charset=UTF-8',
            ],
            'mode' => 'post',
            'data' => $data,
            'url' => $this->url().$url,
        ]);
    }

    public function post($url, $data)
    {
        return $this->curl([
            'headers' => $this->headers(),
            'mode' => 'post',
            'url' => $this->url().$url,
            'data' => $data,
        ]);
    }

    public function get($url)
    {
        dump($this->url().$url);

        return $this->curl([
            'headers' => $this->headers(),
            'mode' => 'get',
            'url' => $this->url().$url,
        ]);
    }
}
