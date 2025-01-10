<?php

namespace App\Bot;

use Illuminate\Support\Facades\DB;
use PDO;

/**
 * @property string $platform_id
 * @property string $name
 * @property string $data
 */
class TmSession
{
    public $platform_id;
    public $name;
    public $data;

    public function saveModel($name, $data)
    {
        $this->del($name);
        $q = DB::connection()
            ->getPdo()
            ->prepare("insert into sessions (platform_id, name, data) values (:platform_id, :name, :data)");
        $q->bindValue(':platform_id', $this->platform_id);
        $q->bindValue(':name', $name);
        $q->bindValue(':data', json_encode($data));
        $q->execute();
    }

    public function getModel($name)
    {
        $q = DB::connection()
            ->getPdo()
            ->prepare("select data from sessions where platform_id=:platform_id and name=:name limit 1");
        $q->bindValue(':platform_id', $this->platform_id);
        $q->bindValue(':name', $name);
        $q->execute();
        return $q->fetch(PDO::FETCH_ASSOC);
    }

    public function del($name)
    {
        $q = DB::connection()
            ->getPdo()
            ->prepare("delete from sessions where platform_id=:platform_id and name=:name");
        $q->bindValue(':platform_id', $this->platform_id);
        $q->bindValue(':name', $name);
        return $q->execute();
    }

    public function delAll()
    {
        $q = DB::connection()
            ->getPdo()
            ->prepare("delete from sessions where platform_id=:platform_id");
        $q->bindValue(':platform_id', $this->platform_id);
        return $q->execute();
    }

    public function set($name, $value)
    {
        return $this->saveModel($name, [$name => $value]);
    }

    public function get($name)
    {
        if ($model = $this->getModel($name)) {
            $d = json_decode($model['data'], true);
            return $d[$name];
        }
        return null;
    }

    public function saveCommonRequest($request)
    {
        if (isset($request['ok']) && $request['ok'] === true && isset($request['result']['message_id'])) {
            (array)$data = $this->common();
            return $this->saveModel('common', $this->setDataUnique($request['result']['message_id'], $data));
        }
    }

    public function getRequestMessageId($request)
    {
        if (isset($request['ok']) && $request['ok'] === true && isset($request['result']['message_id'])) {
            return $request['result']['message_id'];
        }
        return null;
    }

    public function saveCommonMessageId($messageId)
    {
        if ($messageId) {
            (array)$data = $this->common();
            return $this->saveModel('common', $this->setDataUnique($messageId, $data));
        }
    }

    public function common()
    {
        $model = $this->getModel('common');
        return $model ? json_decode($model['data'], true) : ['message_id' => []];
    }

    private function setDataUnique($message_id, $data)
    {
        $data['message_id'][] = $message_id;
        return ['message_id' => array_unique($data['message_id'])];
    }

    private function setDataExclude($message_id, $data)
    {
        if (($key = array_search($message_id, $data['message_id'])) !== false) {
            unset($data['message_id'][$key]);
        } else {
            $data['message_id'][] = $message_id;
        }
        return $data;
    }
}
