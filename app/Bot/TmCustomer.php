<?php

namespace App\Bot;

use App\Models\Bot\Employer;

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
        $this->model = Employer::query()->where('platform_id', $this->init->platformId)->first();
        if ($this->model) {
            if ($this->model->status == Employer::STATUS_UNSUBSCRIBED) {
                $this->model->update(['status' => Employer::STATUS_NEW]);
            }
            return;
        }
        if ($this->init->type == 'kicked') {
            return;
        }
        $this->model = new Employer();
        $this->model->platform_id = (string)$this->init->platformId;
        $this->model->status = Employer::STATUS_NEW;
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
    }

    public function getModelActive()
    {
        $this->model = Employer::where('platform_id', $this->init->platformId)
            ->where('status', Employer::STATUS_ACTIVE)
            ->first();
        return $this->model;
    }
}
