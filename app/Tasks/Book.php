<?php

declare(strict_types=1);

namespace App\Tasks;

final class Book
{
    public function __construct(public readonly int $id, public readonly string $title, public readonly array $authors, public readonly int $pubYear) {}
}
