<?php

declare(strict_types=1);

namespace App\Core;

final class Database
{
    private static ?self $instance = null;

    private function __construct()
    {
        echo 'Підключення до БД створено'.PHP_EOL;
    }

    private function __clone() {}

    public static function getInstance(): self
    {
        return self::$instance ??= new self;
    }
}
