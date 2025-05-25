<?php

namespace App\Bot;

use App\Jobs\SendWaiterAssignTableJob;
use App\Models\Bot\Employer;
use App\Models\Bot\Text;
use App\Models\Project\Notification;
use App\Services\Project\InEstablishmentService;
use App\Services\Tabster\TabsterService;

class TmEmployer extends TmCommon
{
    public function orderPay(array $data)
    {
        $this->handleMessage('orderPay', $data);
    }

    public function orderPaid(array $data)
    {
        $this->handleMessage('orderPaid', $data);
    }

    public function orderCall(array $data)
    {
        $this->handleMessage('orderCall', $data);
    }

    public function orderAdditional(array $data)
    {
        $this->handleMessage('orderAdditional', $data);
    }

    public function visitorEvaluate(array $data, $additionalPlaceholders = [])
    {
        $this->handleMessage('visitorEvaluate', $data, $additionalPlaceholders);
    }

    public function handleMessage(string $template, array $data, $additionalPlaceholders = [])
    {
        $placeholders = TabsterService::getPlaceholdersFromData($data);
        $placeholders = array_merge($placeholders, $additionalPlaceholders);
        $text = Text::getPrepared($template, $placeholders);
        $this->sendMessage($text);
        $this->saveResponseMessageIdToCommon();
    }
}
