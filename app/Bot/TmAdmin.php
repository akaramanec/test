<?php

namespace App\Bot;

use App\Jobs\SendWaiterAssignTableJob;
use App\Models\Bot\Employer;
use App\Models\Bot\Text;
use App\Models\Project\Notification;
use App\Services\Project\InEstablishmentService;
use App\Services\Tabster\TabsterService;

class TmAdmin extends TmEmployer
{
    public function visitorIn(Notification $notification)
    {
        $placeholders = TabsterService::getPlaceholdersFromNotification($notification);
        $text = Text::getPrepared('visitorIn', $placeholders);
        $buttons = [];
        if (isset($notification->data['workers']['waiters'])) {
            $waiters = Employer::whereIn('external_id', array_column($notification->data['workers']['waiters'], 'id'))->get();
            /** @var Employer $waiter */
            foreach ($waiters as $waiter) {
                $buttons[] = [[
                    'text' => $waiter->fullName(),
                    'callback_data' => json_encode([
                        'a' => 'a-ae',
                        'eid' => $waiter->id,
                        'nid' => $notification->id,
                    ]),
                ]];
            }
        }
        $buttons[] = [[
            'text' => Text::getPrepared('assignMyself'),
            'callback_data' => json_encode(['a' => 'a-ae', 'eid' => $this->init->customer->id, 'nid' => $notification->id]),
        ]];

        $this->sendButton($this->prepareText($text), $buttons);
        if (isset($this->response['result']['message_id'])) {
            $this->saveResponseMessageIdToCommon();
            InEstablishmentService::saveMessageId(
                $notification,
                $this->init->customer->id,
                $this->response['result']['message_id']
            );
        }
    }

    public function assignEmployer()
    {
        /** @var Employer $employer */
        if (!isset($this->init->data->eid) || !($employer = Employer::whereId($this->init->data->eid)->first())) {
            $this->unknown();
        }

        /** @var Notification $notification */
        if (!isset($this->init->data->nid) || !($notification = Notification::whereId($this->init->data->nid)->first())) {
            $this->unknown();
        }

        if ($notification->status == Notification::STATUS_ASSIGNED) {
            $placeholders = [];
            if (isset($notification->data['assigned_by']) && isset($notification->data['assigned_employer_id'])) {
                $placeholders = [
                    '{assigned_by}' => $notification->data['assigned_by'],
                    '{assigned_employer_id}' => $notification->data['assigned_employer_id']
                ];
            }
            $this->handleMessage('reserveAlreadyAssigned', $placeholders);
            $this->deleteMessageByMessageId($this->init->messageId);
            exit(__METHOD__);
        }

        $notification->assignByAdmin($this->init->customer, $employer);
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
        $this->handleMessage('visitorReject', $data);
    }

    public function orderCallWithoutWaiter(array $data)
    {
        $placeholders = TabsterService::getPlaceholdersFromData($data);
        $text = Text::getPrepared('orderCallWithoutWaiter', $placeholders);
        $waiters = Employer::whereIn('external_id', array_column($data['workers']['waiters'], 'id'))->get();
        $this->setSessionData('orderCallAssignEmployer', $data);
        /** @var Employer $waiter */
        foreach ($waiters as $waiter) {
            $buttons[] = [[
                'text' => $waiter->fullName(),
                'callback_data' => json_encode([
                    'a' => 'a-ocae',
                    'wid' => $waiter->id
                ]),
            ]];
        }
        $this->sendButton($text, $buttons);
        $this->saveResponseMessageIdToCommon();
    }

    public function orderCallAssignEmployer()
    {
        if (!isset($this->init->data->wid) || !($waiter = Employer::whereId($this->init->data->wid)->first())) {
            $this->unknown();
        }

        if (!$data = $this->getSessionData('orderCallAssignEmployer')) {
            $this->unknown();
        }
        $this->deleteMessageByMessageId($this->init->messageId);
        $this->delSessionData('orderCallAssignEmployer');

        $placeholders['{waiter}'] = $waiter->fullName();
        $this->handleMessage('youAssignedWaiter', $data, $placeholders);

        /** @var TmWaiter $waiterBot */
        $waiterBot = $waiter->getBot();
        $waiterBot->handleMessage('adminAssigned', $data);
    }

    public function preOrder(array $data)
    {
        $placeholders['{food_serving_status}'] = $data['food_serving_status'] ?? '';
        $this->handleMessage('preOrder', $data, $placeholders);
    }

    public function reserve(array $data)
    {
        $this->handleMessage('newReserve', $data);
    }
}
