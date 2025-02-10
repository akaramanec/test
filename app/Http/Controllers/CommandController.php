<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CommandController extends Controller
{
    public function run(Request $request)
    {
        $command = $request->query('command'); // Отримуємо параметр з URL

        if (!$command) {
            return response()->json(['error' => 'No command provided'], 400);
        }

        // Виконання Artisan команди
        try {
            Artisan::call($command);
            $output = Artisan::output();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['output' => $output]);
    }
}
