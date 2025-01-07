<?php

namespace App\Bot;

use App\Models\Logger;
use App\Models\Text;

/**
 * @property $init \App\Bot\Telegram\TmInit
 */
class TmBase
{
    public $api;

    public TmInit $init;

    public $bot;

    public $response;

    public function __construct()
    {
        $this->init = app(TmInit::class);
    }

    public function sendMessage($text, $mode = 'HTML')
    {
        $this->send('sendMessage', [
            'chat_id' => $this->init->platformId,
            'text' => $text,
            'parse_mode' => $mode,
        ]);
    }

    public function getDigitalGodsData(array $prices, string $title, string $description, string $payload)
    {
        return [
            'prices' => json_encode($prices),
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
        ];
    }

    public function getPhotoData(string $url, ?int $photoSize = null, ?int $photoWidth = null, ?int $photoHeight = null)
    {
        return [
            'photo_url' => $url,
            'photo_size' => $photoSize,
            'photo_width' => $photoWidth,
            'photo_height' => $photoHeight,
        ];
    }

    public function sendInvoice(string $currency, array $digitalGodsData, array $photoData)
    {
        $this->send('sendInvoice', [
            'chat_id' => $this->init->platformId,
            'title' => $digitalGodsData['title'],
            'description' => $digitalGodsData['description'],
            'payload' => $digitalGodsData['payload'],
            'prices' => $digitalGodsData['prices'],
            'currency' => $currency,
            'photo_url' => $photoData['photo_url'],
            'photo_size' => $photoData['photo_size'],
            'photo_width' => $photoData['photo_width'],
            'photo_height' => $photoData['photo_height'],
        ]);
    }

    public function sendSubscription(string $currency, ?int $seconds, array $digitalGodsData, array $photoData)
    {
        if (! $seconds) {
            $seconds = 2592000; // 30 days
        }

        return $this->send('createInvoiceLink', [
            'title' => $digitalGodsData['title'],
            'description' => $digitalGodsData['description'],
            'payload' => $digitalGodsData['payload'],
            'prices' => $digitalGodsData['prices'],
            'subscription_period' => $seconds,
            'currency' => $currency,
            'photo_url' => $photoData['photo_url'],
            'photo_size' => $photoData['photo_size'],
            'photo_width' => $photoData['photo_width'],
            'photo_height' => $photoData['photo_height'],
        ]);
    }

    public function answerPreCheckoutQuery(string $preCheckoutQueryId, $ok = true, $errorMessage = '')
    {
        $data = [
            'pre_checkout_query_id' => $preCheckoutQueryId,
            'ok' => $ok,
        ];
        if (! $ok && $errorMessage) {
            $data['error_message'] = $errorMessage;
        }
        $this->send('answerPreCheckoutQuery', $data);
    }

    public function sendStarsInvoice(array $digitalGodsData, array $photoData)
    {
        $this->sendInvoice('XTR', $digitalGodsData, $photoData);
    }

