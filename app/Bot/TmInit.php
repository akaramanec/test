<?php

namespace App\Bot;

use App\Models\Bot\Customer;
use App\Models\Logger;

/**
 * @property TmSession $session
 * @property Customer $customer
 */
class TmInit
{
    public $input;

    public string $type;

    public string $platformId;

    public string $messageId;

    public string $queryMessageId;

    public string $queryChatId;

    public TmSession $session;

    public Customer $customer;

    public $data;

    public bool $isPremium;

    public function run($input)
    {
        $this->input = $input;
        $this->setType();

        if ($this->type == 'channel') {
            exit(__METHOD__);
        }
        $this->setSession($this->platformId);
        if (($customer = new TmCustomer($this)) && isset($customer->model) && $customer->model) {
            $this->setCustomer($customer->model);
            $this->setData();
            $this->action($this->data->a, $this->data->v ?? null);
        }
    }

    public function checkMessageToken()
    {
        if (isset($this->messageId)) {
            if ($this->messageId == $this->session->get('message_token')) {
                exit(__METHOD__);
            } else {
                $this->session->set('message_token', $this->messageId);
            }
        }
    }

    public function action($action, $actionData = null)
    {
        new TmRoute($action, $actionData);
    }

    public function setPlatformId($platform_id)
    {
        $this->platformId = $platform_id;

        return $this;
    }

    public function setMessageId($message_id)
    {
        $this->messageId = $message_id;

        return $this;
    }

    public function setQueryMessageId($message_id)
    {
        $this->queryMessageId = $message_id;

        return $this;
    }

    public function setQueryChatId($chat_id)
    {
        $this->queryChatId = $chat_id;

        return $this;
    }

    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    public function setSession($platform_id)
    {
        $this->session = new TmSession;
        $this->session->platform_id = (string) $platform_id;

        return $this;
    }

    public function currentCustomer($customer)
    {
        $this->setPlatformId($customer->platform_id);
        $this->setSession($customer->platform_id);
        $this->setCustomer($customer);

        return $this;
    }

    public function setAccount()
    {
        if (count($this->customer->accounts) == 0) {
            $this->customer->setAccountDefaultOptions();
        }
        $this->account = $this->customer->defaultAccount();

        return $this;
    }

