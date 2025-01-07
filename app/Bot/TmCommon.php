<?php

namespace App\Bot;

use App\Models\Admin\Setting;
use App\Models\Bot\Customer;
use App\Models\Bot\StartMessage;
use App\Models\Bot\TranslateLanguage;
use App\Models\Project\Event;
use App\Models\Project\Operation;
use App\Models\Project\Tariff;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TmCommon extends TmBase
{
    public string $method;

    public $errors;

    public function start()
    {
        $this->delAll();
        if (! $this->init->customer->language) {
            $this->chooseLanguage();
        } else {
            $text = null;
            $this->init->setAccount();
            if ($this->getSessionData('newCustomer')) {
                $this->delSessionData('newCustomer');
                $this->sendStartMessage();
            }
            if ($this->getSessionData('webLogin')) {
                $this->delSessionData('webLogin');
                $text = $this->text('webLogin');
            }
            $this->mainMenu($text, false);
            $this->deleteMessage();
        }
    }

    public function buy()
    {
        $digitalGodsData = $this->getDigitalGodsData([['amount' => 1, 'label' => 'label']], 'Title', 'Description', 'wqerewrwerw');
        $photoData = $this->getPhotoData('https://fin.boto.kyiv.ua/storage/cache/newsletter/3/400x400m1720472828.png');
        $this->sendStarsInvoice($digitalGodsData, $photoData);
    }

    public function buyTariffPro()
    {
        if (($proButtonPushTime = $this->getSessionData('buyTariffProButtonPushTime')) && now()->subMinute()->timestamp < $proButtonPushTime) {
            exit(__METHOD__.' 1 minute send limit');
        }
        $this->setSessionData('buyTariffProButtonPushTime', now()->timestamp);
        $tariff = Tariff::getPro();
        $percent = (1 - ($tariff->cost_year / ($tariff->cost_month * 12))) * 100;
        $placeholders = [
            '{cost_year}' => $tariff->cost_year,
            '{cost_month}' => $tariff->cost_month,
            '{percent}' => round($percent),
            '{saving}' => round($tariff->cost_year / 12, 2),
        ];
        $description = $this->text('proTariffDescription');
        $cost = $this->text('proTariffCostDescription', $placeholders);
        $text = '<strong>'.Tariff::getPro()->getName($this->init->customer->language).'</strong>'.PHP_EOL.PHP_EOL.$description.PHP_EOL.PHP_EOL.$cost;
        $buttons = [
            [
                ['text' => $this->text('buyMonthBtn'), 'callback_data' => json_encode(['a' => 'or-btpm'])],
            ],
            [
                ['text' => $this->text('buyYearBtn'), 'callback_data' => json_encode(['a' => 'or-btpy'])],
            ],
        ];
        if ($tariff->img) {
            $this->sendPhoto('', $tariff->mainImgUrl());
            $this->saveResponseMessageIdToCommon();
        }

        $this->sendButton($text, $buttons);
        $this->saveResponseMessageIdToCommon();
    }

    public function sendStartMessage()
    {
        /** @var StartMessage $startMessage */
        $messages = StartMessage::whereStatus(StartMessage::STATUS_ACTIVE)->orderBy('sort')->get();
        foreach ($messages as $startMessage) {
            if ($messages->first() != $startMessage) {
                $this->sendChatAction($startMessage->timeout);
            }
            $text = $startMessage->{$this->init->customer->language};
            if ($startMessage->img) {
                $this->sendPhoto($this->prepareText($text), $startMessage->mainImgUrl($this->init->customer->language));
            } else {
                $this->sendMessage($this->prepareText($text));
            }
            $this->saveResponseMessageIdToCommon();
            if ($startMessage->audio) {
                $this->sendVoice($startMessage->mainAudioUrl($this->init->customer->language));
            }
            $this->saveResponseMessageIdToCommon();
        }
    }

    public function startMessage()
    {
        if ($startMessage = StartMessage::first()) {
            $language = $this->init->customer->language ?? Setting::getGeneralItem('defaultLanguage');
            $text = $this->prepareText($startMessage->{$language});
            if ($startMessage->img) {
                $entity = START_MESSAGE;
                $img = config('app.domain_open')."/storage/{$entity}/".$startMessage->id.'/'.$startMessage->img;
                $this->sendPhoto($text, $img);
            } else {
                $this->sendMessage($this->prepareText($text));
            }
            $this->saveResponseMessageIdToCommon();
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
        $this->chatMenu();
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

    public function chatMenu(?string $text = null)
    {
        $text = $text ?? $this->text('cabinetEnterChatMenuBtn');
        $this->sendChatMenuWebappButton($text);
    }

    public function operationsAnalysis()
    {
        $this->setSessionData('operationsAnalytic', true);
        $this->sendMessage($this->text('operationsAnalysis'));
        $message = $this->getSessionData('operationsAnalysisMessageId');
        $this->setSessionData('operationsAnalysisMessageId', $this->getResponseMessageId());
        if ($message) {
            $this->deleteMessageByMessageId($message);
        }
    }

    public function profile()
    {
        $this->profileMenu();
    }

    public function profileMenu(?string $text = null)
    {
        $this->checkProfile();

        $orthography = $this->init->customer->profile->orthography;
        $orthographyBtn = $this->text('orthographyBtn').' '.$this->getMarkSign($orthography);

        $emoji = $this->init->customer->profile->emoji;
        $emojiBtn = $this->text('emojiBtn').' '.$this->getMarkSign($emoji);

        $translate = $this->init->customer->profile->translate;
        $translateBtn = $this->text('translateBtn').' '.$this->getMarkSign($translate);

        $language = $this->init->customer->profile->language;
        $languageName = $language->{$this->init->customer->language ?? 'uk'};
        $translateLanguageBtn = $this->text('translateLanguageBtn').' '.$languageName;

        $placeholders = [
            '{orthography}' => $this->getOnOff($orthography),
            '{emoji}' => $this->getOnOff($emoji),
            '{translate}' => $this->getOnOff($translate),
            '{language}' => $languageName,
        ];
        $text = $text ?? $this->text('templateSettings', $placeholders);

        $buttons = [
            [['text' => $orthographyBtn]],
            [['text' => $emojiBtn]],
            [['text' => $translateBtn], ['text' => $translateLanguageBtn]],
            [['text' => $this->text('backBtn')]],
        ];

        $mainMenuData = [
            $orthographyBtn => ['a' => 'templateSetting', 'type' => 'orthography', 'value' => ! $orthography],
            $emojiBtn => ['a' => 'templateSetting', 'type' => 'emoji', 'value' => ! $emoji],
            $translateBtn => ['a' => 'templateSetting', 'type' => 'translate', 'value' => ! $translate],
            $translateLanguageBtn => ['a' => 'translateLanguage'],
            $this->text('backBtn') => ['a' => '/start'],
        ];

        $this->setMainMenuData($mainMenuData);
        $this->sendMainMenu($text, $buttons);
    }

    public function operationsSummery()
    {
        $this->init->session->set('operationsSummery', true);
        $this->sendMessage($this->text('operationsSummeryIntro'));
    }

    public function getMarkSign(mixed $field): string
    {
        return $field ? $this->text('checkMark') : $this->text('crossMark');
    }

    public function getOnOff(mixed $field): string
    {
        return $field ? $this->text('on') : $this->text('off');
    }

    public function chooseLanguageMenu(?string $text = null)
    {
        $text = $text ?? $this->text('chooseHelloText');
        $buttons = [];
        foreach (array_keys(Customer::languages()) as $language) {
            $buttons[] = [['text' => $this->text($language.'ChooseBtn'), 'callback_data' => json_encode(['a' => 'setLanguage', 'language' => $language])]];
        }
        $this->sendButton($text, $buttons);
    }

    public function translateLanguageMenu(?string $text = null)
    {
        $text = $text ?? $this->text('translateLanguage');
        $mainMenuData = [];
        $buttons = [];
        foreach (TranslateLanguage::all() as $language) {
            $mainMenuData[$language->{$this->init->customer->language ?? 'uk'}] = ['a' => 'setTranslateLanguage', 'language' => $language->iso_639_1];
            $buttons[] = [['text' => $language->{$this->init->customer->language ?? 'uk'}]];
        }
        $this->setMainMenuData($mainMenuData);
        $this->sendMainMenu($text, $buttons);
    }

    public function setLanguage()
    {
        if (! $this->init->data->language || ! array_key_exists($this->init->data->language, Customer::languages())) {
            $this->unknown();
        }
        $this->init->customer->update(['language' => $this->init->data->language]);
        $this->init->customer->refresh();
        $this->start();
    }

    public function setTranslateLanguage()
    {
        if (! $this->init->data->language || ! ($translateLanguage = TranslateLanguage::where('iso_639_1', $this->init->data->language)->first())) {
            $this->unknown();
        }
        $this->checkProfile();
        $this->init->customer->profile->update(['language_id' => $translateLanguage->id]);
        $this->init->customer->profile->refresh();
        $this->profile();
    }

    public function chooseLanguage()
    {
        $this->deleteMainMessage();
        $this->chooseLanguageMenu();
    }

    public function templateSetting()
    {
        if ((! isset($this->init->data->type) || ! $this->init->data->type)) {
            $this->unknown();
        }
        if (! isset($this->init->data->value)) {
            $this->unknown();
        }
        $this->checkProfile();
        $this->init->customer->profile->update([$this->init->data->type => $this->init->data->value]);
        $this->init->customer->profile->refresh();
        $this->profile();
    }

    public function translateLanguage()
    {
        $this->delAll();
        $this->deleteMainMessage();
        $this->translateLanguageMenu();
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

    public function sendBotInviteMessage()
    {
        $placeholder = [
            '{name}' => $this->init->customer->actualPayment()->tariff->name,
            '{valid_to}' => $this->init->customer->actualPayment()->valid_to,
        ];
        $text = $this->text('paidTariff', $placeholder);
        $button[] = [['text' => $this->text('paidTariffButton'), 'url' => Setting::getGeneralItem('communityLink')]];
        if ($messageId = $this->init->session->get('botInviteMessageId')) {
            $response = $this->buttonEdit($text, $button, $messageId);
            $this->saveBotInviteMessage($response);
        } else {
            $this->sendButton($text, $button);
            $this->saveBotInviteMessage();
        }
    }

    public function saveBotInviteMessage($response = null)
    {
        if ($response && isset($response['result']['message_id'])) {
            return $this->init->session->set('botInviteMessageId', $response['result']['message_id']);
        }
        if (isset($this->response['result']['message_id'])) {
            $this->init->session->set('botInviteMessageId', $this->response['result']['message_id']);
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

    public function help()
    {
        $this->init->action('f-lqs');
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

    public function setBusinessDataMessageId()
    {
        if (isset($this->response['ok']) && $this->response['ok'] === true && isset($this->response['result']['message_id'])) {
            $this->init->session->set('businessDataMessageId', $this->response['result']['message_id']);
        }
    }

    public function editBusinessMessage(string $text): void
    {
        if ($messageId = $this->getBusinessDataMessageId()) {
            $this->editMessageText($text, $messageId);
        } else {
            $this->sendMessage($text);
            $this->setBusinessDataMessageId();
        }
    }

    public function editBusinessMessageButton(string $text, array $buttons): void
    {
        if ($messageId = $this->getBusinessDataMessageId()) {
            $this->buttonEdit($text, $buttons, $messageId);
        } else {
            $this->sendButton($text, $buttons);
            $this->setBusinessDataMessageId();
        }
    }

    public function getBusinessDataMessageId()
    {
        return $this->init->session->get('businessDataMessageId');
    }

    public function delBusinessDataMessage()
    {
        if ($messageId = $this->init->session->get('businessDataMessageId')) {
            $this->deleteMessageByMessageId($messageId);
            $this->init->session->del('businessDataMessageId');
        }
    }

    public function getEvent(): ?Event
    {
        $eventId = $this->init->session->get('event');
        if (! $eventId) {
            $this->init->session->del('event');
        }
        if (isset($this->init->data->eid)) {
            $eventId = $this->init->data->eid;
        }

        return ! $eventId ? null : Event::find($eventId);
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

    public function getConfirmEditButtons(string $saveRoute, string $editRoute, array $additionalData = []): array
    {
        return [
            [
                'text' => $this->text('eventBusinessDataEditButton'), 'callback_data' => json_encode(array_merge(['a' => $editRoute], $additionalData)),
            ],
            [
                'text' => $this->text('eventBusinessDataConfirmButton'), 'callback_data' => json_encode(array_merge(['a' => $saveRoute], $additionalData)),
            ],
        ];
    }

    public function getYesNoButtons(string $yesRoute, string $noRoute, array $additionalData = []): array
    {
        return [
            [
                'text' => $this->text('yesBtn'), 'callback_data' => json_encode(array_merge(['a' => $yesRoute], $additionalData)),
            ],
            [
                'text' => $this->text('noBtn'), 'callback_data' => json_encode(array_merge(['a' => $noRoute], $additionalData)),
            ],
        ];
    }

    public function getEditButton(string $type): array
    {
        return [
            [['text' => $this->text('editBtn'), 'callback_data' => json_encode(array_merge(['a' => $type.'_edit']))]],
            [['text' => $this->text('deleteBtn'), 'callback_data' => json_encode(array_merge(['a' => 'delete']))]],
        ];
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
