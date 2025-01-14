<?php

namespace App\Bot;

use App\Jobs\SendAssignReserveJob;
use App\Models\Bot\Text;
use App\Models\Project\Reservation;
use App\Services\Project\ReservationService;

class TmWaiter extends TmCommon
{
    public function visitorIn(Reservation $reservation)
    {
        $placeholders = ReservationService::getTextPlaceholders($reservation);
        $text = Text::getPrepared('visitorIn', $placeholders);
        $buttons[] = [
            [
                'text' => Text::getPrepared('assignReserveBtn'),
                'callback_data' => json_encode(['a' => 'w-ar', 'rid' => $reservation->id]),
            ],
            [
                'text' => Text::getPrepared('rejectReserveBtn'),
                'callback_data' => json_encode(['a' => 'w-rr', 'rid' => $reservation->id]),
            ]
        ];
        $this->sendButton($this->prepareText($text), $buttons);
        $this->saveResponseMessageIdToCommon();
        ReservationService::updateMessageIds($reservation, $this->init->customer->id, $this->response['result']['message_id']);
    }

    public function assignReserve()
    {
        /** @var Reservation $reservation */
        if (!isset($this->init->data->rid) || !($reservation = Reservation::whereId($this->init->data->rid)->first())) {
            $this->unknown();
            return true;
        }

        $reservation->update(['customer_id' => $this->init->customer->id, 'status' => Reservation::STATUS_ACCEPTED]);
        $reservation->refresh();
        ReservationService::deleteMessages($reservation);
        SendAssignReserveJob::dispatch($reservation);
    }

    public function rejectReserve()
    {
        /** @var Reservation $reservation */
        if (!isset($this->init->data->rid) || !($reservation = Reservation::whereId($this->init->data->rid)->first())) {
            $this->unknown();
            return true;
        }
        ReservationService::deleteMessageForWaiter($reservation, $this);
    }

    public function additionalOrder(Reservation $reservation)
    {

    }
}
