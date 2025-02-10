<?php

namespace App\Bot;

use App\Jobs\SendAssignReserveJob;
use App\Jobs\SendWaiterAssignTableJob;
use App\Models\Bot\Customer;
use App\Models\Bot\Text;
use App\Models\Logger;
use App\Models\Project\Notification;
use App\Services\Project\InEstablishmentService;
use App\Services\Project\OrderService;
use App\Services\Tabster\TabsterService;

class TmWaiter extends TmCommon
{
    public function visitorIn(Notification $notification)
    {
        $placeholders = TabsterService::getPlaceholdersFromNotification($notification);
        $text = Text::getPrepared('visitorIn', $placeholders);
        $buttons[] = [
            [
                'text' => Text::getPrepared('assignReserveBtn'),
                'callback_data' => json_encode(['a' => 'w-ar', 'nid' => $notification->id]),
            ],
            [
                'text' => Text::getPrepared('rejectReserveBtn'),
                'callback_data' => json_encode(['a' => 'w-rr', 'nid' => $notification->id]),
            ]
        ];
        $this->sendButton($this->prepareText($text), $buttons);
        $this->saveResponseMessageIdToCommon();
        Logger::commit([$this->init->customer->id, $this->response['result']['message_id']], __METHOD__);
        InEstablishmentService::saveMessageId($notification, $this->init->customer->id, $this->response['result']['message_id']);
    }

    public function assignReserve()
    {
        if (!isset($this->init->data->nid) || !($notification = Notification::whereId($this->init->data->nid)->first())) {
            $this->unknown();
        }
        $notification->addData(['assigned_by' => Customer::ROLE_ADMIN, 'assigned_waiter_id' => $this->init->customer->external_id]);
        $notification->update(['status' => Notification::STATUS_ASSIGNED]);
        InEstablishmentService::deleteMessages($notification);
        SendWaiterAssignTableJob::dispatch($notification);
    }

    public function rejectReserve()
    {
        /** @var Notification $notification */
        if (!isset($this->init->data->nid) || !($notification = Notification::whereId($this->init->data->nid)->first())) {
            $this->unknown();
        }
        InEstablishmentService::deleteMessageForWaiter($notification, $this);
    }

    private function handleMessage(string $template, array $data)
    {
        $placeholders = TabsterService::getPlaceholdersFromData($data);
        $text = Text::getPrepared($template, $placeholders);
        $this->sendMessage($text);
        $this->saveResponseMessageIdToCommon();
    }

    public function orderAdditional(array $data)
    {
        $this->handleMessage('orderAdditional', $data);
    }

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

    public function visitorEvaluate(array $data)
    {
        $this->handleMessage('visitorEvaluate', $data);
    }
}
