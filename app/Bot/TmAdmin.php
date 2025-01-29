<?php

namespace App\Bot;

use App\Jobs\SendAssignReserveJob;
use App\Jobs\SendWaiterAssignTableJob;
use App\Models\Bot\Customer;
use App\Models\Bot\Text;
use App\Models\Logger;
use App\Models\Project\Notification;
use App\Services\Project\InEstablishmentService;
use App\Services\Tabster\TabsterService;

class TmAdmin extends TmCommon
{
    public function visitorIn(Notification $notification)
    {
        $placeholders = TabsterService::getPlaceholdersFromNotification($notification);
        $text = Text::getPrepared('visitorIn', $placeholders);
        $buttons = [];
        $waiters = Customer::whereIn('external_id', array_column($notification->data['workers']['waiters'], 'id'))->get();
        /** @var Customer $waiter */
        foreach ($waiters as $waiter) {
            $buttons[] = [[
                'text' => $waiter->fullName(),
                'callback_data' => json_encode([
                    'a' => 'a-aw',
                    'wid' => $waiter->id,
                    'nid' => $notification->id,
                ]),
            ]];
        }

        $this->sendButton($this->prepareText($text), $buttons);
        $this->saveResponseMessageIdToCommon();
        Logger::commit([$this->init->customer->id, $this->response['result']['message_id']], __METHOD__);
        InEstablishmentService::saveMessageId($notification, $this->init->customer->id, $this->response['result']['message_id']);
    }

    public function assignWaiter()
    {
        /** @var Customer $waiter */
        if (!isset($this->init->data->wid) || !($waiter = Customer::whereId($this->init->data->wid)->first())) {
            $this->unknown();
        }

        /** @var Notification $notification */
        if (!isset($this->init->data->nid) || !($notification = Notification::whereId($this->init->data->nid)->first())) {
            $this->unknown();
        }

        $placeholders = TabsterService::getPlaceholdersFromNotification($notification);
        $waiterBot = $waiter->getBot();
        $waiterBot->sendMessage(Text::getPrepared('adminAssigned', $placeholders));
        $waiterBot->saveResponseMessageIdToCommon();
        InEstablishmentService::deleteMessages($notification);
        $notification->addData(['assigned_by' => Customer::ROLE_ADMIN, 'assigned_waiter_id' => $waiter->external_id]);
        SendWaiterAssignTableJob::dispatch($notification);
    }

    public function visitorLate(array $data)
    {
        $placeholders = TabsterService::getPlaceholdersFromData($data);
        $text = Text::getPrepared('visitorLate', $placeholders);
        $this->sendMessage($text);
    }

    public function visitorReject(array $data)
    {
        $placeholders = TabsterService::getPlaceholdersFromData($data);
        $text = Text::getPrepared('visitorReject', $placeholders);
        $this->sendMessage($text);
    }
}
