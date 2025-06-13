<?php

namespace App\Models\Project;

use App\Models\Bot\Employer;
use App\Models\Bot\Text;
use App\Services\Project\InEstablishmentService;
use App\Services\Tabster\TabsterService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;

    const ACTION_IN_ESTABLISHMENT = 'inEstablishment';
    const STATUS_NEW = 'new';
    const   STATUS_PROCESSING = 'processing';
    const STATUS_ASSIGNED = 'assigned';

    protected $table = 'project_notifications';

    protected $fillable = [
        'key',
        'action',
        'status',
        'data',
        'message_ids',
        'sent_at',
    ];

    protected $casts = [
        'data' => 'array',
        'message_ids' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            do {
                $uuid = Str::uuid()->toString();
            } while (self::where('uuid', $uuid)->exists());

            $model->uuid = $uuid;
        });
    }

    public function addData(array $addData)
    {
        $data = $this->data;
        $data = array_merge($data, $addData);
        $this->update(['data' => $data]);
        $this->refresh();
    }

    public function assignByWaiter(Employer $waiter)
    {
        $this->addData([
            'assigned_by' => Employer::ROLE_WAITER,
            'assigned_employer_id' => $waiter->external_id,
            'waiter_phone' => $waiter->phone,
        ]);
        $this->update(['status' => Notification::STATUS_ASSIGNED]);

        $placeholders = TabsterService::getPlaceholdersFromNotification($this);
        $text = Text::getPrepared('youAssignReserve', $placeholders);
        $waiterBot = $waiter->getBot();
        $waiterBot->sendMessage($text);
        $waiterBot->saveResponseMessageIdToCommon();

        $admins = $this->data['workers']['admins'];
        $placeholders['{waiter}'] = $waiter->name;
        foreach ($admins as $adminData) {
            if (!$admin = Employer::whereExternalId($adminData['id'])->first()) continue;
            $adminBot = $admin->getBot();
            $adminBot->sendMessage(Text::getPrepared('waiterAssigned', $placeholders));
            $adminBot->saveResponseMessageIdToCommon();
        }

        InEstablishmentService::deleteMessages($this);
    }

    public function assignByAdmin(Employer $admin, Employer $waiter)
    {
        $this->addData([
            'assigned_by' => Employer::ROLE_ADMIN,
            'assigned_employer_id' => $waiter->external_id,
            'waiter_phone' => $waiter->phone,
        ]);
        $this->update(['status' => Notification::STATUS_ASSIGNED]);

        $placeholders = TabsterService::getPlaceholdersFromNotification($this);
        if ($admin->id == $waiter->id) {
            $text = Text::getPrepared('youAssignedYourself', $placeholders);
        } else {
            $placeholders['{waiter}'] = $waiter->fullName();
            $text = Text::getPrepared('youAssignedWaiter', $placeholders);
        }
        $adminBot = $admin->getBot();
        $adminBot->sendMessage($text);
        $adminBot->saveResponseMessageIdToCommon();

        if ($admin->id != $waiter->id) {
            $waiterBot = $waiter->getBot();
            $text = Text::getPrepared('adminAssigned', $placeholders);
            $waiterBot->sendMessage($text);
            $waiterBot->saveResponseMessageIdToCommon();
        }

        InEstablishmentService::deleteMessages($this);
    }
}
