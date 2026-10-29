<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\LoggerInterface;
use RuntimeException;

final class FileLogger implements LoggerInterface
{
    public function __construct(private string $path = 'storage/entity.log') {}

    public function log(string $message): void
    {
        $dir = dirname($this->path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException('Не вдалося створити каталог логів');
        }
        if (file_put_contents($this->path, '[FILE] '.$message.PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException('Не вдалося записати лог');
        }
    }
}
