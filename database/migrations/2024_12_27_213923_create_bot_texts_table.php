<?php

use App\Models\CustomerPlatform;
use App\Services\EloquentTableValuesTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use EloquentTableValuesTrait;

    public function up()
    {
        Schema::create('bot_texts', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id');
            $table->string('name', 50)->unique();
            $table->text('text');
            $table->json('placeholder')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bot_texts');
    }
};
