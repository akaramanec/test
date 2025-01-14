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
    'unsubscribed' => [
        'class' => 'App\Bot\TmAuth',
        'method' => 'unsubscribed'
    ],
    'a-aw' => [
        'class' => 'App\Bot\TmAdmin',
        'method' => 'assignWaiter'
    ],
    'w-av' => [
        'class' => 'App\Bot\TmWaiter',
        'method' => 'assignReserve'
    ],
    'w-ar' => [
        'class' => 'App\Bot\TmWaiter',
        'method' => 'assignReserve'
    ],
    'w-rv' => [
        'class' => 'App\Bot\TmWaiter',
        'method' => 'rejectReserve'
    ],
    'w-rr' => [
        'class' => 'App\Bot\TmWaiter',
        'method' => 'rejectReserve'
    ],
];