    public function setType()
    {
        if (isset($this->input->inline_query)) {
            $this->setPlatformId($this->input->inline_query->from->id);
            $this->isPremium = isset($this->input->inline_query->from->is_premium) && $this->input->inline_query->from->is_premium;
            $this->setQueryMessageId($this->input->inline_query->id);

            return $this->type = 'inline_query';
        }
        if (isset($this->input->chosen_inline_result)) {
            $this->setPlatformId($this->input->chosen_inline_result->from->id);
            $this->isPremium = isset($this->input->chosen_inline_result->from->is_premium) && $this->input->chosen_inline_result->from->is_premium;
            $this->setQueryMessageId($this->input->chosen_inline_result->result_id);

            return $this->type = 'chosen_inline_result';
        }
        if (isset($this->input->callback_query)) {
            $this->setPlatformId($this->input->callback_query->from->id);
            $this->isPremium = isset($this->input->callback_query->from->is_premium) && $this->input->callback_query->from->is_premium;
            if (isset($this->input->callback_query->message)) {
                $this->setMessageId($this->input->callback_query->message->message_id);
            }
            if (isset($this->input->callback_query->inline_message_id)) {
                $this->setQueryMessageId($this->input->callback_query->inline_message_id);
            }
            if (isset($this->input->callback_query->chat_instance)) {
                $this->setQueryChatId($this->input->callback_query->chat_instance);
            }

            return $this->type = 'callback_query';
        }
        if (isset($this->input->message->contact)) {
            $this->setPlatformId($this->input->message->chat->id);
            $this->isPremium = isset($this->input->message->from->is_premium) && $this->input->message->from->is_premium;
            $this->setMessageId($this->input->message->message_id);

            return $this->type = 'contact';
        }
        if (isset($this->input->message->photo)) {
            $this->setPlatformId($this->input->message->chat->id);
            $this->isPremium = isset($this->input->message->from->is_premium) && $this->input->message->from->is_premium;
            $this->setMessageId($this->input->message->message_id);

            return $this->type = 'photo';
        }
        if (isset($this->input->message->voice)) {
            $this->setPlatformId($this->input->message->chat->id);
            $this->isPremium = isset($this->input->message->from->is_premium) && $this->input->message->from->is_premium;
            $this->setMessageId($this->input->message->message_id);

            return $this->type = 'voice';
        }
        if (isset($this->input->message->document)) {
            $this->setPlatformId($this->input->message->chat->id);
            $this->isPremium = isset($this->input->message->from->is_premium) && $this->input->message->from->is_premium;
            $this->setMessageId($this->input->message->message_id);
            if (str_contains($this->input->message->document->mime_type, 'audio')) {
                $this->input->message->audio = $this->input->message->document;

                return $this->type = 'audio';
            }
            if (str_contains($this->input->message->document->mime_type, 'video')) {
                $this->input->message->video = $this->input->message->document;

                return $this->type = 'video';
            }

            return $this->type = 'document';
        }
        if (isset($this->input->message->audio)) {
            $this->setPlatformId($this->input->message->chat->id);
            $this->isPremium = isset($this->input->message->from->is_premium) && $this->input->message->from->is_premium;
            $this->setMessageId($this->input->message->message_id);

            return $this->type = 'audio';
        }
        if (isset($this->input->message->video)) {
            $this->setPlatformId($this->input->message->chat->id);
            $this->isPremium = isset($this->input->message->from->is_premium) && $this->input->message->from->is_premium;
            $this->setMessageId($this->input->message->message_id);

            return $this->type = 'video';
        }
        if (isset($this->input->message->entities[0])) {
            $this->setPlatformId($this->input->message->chat->id);
            $this->isPremium = isset($this->input->message->from->is_premium) && $this->input->message->from->is_premium;
            $this->setMessageId($this->input->message->message_id);
            if ($this->input->message->entities[0]->type == 'bot_command') {
                return $this->type = 'bot_command';
            } else {
                return $this->type = 'message';
            }
        }

        if (isset($this->input->message)) {
            if (isset($this->input->message->chat->id)) {
                $this->setPlatformId($this->input->message->chat->id);
            }
            $this->isPremium = isset($this->input->message->from->is_premium) && $this->input->message->from->is_premium;
            if (isset($this->input->message->message_id)) {
                $this->setMessageId($this->input->message->message_id);
            }

            if (isset($this->input->message->successful_payment) && isset($this->input->message->successful_payment->invoice_payload)) {
                return $this->type = 'successful_payment';
            }

            return $this->type = 'message';
        }
        if (isset($this->input->edited_message)) {
            $this->setPlatformId($this->input->edited_message->chat->id);
            $this->isPremium = isset($this->input->edited_message->from->is_premium) && $this->input->edited_message->from->is_premium;
            $this->setMessageId($this->input->edited_message->message_id);

            return $this->type = 'edited_message';
        }
        if (isset($this->input->my_chat_member->new_chat_member->status) && $this->input->my_chat_member->new_chat_member->status == 'member') {
            if (isset($this->input->my_chat_member->chat->type)
                && ! $this->input->my_chat_member->chat->type == 'supergroup') {
                $this->setPlatformId($this->input->my_chat_member->chat->id);
            }
            exit(__METHOD__.' '.__LINE__);
        }

        if (isset($this->input->my_chat_member->new_chat_member->status) && $this->input->my_chat_member->new_chat_member->status == 'kicked') {
            $this->setPlatformId($this->input->my_chat_member->chat->id);

            return $this->type = 'kicked';
        }

        if (isset($this->input->channel_post)) {
            $this->setPlatformId($this->input->channel_post->chat->id);

            return $this->type = 'channel';
        }
        if (isset($this->input->my_chat_member) && $this->isGroup($this->input->my_chat_member->chat->id)) {
            $this->setPlatformId($this->input->my_chat_member->chat->id);

            return $this->type = 'channel';
        }

        if (isset($this->input->pre_checkout_query) && isset($this->input->pre_checkout_query->invoice_payload)) {
            $this->setPlatformId($this->input->pre_checkout_query->from->id);

            return $this->type = 'pre_checkout_query';
        }

        if (! isset($this->type) || ! $this->type) {
            Logger::commit($this, 'Can`t init tg');
            exit();
        }
    }

