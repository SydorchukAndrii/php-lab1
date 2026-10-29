<?php

declare(strict_types=1);

function isBelowMinimum(int $quantity, int $minimumStock): bool
{
    return $quantity < $minimumStock;
}

function applyDiscount(float $price, float $discount): float
{
    if ($discount < 0 || $discount > 100) {
        throw new Exception('Неприпустиме значення знижки!');
    }

    return $price - ($price * ($discount / 100));
}
