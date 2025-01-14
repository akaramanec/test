<?php

namespace App\Bot;

use App\Models\Bot\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TmCommon extends TmBase
{
    public string $method;

    public $errors;

    public function start()
    {
        $this->delAll();
        $this->checkAuth();
        $this->sendMessage($this->text('start'));
//        $this->mainMenu();
        $this->deleteMessage();
    }

    public function checkAuth()
    {
        if ($this->init->customer->status === Customer::STATUS_BLACKLIST) {
            $this->sendMessage($this->text('blacklist'));
            exit(__METHOD__ . __LINE__);
        }
        if ($this->init->customer->status != Customer::STATUS_ACTIVE) {
            $this->init->action('phone');
            exit(__METHOD__);
        }
    }

    public function unknown()
    {
        $this->init->session->saveCommonRequest($this->init->messageId);
        $this->sendMessage($this->text('unknownTm'));
        $this->saveResponseMessageIdToCommon();
        exit(__METHOD__.' '.__LINE__);
    }

    public function audio()
    {
        $this->sendMessage($this->text('cantAudio'));
        $this->saveResponseMessageIdToCommon();
    }

    public function video()
    {
        $this->sendMessage($this->text('cantVideo'));
        $this->saveResponseMessageIdToCommon();
    }

    public function photo()
    {
        $this->sendMessage($this->text('cantPhoto'));
        $this->saveResponseMessageIdToCommon();
    }

    public function document()
    {
        $this->sendMessage($this->text('cantDocument'));
        $this->saveResponseMessageIdToCommon();
    }

    public function caption()
    {
        $this->sendMessage($this->text('captionCan'));
        $this->saveResponseMessageIdToCommon();
    }

    public function mainMenu(?string $text = null, $delCommon = true)
    {
        $this->deleteMainMessage();
        if ($delCommon) {
            $this->delCommon();
        }
        $buttons = [
            [['text' => $this->text('analysisBtn')]],
            [['text' => $this->text('clearBtn')]],
            [['text' => $this->text('buyTariffProBtn')]],
        ];
        $mainMenuData = [
            $this->text('buyTariffProBtn') => ['a' => '/buyTariffPro'],
            $this->text('clearBtn') => ['a' => '/start'],
            $this->text('analysisBtn') => ['a' => 'operationsAnalysis'],
        ];
        $this->setMainMenuData($mainMenuData);
        $this->sendMainMenu($this->text('mainMenu'), $buttons);
        if ($text) {
            $button[] = [['text' => $this->text('goBtn'), 'url' => config('app.desktop_webapp_url')]];
            $this->sendButton($text, $button);
            $this->saveResponseMessageIdToCommon();
        }
    }

    public function getMarkSign(mixed $field): string
    {
        return $field ? $this->text('checkMark') : $this->text('crossMark');
    }

    public function getOnOff(mixed $field): string
    {
        return $field ? $this->text('on') : $this->text('off');
    }

    public function sendMainButton($text, $button, $edit = false)
    {
        $messageId = $this->init->session->get('mainMessageId');
        if ($messageId && $edit) {
            $this->buttonEdit($text, $button, $messageId);
        } else {
            $this->sendButton($text, $button);
            $this->saveMainMessageId();
            if ($messageId) {
                $this->deleteMessageByMessageId($messageId);
            }
        }
    }

    public function sendMainMessage($text)
    {
        $messageId = $this->init->session->get('mainMessageId');
        if ($messageId) {
            $this->editMessageText($text, $messageId);
        } else {
            $this->sendMessage($text);
            $this->saveMainMessageId();
        }
    }

    public function sendMainPhoto($text)
    {
        $messageId = $this->init->session->get('mainMessageId');
        if ($messageId) {
            $this->editMessageText($text, $messageId);
        } else {
            $this->sendMessage($text);
            $this->saveMainMessageId();
        }
    }

    public function saveMainMessageId()
    {
        if (isset($this->response['result']['message_id'])) {
            $this->init->session->set('mainMessageId', $this->response['result']['message_id']);
        }
    }

    public function saveMainMenuMessageId()
    {
        if (isset($this->response['result']['message_id'])) {
            $this->init->session->set('mainMenuMessageId', $this->response['result']['message_id']);
        }
    }

    public function delMainMenuMessageId()
    {
        if ($messageId = $this->init->session->get('mainMenuMessageId')) {
            $this->deleteMessageByMessageId($messageId);
            $this->init->session->del('mainMenuMessageId');
        }
    }

    public function sendMainMenu(string $text, array $button)
    {
        $messageId = $this->init->session->get('mainMenuMessageId');
        $this->keyboard($text, $button);
        $this->saveMainMenuMessageId();
        if ($messageId) {
            $this->deleteMessageByMessageId($messageId);
        }
    }

    public function deleteMainMessage()
    {
        $messageId = $this->init->session->get('mainMessageId');
        if ($messageId) {
            $this->init->session->del('mainMessageId');
            $this->deleteMessageByMessageId($messageId);
        }
    }

    public function setMainMenuData($data)
    {
        $this->init->session->set('mainMenuData', $data);
    }

    public function getMainMenuData()
    {
        return $this->init->session->get('mainMenuData');
    }

    public function delMainMenuData()
    {
        $this->init->session->del('mainMenuData');
    }

    public function deleteCommand()
    {
        $this->init->session->del('command');
    }

    public function saveCommand($d)
    {
        $this->init->session->set('command', $d);
    }

    public function delCommon()
    {
        $data = $this->init->session->common();
        if ($data['message_id']) {
            foreach ($data['message_id'] as $messageId) {
                $this->deleteMessageByMessageId($messageId);
            }
        }
        $this->init->session->del('common');
    }

    public function saveResponseMessageIdToCommon(): void
    {
        $this->init->session->saveCommonRequest($this->response);
    }

    public function getResponseMessageId()
    {
        return $this->init->session->getRequestMessageId($this->response);
    }

    public function saveMessageIdToCommon($messageId): void
    {
        $this->init->session->saveCommonMessageId($messageId);
    }

    public function setBackAction(array $action)
    {
        return $this->init->session->set('backAction', $action);
    }

    public function getBackAction()
    {
        return $this->init->session->get('backAction');
    }

    public function none()
    {
        exit(__METHOD__.' '.__LINE__);
    }

    public function getOperation()
    {
        $operation = null;
        if (! isset($this->init->data->oid) || ! $this->init->data->oid || ! $operation = Operation::find($this->init->data->oid)) {
            $this->unknown();
        }

        return $operation;
    }

    public function validate($field)
    {
        switch ($field) {
            case 'phone':
                if ($phone = getFullPhoneFormat($this->init->data->value)) {
                    $this->init->data->value = $phone;
                } else {
                    $this->errors = __('validation.not_regex', ['attribute' => __('validation.attributes.phone')]);
                }
                $rules = [$field => 'required|numeric|regex:/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,12}(\s*)?$/'];
                break;
            case 'name':
                $rules = [$field => 'required|string|max:500'];
                break;
            case 'email':
                $rules = [$field => 'required|email'];
                break;
            case 'type':
            case 'category':
            case 'operationName':
                $rules = [$field => 'required|string|max:255|min:3'];
                break;
            case 'sum':
                $rules = [$field => 'required|numeric|min:0'];
                break;
            case 'currency':
                $rules = [$field => 'required|string|in:UAH,USD,EUR,GBP,PLN,CAD,AUD,CHF,JPY,SEK'];
                $this->init->data->value = Str::upper($this->init->data->value);
                break;
        }
        $validator = Validator::make([$field => $this->init->data->value], $rules);
        if ($validator->passes()) {
            return true;
        }
        $this->errors = $validator->errors()->first($field);
    }

    public function addBackButton(array &$buttons = [], array $callbackData = ['a' => '/menu'], ?string $backButtonText = null): array
    {
        //        if ($backAction = $this->init->session->get('backAction')) {
        //            $this->init->session->del('backAction');
        //            $callbackData = $backAction;
        //        }
        if (! $backButtonText) {
            $backButtonText = $this->text('backBtn');
        }

        return $this->addButton($callbackData, $backButtonText, $buttons);
    }

    public function addButton(array $callbackData, string $buttonText, array &$buttons = []): array
    {
        $buttons[] = [['text' => $buttonText, 'callback_data' => json_encode($callbackData)]];

        return $buttons;
    }

    public function delAll(): void
    {
        $this->deleteMainMessage();
        $this->delCommon();
        $this->deleteCommand();
        $this->delSessionData('operationsData');
        $this->delSessionData('backAction');
        $this->delSessionData('operationsAnalytic');
        if ($messageId = $this->getSessionData('operationsAnalysisMessageId')) {
            $this->deleteMessageByMessageId($messageId);
            $this->delSessionData('operationsAnalysisMessageId');
        }
    }

    public function setSessionData(string $name, mixed $value): void
    {
        $this->init->session->set($name, $value);
    }

    public function getSessionData(string $name): mixed
    {
        return $this->init->session->get($name);
    }

    public function delSessionData(string $name): void
    {
        $this->init->session->del($name);
    }

    public function test()
    {
        $this->getStarTransactions();
        dd($this->response);
    }
}
