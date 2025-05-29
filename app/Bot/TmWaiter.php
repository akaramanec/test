<?php

namespace App\Bot;

use App\Jobs\SendWaiterAssignTableJob;
use App\Models\Bot\Employer;
use App\Models\Bot\Text;
use App\Models\Logger;
use App\Models\Project\Notification;
use App\Services\Project\InEstablishmentService;
use App\Services\Tabster\TabsterService;

class TmWaiter extends TmEmployer
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

        if (isset($notification->data['waiter_id'])) {
            $this->sendMessage($this->prepareText($text));
        } else {
            $this->sendButton($this->prepareText($text), $buttons);
        }

        if (isset($this->response['result']['message_id'])) {
            $this->saveResponseMessageIdToCommon();
            InEstablishmentService::saveMessageId(
                $notification,
                $this->init->customer->id,
                $this->response['result']['message_id']
            );
        }
    }

    public function assignReserve()
    {
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
            $this->sendMessage(Text::getPrepared('reserveAlreadyAssigned', $placeholders));
            $this->saveResponseMessageIdToCommon();
            exit(__METHOD__);
        }

        $notification->assignByWaiter($this->init->customer);
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
}
