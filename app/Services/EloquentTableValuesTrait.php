<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;

trait EloquentTableValuesTrait
{

    /**
     * @param  Blueprint  $table
     * @param  object  $eloquent
     * @param  string|null  $column
     * @param  string|null  $after  default 'id'
     * @param  string  $delete  default 'cascade'
     * @param  null  $name
     * @return void
     */
    function foreignFor(Blueprint $table, object $eloquent, ?string $column = null, ?string $after = null, string $delete = 'cascade', $name = null)
    {
        $table->foreignIdFor($eloquent::class, $column)->nullable()->after($after);
        $table->foreign($column ?? $eloquent::columnForForeignKey(), $name)->references($eloquent::idColumnName())->on($eloquent::tableName())->onDelete($delete);
    }
}
