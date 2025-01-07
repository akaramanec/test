<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logger extends Model
{
    public $timestamps = false;

    protected $table = 'loggers';

    protected $fillable = ['data', 'platform_id', 'created_at', 'slug'];

    protected $casts = ['data' => 'array'];

    public static function commit($data, $slug = null, $platformId = null)
    {
        if (is_string($data) && is_array($json = json_decode($data, true))) {
            $data = $json;
        }
        if (! $data) {
            $data = 'empty';
        }

        return static::create([
            'data' => $data,
            'slug' => $slug,
            'platform_id' => $platformId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function commitError(\Exception $e, $slug = null)
    {
        self::commit([$e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]], $slug);
    }
}
