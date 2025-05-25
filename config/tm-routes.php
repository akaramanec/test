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
    'a-ae' => [
        'class' => 'App\Bot\TmAdmin',
        'method' => 'assignEmployer'
    ],
    'a-ocae' => [
        'class' => 'App\Bot\TmAdmin',
        'method' => 'orderCallAssignEmployer'
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
