<?php

namespace App\Bot;

use App\Jobs\SendAssignReserveJob;
use App\Models\Bot\Customer;
use App\Models\Bot\Text;
use App\Models\Project\Reservation;
use App\Services\Project\ReservationService;

class TmAdmin extends TmCommon
{
    public function visitorIn(Reservation $reservation)
    {
        if ($this->init->platformId == 7895444089) exit(__METHOD__.' '.__LINE__); // TODO: remove this line after testing

        $placeholders = ReservationService::getTextPlaceholders($reservation);
        $text = Text::getPrepared('visitorIn', $placeholders);
        $buttons = [];
        if ($reservation->establishment->workers) {
            foreach ($reservation->establishment->waiters as $worker) {
                $buttons[] = [[
                    'text' => $worker->fullName(),
                    'callback_data' => json_encode([
                        'a' => 'a-aw',
                        'wid' => $worker->id,
                        'rid' => $reservation->id,
                    ]),
                ]];
            }
        }

        $this->sendButton($this->prepareText($text), $buttons);
        $this->saveResponseMessageIdToCommon();
        ReservationService::updateMessageIds($reservation, $this->init->customer->id, $this->response['result']['message_id']);
    }

    public function assignWaiter()
    {
        /** @var Customer $waiter */
        if (!isset($this->init->data->wid) || !($waiter = Customer::whereId($this->init->data->wid)->first())) {
            $this->unknown();
            return true;
        }

        /** @var Reservation $reservation */
        if (!isset($this->init->data->rid) || !($reservation = Reservation::whereId($this->init->data->rid)->first())) {
            $this->unknown();
            return true;
        }
        $reservation->update(['customer_id' => $waiter->id, 'status' => Reservation::STATUS_ACCEPTED]);
        $reservation->refresh();
        $waiterBot = $waiter->getBot();
        $placeholders = ReservationService::getTextPlaceholders($reservation);
        $waiterBot->sendMessage(Text::getPrepared('adminAssigned', $placeholders));
        $waiterBot->saveResponseMessageIdToCommon();
        ReservationService::deleteMessages($reservation);
    }
}
