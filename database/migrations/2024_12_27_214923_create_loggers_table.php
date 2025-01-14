<?php

use App\Models\Bot\Customer;
use App\Services\EloquentTableValuesTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use EloquentTableValuesTrait;

    public function up()
    {
        Schema::create('loggers', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id');
            $table->json('data')->nullable();
            $table->string('slug')->nullable();
            $this->foreignFor($table, new Customer, 'platform_id');
            $table->dateTime('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('loggers');
    }
};
