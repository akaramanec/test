<?php

namespace App\Http\Controllers\Bot;

use App\Bot\TmInfo;
use App\Http\Controllers\Controller;
use App\Services\BotTestService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function test()
    {
        $data = [
            'dishes' => [
                ['id' => 1, 'name' => 'Pizza'],
                ['id' => 2, 'name' => 'Burger'],
                ['id' => 3, 'name' => 'Pasta'],
                ['id' => 4, 'name' => 'French Fries'],
                ['id' => 5, 'name' => 'Sandwich'],
            ]
        ];
        return response()->json($data);
    }
}
