<?php

use App\Models\Customer;
use App\Services\EloquentTableValuesTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use EloquentTableValuesTrait;

    public function up()
    {
        Schema::create('customer_platforms', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id');
            $this->foreignFor($table, new Customer);
            $table->string('phone', 30)->nullable()->index();
            $table->string('platform_id', 50)->unique();
            $table->enum('status', ['new', 'inactive', 'active', 'unsubscribed'])->index();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_platforms');
    }
};
