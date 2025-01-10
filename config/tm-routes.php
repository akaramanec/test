<?php
return [
    '/start' => [
        'class' => 'App\Bot\TmCommon',
        'method' => 'start'
    ],
    '/menu' => [
        'class' => 'App\Bot\TmCommon',
        'method' => 'start'
    ],
    '/unknown' => [
        'class' => 'App\Bot\TmCommon',
        'method' => 'unknown'
    ],
    '/help' => [
        'class' => 'App\Bot\TmCommon',
        'method' => 'help'
    ],
    'phone' => [
        'class' => 'App\Bot\TmAuth',
        'method' => 'phone'
    ],
    'phone-save' => [
        'class' => 'App\Bot\TmAuth',
        'method' => 'phoneSave'
    ],
];
