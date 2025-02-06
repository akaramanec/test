<?php

namespace App\Bot;

use App\Models\Bot\Customer;
use App\Models\Logger;
use App\Services\Tabster\TabsterService;

class TmAuth extends TmCommon
{
    public function phone($text = null)
    {
        $text = $text ?? $this->text('yourPhoneText');
        $button[] = [['text' => $this->text('yourPhoneButton'), 'request_contact' => true]];
        $this->contact($text, $button);
        $this->init->session->saveCommonRequest($this->response);
        exit(__METHOD__.' '.__LINE__);
    }

    public function phoneSave()
    {
        $this->init->session->saveCommonMessageId($this->init->messageId);
        if ($this->validate('phone') && $customerData = TabsterService::getWorker($this->init->data->value)) {
            if (!is_array($customerData) || !isset($customerData['role'])) {
                $this->phone($this->text('serverPhoneError'));
                Logger::commit(['serverPhoneError' => $customerData], __METHOD__);
                exit(__METHOD__.' '.__LINE__);
            }
            $this->init->customer->phone = $customerData['phone'] ?? $this->init->data->value;
            $this->init->customer->status = Customer::STATUS_ACTIVE;
            $this->init->customer->role = $customerData['role'];
            if (isset($customerData['name'])) {
                $this->init->customer->name = $customerData['name'];
            }
            $this->init->customer->save();
            $this->deleteCommand();
            $this->delCommon();
            $this->start();
        } else {
            $this->phone($this->errors);
            $this->init->session->saveCommonRequest($this->response);
        }
    }

    public function unsubscribed()
    {
        if (isset($this->init->customer)) {
            $this->init->customer->status = Customer::STATUS_UNSUBSCRIBED;

            return $this->init->customer->save();
        }

        return true;
    }
}
