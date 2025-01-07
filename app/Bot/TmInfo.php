<?php

namespace App\Bot;

class TmInfo extends TmApi
{
    public $getMe;

    public $first_name;

    public $username;

    public $webHookInfo;

    public $url;

    public $pending_update_count;

    public $max_connections;

    public function __construct()
    {
        $this->getMe();
        $this->getWebHookInfo();
    }

    public function getMe()
    {
        $this->getMe = $this->get('getMe');
        if ($this->getMe['ok'] == false) {
            return;
        }
        if (isset($this->getMe['result']['first_name'])) {
            $this->first_name = $this->getMe['result']['first_name'];
        }
        if (isset($this->getMe['result']['username'])) {
            $this->username = $this->getMe['result']['username'];
        }
    }

    public function getWebHookInfo()
    {
        $this->webHookInfo = $this->get('getWebhookInfo');
        if ($this->webHookInfo['ok'] == false) {
            return;
        }
        if (isset($this->webHookInfo['result']['url'])) {
            $this->url = $this->webHookInfo['result']['url'];
        }
        if (isset($this->webHookInfo['result']['pending_update_count'])) {
            $this->pending_update_count = $this->webHookInfo['result']['pending_update_count'];
        }
        if (isset($this->webHookInfo['result']['max_connections'])) {
            $this->max_connections = $this->webHookInfo['result']['max_connections'];
        }
    }

    public function setWebhookUrl()
    {
        return 'https://api.telegram.org/bot'.config('app.token_tm').'/setWebhook?url='.config('app.url').'/api/telegram';
    }

    public function getMeUrl()
    {
        return 'https://api.telegram.org/bot'.config('app.token_tm').'/getMe';
    }
}
