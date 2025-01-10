<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id');
            $table->string('platform_id', 50)->unique();
            $table->enum('status', ['new', 'inactive', 'active', 'subscribed', 'unsubscribed', 'blacklist'])->default('active')->index();
            $table->string('name', 510)->nullable()->index();
            $table->string('password')->nullable()->index();
            $table->string('phone', 30)->nullable()->index();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
