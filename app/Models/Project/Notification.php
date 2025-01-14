<?php

namespace App\Models\Project;

use App\Models\Bot\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    const ACTION_IN_ESTABLISHMENT = 'inEstablishment';
    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';

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
}
