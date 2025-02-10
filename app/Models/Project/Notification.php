<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;

    const ACTION_IN_ESTABLISHMENT = 'inEstablishment';
    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
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
}
