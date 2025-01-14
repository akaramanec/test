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

class TmWaiter extends TmCommon
{
    public function visitorIn(Notification $notification)
    {
        $placeholders = InEstablishmentService::getPlaceholders($notification);
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
        /** @var Notification $notification */
        if (!isset($this->init->data->nid) || !($notification = Notification::whereId($this->init->data->nid)->first())) {
            $this->unknown();
        }

        $data = $notification->data;
        $data['assigned_by'] = Customer::ROLE_WAITER;
        $data['assigned_waiter_id'] = $this->init->customer->external_id;
        $notification->update(['status' => 'done', 'data' => $data]);
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

    private function handleMessage(string $template, array $orderData)
    {
        $placeholders = OrderService::getPlaceholders($orderData);
        $text = Text::getPrepared($template, $placeholders);
        $this->sendMessage($text);
        $this->saveResponseMessageIdToCommon();
    }

    public function orderAdditional(array $orderData)
    {
        $this->handleMessage('orderAdditional', $orderData);
    }

    public function orderPay(array $orderData)
    {
        $this->handleMessage('orderPay', $orderData);
    }

    public function orderPaid(array $orderData)
    {
        $this->handleMessage('orderPaid', $orderData);
    }

    public function orderCall(array $orderData)
    {
        $this->handleMessage('orderCall', $orderData);
    }

    public function visitorEvaluate(array $orderData)
    {
        $this->handleMessage('visitorEvaluate', $orderData);
    }
}