    public function sendMessageToChannel($channel, $text, $preview = true)
    {
        $data = [
            'chat_id' => $channel,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
        if (! $preview) {
            $data['disable_web_page_preview'] = true;
        }
        $this->send('sendMessage', $data);
    }

    public function editMessageText($text, $messageId)
    {
        $this->send('editMessageText', [
            'chat_id' => $this->init->platformId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
        if ($this->checkEdit()) {
            $this->sendMessage($text);
        }
    }

    public function sendButton($text, $button, $oneTimeKeyboard = false)
    {
        return $this->send('sendMessage', [
            'chat_id' => $this->init->platformId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'one_time_keyboard' => $oneTimeKeyboard,
                'inline_keyboard' => $button,
            ]),
        ]);
    }

    public function buttonEdit($text, $button, $messageId)
    {
        $this->send('editMessageText', [
            'chat_id' => $this->init->platformId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'one_time_keyboard' => false,
                'inline_keyboard' => $button,
            ]),
        ]);
        if ($this->checkEdit()) {
            return $this->sendButton($text, $button);
        }
    }

    protected function checkEdit()
    {
        if (isset($this->response['ok']) && $this->response['ok'] == false && isset($this->response['error_code']) && $this->response['error_code'] == 400) {
            if ($this->response['description'] == 'Bad Request: message is not modified: specified new message content and reply markup are exactly the same as a current content and reply markup of the message') {
                exit(__METHOD__.' '.__LINE__);
            }

            return true;
        }
    }

    public function keyboard($text, $button)
    {
        $this->send('sendMessage', [
            'chat_id' => $this->init->platformId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'keyboard' => $button,
                'one_time_keyboard' => false,
                'resize_keyboard' => true,
            ]),
        ]);
    }

    public function contact($text, $button)
    {
        $this->send('sendMessage', [
            'chat_id' => $this->init->platformId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'keyboard' => $button,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
            ]),
        ]);
    }

    public function sendVoice($voice, $text = null)
    {
        $data = [
            'chat_id' => $this->init->platformId,
            'voice' => $voice,
        ];
        if ($text) {
            $data['caption'] = $text;
        }
        $this->send('sendVoice', $data);
    }

    public function sendPhoto($text, $photo)
    {
        $this->send('sendPhoto', [
            'chat_id' => $this->init->platformId,
            'photo' => $photo,
            'caption' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    public function deleteMessage()
    {
        if (! isset($this->init->messageId)) {
            return;
        }
        $this->send('deleteMessage', [
            'chat_id' => $this->init->platformId,
            'message_id' => $this->init->messageId,
        ]);
    }

    public function deleteMessageByMessageId($messageId)
    {
        $this->send('deleteMessage', [
            'chat_id' => $this->init->platformId,
            'message_id' => $messageId,
        ]);
    }

    public function sendDocumentKeyboard($fileUrl, $keyboard, $caption = '')
    {
        $this->send('sendDocument', [
            'chat_id' => $this->init->platformId,
            'document' => $fileUrl,
            'caption' => $caption,
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'one_time_keyboard' => false,
                'resize_keyboard' => true,
            ]),
        ]);
    }

    public function sendDocument($fileUrl, $caption = '')
    {
        $this->send('sendDocument', [
            'chat_id' => $this->init->platformId,
            'document' => $fileUrl,
            'caption' => $caption,
        ]);
    }

    public function keyboardDelete($text)
    {
        $this->send('sendMessage', [
            'chat_id' => $this->init->platformId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
            ]),
        ]);
    }

    public function sendPhotoButton($text, $photo, $button)
    {
        $this->send('sendPhoto', [
            'chat_id' => $this->init->platformId,
            'photo' => $photo,
            'caption' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'one_time_keyboard' => false,
                'inline_keyboard' => $button,
            ]),
        ]);
    }

    public function sendPhotoKeyboard($text, $photo, $keyboard)
    {
        $this->send('sendPhoto', [
            'chat_id' => $this->init->platformId,
            'photo' => $photo,
            'caption' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'one_time_keyboard' => false,
                'resize_keyboard' => true,
            ]),
        ]);
    }

    public function sendChatMenuWebappButton($buttonName)
    {
        return $this->send('setChatMenuButton', [
            'chat_id' => $this->init->platformId,
            'menu_button' => json_encode([
                'text' => $buttonName,
                'type' => 'web_app',
                'web_app' => ['url' => config('app.webapp_url')],
            ]),
        ]);
    }

    public function getUserProfilePhotos($platformId)
    {
        return $this->send('getUserProfilePhotos', [
            'user_id' => $platformId,
        ]);
    }

    public function getFile()
    {
        $url = 'https://api.telegram.org/file/bot'.config('app.token_tm').'/'.$this->getFileResult()['file_path'];

        return file_get_contents($url);
    }

    public function getFileData($file_id)
    {
        return $this->send('getFile', [
            'file_id' => $file_id,
        ]);
    }

    public function getChatMenuButton()
    {
        return $this->send('getChatMenuButton', [
            'chat_id' => $this->init->platformId,
        ]);
    }

    public function getStarTransactions()
    {
        return $this->send('getStarTransactions', ['flags' => ['inbound' => true]]);
    }

    public function getStarsStatus()
    {
        return $this->send('getStarsStatus');
    }

    public function getFileResult()
    {
        $file = null;
        switch ($this->init->type) {
            case 'photo':
                $item = null;
                if (isset($this->init->input->message->photo[3])) {
                    $item = $this->init->input->message->photo[3];
                }
                if ($item === null && isset($this->init->input->message->photo[2])) {
                    $item = $this->init->input->message->photo[2];
                }
                if ($item === null && isset($this->init->input->message->photo[1])) {
                    $item = $this->init->input->message->photo[1];
                }
                if ($item === null && isset($this->init->input->message->photo[0])) {
                    $item = $this->init->input->message->photo[0];
                }
                $file = $this->getFileData($item->file_id);
                break;
            case 'document':
                if (isset($this->init->input->message->document->file_id)) {
                    $file = $this->getFileData($this->init->input->message->document->file_id);
                }
                break;
            case 'voice':
                if (isset($this->init->input->message->voice->file_id)) {
                    $file = $this->getFileData($this->init->input->message->voice->file_id);
                }
                break;
            case 'audio':
                if (isset($this->init->input->message->audio->file_id)) {
                    $file = $this->getFileData($this->init->input->message->audio->file_id);
                }
                break;
            case 'video':
                if (isset($this->init->input->message->video->file_id)) {
                    $file = $this->getFileData($this->init->input->message->video->file_id);
                }
                break;
        }

        return $file = isset($file['ok']) && $file['ok'] == true ? $file['result'] : null;
    }

    public function sendSticker($sticker)
    {
        $data = [
            'start' => 'CAACAgIAAxkBAAIHt2EWWbeDGrue113ApcbU_J8GKK-kAALCEAAC3CZxSF-AQVCnwj0KIAQ',
            'new_module' => 'CAACAgIAAxkBAAIHvmEWWq9DJG10QSRRQ_2-dfjyiNlyAAJaDgAC-95pSFL4FhPqH7vPIAQ',
            'end_module' => 'CAACAgIAAxkBAAIHwGEWW-krQuXLcn_K-9FmMzm7ZsavAAJ6EQAC7HdoSF3dqYtzY9jTIAQ',
            'error_module' => 'CAACAgIAAxkBAAIHwWEWXMfuTaf2RyklmUxS9g6mQufGAAL4DQACD8doSKHBPqsQbVZWIAQ',
            'faq' => 'CAACAgIAAxkBAAIHxmEWXQN1pZ0NL7vrMB0ExHIoXb3LAAJlDwAC4O1wSCD5mEP1Yrk1IAQ',
        ];
        $this->send('sendSticker', [
            'chat_id' => $this->init->platformId,
            'sticker' => $data[$sticker],
        ]);
    }

    public function text($name, $data = [])
    {
        return Text::item($name, $data);
    }

    public function prepareText(string $text): string
    {
        return Text::prepareText($text);
    }

    public function send($method, $data = null)
    {
        Logger::commit([$method, $data], __METHOD__);
        $apiTm = new TmApi;
        $this->response = $apiTm->post($method, $data);
        Logger::commit($this->response, __METHOD__);

        return $this->response;
    }

    public function sendChatAction($daley = 0, $action = 'typing')
    {
        $round = round($daley / 5);
        if ($round > 1) {
            for ($i = 1; $i <= $round; $i++) {
                $this->send('sendChatAction', [
                    'chat_id' => $this->init->platformId,
                    'action' => $action,
                ]);
                sleep(5);
            }
        } else {
            $this->send('sendChatAction', [
                'chat_id' => $this->init->platformId,
                'action' => $action,
            ]);
            sleep($daley);
        }
    }
}
