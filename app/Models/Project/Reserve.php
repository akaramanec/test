<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PROCESSED = 'processed';

    use HasFactory;
    protected $table = 'project_reserves';

    protected $fillable = [
        'key',
        'status',
        'date',
        'data',
        'message_ids',
        'sent_at',
    ];

    protected $casts = [
        'data' => 'array',
        'message_ids' => 'array',
    ];
}