    public function setData()
    {
        switch ($this->type) {
            case 'message':
                $action = null;
                $value = null;
                $bot = new TmCommon;
                if (isset($this->input->message->via_bot)) {
                    $bot->init->session->saveCommonMessageId($this->messageId);
                    exit(__METHOD__.' '.__LINE__);
                }
                if (isset($this->input->message->text)) {
                    if ($mainMenuData = $this->session->get('mainMenuData')) {
                        if (isset($mainMenuData[$this->input->message->text])) {
                            $this->data = json_decode(json_encode($mainMenuData[$this->input->message->text]));
                            $bot->deleteMessage();
                            break;
                        }
                    }

                    if ($command = $this->session->get('command')) {
                        $d = $command + ['value' => trim($this->input->message->text)];
                        $this->data = json_decode(json_encode($d));
                        break;
                    }
                    if ($this->session->get('fileFromUrlToTextConvert') && filter_var($this->input->message->text, FILTER_VALIDATE_URL)) {
                        $d = [
                            'a' => 'file_from_url_to_text_convert',
                            'value' => $this->input->message->text,
                        ];
                        $this->data = json_decode(json_encode($d));
                        break;
                    }
                    $value = trim($this->input->message->text);
                }
                if (! $action) {
                    $action = 'o-p';
                    $this->session->saveCommonMessageId($this->messageId);
                }
                $d = [
                    'a' => $action,
                    'value' => $value,
                ];
                $this->data = json_decode(json_encode($d));
                break;
            case 'callback_query':
                if (isset($this->input->callback_query->data)) {
                    $this->data = json_decode($this->input->callback_query->data);
                }
                break;
            case 'inline_query':
                if ($this->input->inline_query->query != '') {
                    $d = [
                        'a' => 'inline-query-search',
                        'value' => $this->input->inline_query->query,
                    ];
                    $this->data = json_decode(json_encode($d));
                    break;
                }
                exit(__METHOD__.' '.__LINE__);
            case 'chosen_inline_result':
                exit(__METHOD__.' '.__LINE__);
            case 'pre_checkout_query':
                if ($this->input->pre_checkout_query->invoice_payload) {
                    $d = [
                        'a' => 'or-preCheckout',
                        'pid' => explode('_', $this->input->pre_checkout_query->invoice_payload)[0],
                    ];
                    $this->data = json_decode(json_encode($d));
                    break;
                }
            case 'successful_payment':
                if ($this->input->message->successful_payment->invoice_payload) {
                    $d = [
                        'a' => 'or-paymentSuccessful',
                        'pid' => explode('_', $this->input->message->successful_payment->invoice_payload)[0],
                    ];
                    $this->data = json_decode(json_encode($d));
                    break;
                }
            case 'bot_command':
                $d = ['a' => '/unknown'];
                if (isset($this->input->message->text)) {
                    $command = explode('@', $this->input->message->text);
                    $tmUri = explode('/', config('app.chat_tm'));
                    $tmName = array_pop($tmUri);
                    if (isset($command[1]) && $command[1] != $tmName) {
                        exit('Command for another bot');
                    }
                    $d = ['a' => trim($command[0])];
                }
                $this->data = json_decode(json_encode($d));
                break;
            case 'photo':
                $d = [
                    'a' => 'fileToText',
                ];
                $this->data = json_decode(json_encode($d));
                break;
            case 'audio':
                $d = [
                    'a' => 'fileToText',
                    'value' => $this->input->message->audio,
                ];
                if (isset($this->input->message->caption) && $this->input->message->caption) {
                    $d['caption'] = $this->input->message->caption;
                }
                $this->data = json_decode(json_encode($d));
                break;
            case 'video':
                $this->fileProcessing('video');
                break;
            case 'document':
                $this->fileProcessing('document');
                break;
            case 'contact':
                $d = [
                    'a' => 'phone-save',
                    'value' => onlyInt($this->input->message->contact->phone_number),
                ];
                $this->data = json_decode(json_encode($d));
                break;
            case 'kicked':
                $this->data = json_decode(json_encode(['a' => 'unsubscribed']));
                break;
            case 'voice':
                $d = [
                    'a' => 'fileToText',
                    'value' => $this->input->message->voice,
                ];
                if (isset($this->input->message->caption) && $this->input->message->caption) {
                    $d['caption'] = $this->input->message->caption;
                }
                $this->data = json_decode(json_encode($d));
                break;
            default:
                $this->data = json_decode(json_encode(['a' => '/unknown']));
        }
    }

    private function isGroup($platform = null): bool
    {
        if ($platform) {
            return str_contains($platform, '-');
        }

        return str_contains($this->platformId, '-');
    }

    public function fileProcessing(string $type): void
    {
        $bot = new TmAdmin;
        $bot->{$type}();
        if (isset($this->input->message->caption) && $this->input->message->caption) {
            $bot->caption();
            $d = [
                'a' => 'action',
                'value' => $this->input->message->caption,
            ];
            $this->data = json_decode(json_encode($d));
        } else {
            $this->data = json_decode(json_encode(['a' => '/unknown']));
        }
    }
}
