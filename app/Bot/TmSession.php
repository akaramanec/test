<?php

namespace App\Bot;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $platform_id
 * @property string $name
 * @property string $data
 */
class TmSession extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'sessions';

    protected $fillable = ['platform_id', 'name', 'data'];

    protected $casts = ['data' => 'array'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'platform_id', 'platform_id');
    }

    public function saveModel($name, $data)
    {
        return self::updateOrCreate(['platform_id' => $this->platform_id, 'name' => $name], ['data' => $data]);
    }

    public function getModel($name)
    {
        return self::where(['platform_id' => $this->platform_id])->where(['name' => $name])->first();
    }

    public function del($name)
    {
        return self::where(['platform_id' => $this->platform_id])->where(['name' => $name])->delete();
    }

    public function delAll()
    {
        return self::where(['platform_id' => $this->platform_id])->delete();
    }

    public function set($name, $value)
    {
        return $this->saveModel($name, [$name => $value]);
    }

    public function get($name)
    {
        $model = $this->getModel($name);

        return $model ? $model->data[$name] : null;
    }

    public function saveCommonRequest($request)
    {
        if (isset($request['ok']) && $request['ok'] === true && isset($request['result']['message_id'])) {
            (array) $data = $this->common();

            return $this->saveModel('common', $this->setDataUnique($request['result']['message_id'], $data));
        }
    }

    public function saveCommonMessageId($messageId)
    {
        if ($messageId) {
            (array) $data = $this->common();

            return $this->saveModel('common', $this->setDataUnique($messageId, $data));
        }
    }

    public function common()
    {
        $model = $this->getModel('common');

        return $model ? $model->data : ['message_id' => []];
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
