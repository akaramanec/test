<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('platform_id', 50);
            $table->string('name', 50);
            $table->json('data')->nullable();
            $table->primary(['platform_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};
