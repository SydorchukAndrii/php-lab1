<?php

declare(strict_types=1);

namespace App\Tasks;

use InvalidArgumentException;

final class Triangle
{
    public function __construct(public readonly float $a, public readonly float $b, public readonly float $c)
    {
        if ($a <= 0 || $b <= 0 || $c <= 0 || $a + $b <= $c || $a + $c <= $b || $b + $c <= $a) {
            throw new InvalidArgumentException('Сторони не утворюють трикутник');
        }
    }

    public function perimeter(): float
    {
        return $this->a + $this->b + $this->c;
    }

    public function area(): float
    {
        $s = $this->perimeter() / 2;

        return sqrt($s * ($s - $this->a) * ($s - $this->b) * ($s - $this->c));
    }
}
