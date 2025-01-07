<?php

namespace App\Services;

trait TableValuesTrait
{
    public static function idColumnName(): string
    {
        return (new self())->getKeyName();
    }

    public static function tableName(): string
    {
        return (new self())->getTable();
    }

        public static function columnForForeignKey(): string
    {
        return (new self())->getForeignKey();
    }
}
