<?php

namespace App\Bot;

use App\Models\Bot\Customer;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Laravel\Facades\Image;

class TmCustomer
{
    public $bot;
    public $model;
    private $init;

    public function __construct(TmInit $tmInit)
    {
        $this->init = $tmInit;
        $this->bot = new TmCommon();
        if ($this->getModelActive()) {
            return;
        }
        $this->setModel();
    }

    public function setModel()
    {
        $this->model = Customer::query()->where('platform_id', $this->init->platformId)->first();
        if ($this->model) {
            if ($this->model->status == Customer::STATUS_UNSUBSCRIBED) {
                $this->model->update(['status' => Customer::STATUS_NEW]);
            }
            return;
        }
        if ($this->init->type == 'kicked') {
            return;
        }
        $this->model = new Customer();
        $this->model->platform_id = (string)$this->init->platformId;
        $this->model->status = Customer::STATUS_NEW;
        $this->model->name = '';
        if (isset($this->init->input->message->chat->last_name)) {
            $this->model->name .= ' ' . $this->init->input->message->chat->last_name;
        }
        if (isset($this->init->input->message->chat->first_name)) {
            $this->model->name .= ' ' . $this->init->input->message->chat->first_name;
        }
        if (!$this->model->name && isset($this->init->input->message->chat->username)) {
            $this->model->name = ' ' . $this->init->input->message->chat->username;
        }
        $this->model->name = trim($this->model->name);
        $this->model->save();
        $this->model->refresh();
        $this->getPhotos();
    }

    public function getPhotos()
    {
        $photos = $this->bot->getUserProfilePhotos($this->init->platformId);
        if (isset($photos['result']['photos'][0][0])) {
            $file = $this->bot->getFileData($photos['result']['photos'][0][0]['file_id']);
            $pathDirectory = storage_path('app') . '/public/customer/' . $this->model->id . '/';
            @mkdir($pathDirectory, 0777, true);
            $file_path = explode('.', $file['result']['file_path']);
            $extension = '.' . $file_path[1];
            if (isset($file['result']['file_path'])) {
                $url = 'https://api.telegram.org/file/bot' . config('app.token_tm') . '/' . $file['result']['file_path'];
                $fullPath = $pathDirectory . 'avatar' . $extension;
                if (file_put_contents($fullPath, file_get_contents($url))) {
                    @chmod($fullPath, 0777);
                    Image::make($fullPath)->encode('jpg', 80)->save($pathDirectory . 'avatar.jpg');
                    return true;
                }
            }
        }
    }

    public function getModelActive()
    {
        $this->model = Customer::where('platform_id', $this->init->platformId)
            ->where('status', Customer::STATUS_ACTIVE)
            ->first();
        if ($this->model && $this->model->imgUrl() == $this->model->noAvatar) {
            $this->getPhotos();
        }
        return $this->model;
    }
}
