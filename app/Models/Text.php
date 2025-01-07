<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    protected $table = 'texts';

    public $timestamps = false;

    protected $fillable = [
        'name', 'text', 'placeholder',
    ];

    protected $casts = ['placeholder' => 'array'];

    public function add($request)
    {
        $this->name = $request->name;
        $this->text = $request->text;
        $this->save();
    }

    public static function item($command, $data = [])
    {
        $self = self::firstWhere('name', $command);
        if (isset($self->text)) {
            return strtr($self->text, $data);
        }

        return $command;
    }

    public function placeholderShow()
    {
        $html = '';
        if ($this->placeholder) {
            foreach ($this->placeholder as $key => $val) {
                $html .= ' <p><strong> '.$key.' </strong> <code>'.$val.'</code></p>';
            }
        }

        return $html;
    }

    public static function prepareText(?string $text): string
    {
        $tagsPlaceholders = [
            '<p>&nbsp;</p>' => PHP_EOL,
            '</p>' => PHP_EOL,
            '&nbsp;' => ' ',
            '<br>' => PHP_EOL,
        ];
        $allowedTags = '<b></b><i></i><s></s><strong></strong><em></em><strike></strike><u></u><code></code><a></a>';

        return strip_tags(strtr($text, $tagsPlaceholders), $allowedTags);
    }
}
