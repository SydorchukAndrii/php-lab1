<?php

declare(strict_types=1);

function findMinimumDigit(int $number): int
{
    if ($number < 0) {
        throw new InvalidArgumentException('Число має бути натуральним.');
    }

    return min(array_map('intval', str_split((string) $number)));
}

function findSubstring(string $text, string $needle): ?string
{
    if ($needle === '') {
        return null;
    }

    return strpos($text, $needle) !== false ? $needle : null;
}

function countEvenLengthWords(string $text): int
{
    $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
    $count = 0;

    foreach ($words ?: [] as $word) {
        $length = preg_match_all('/./us', $word);
        if ($length !== false && $length % 2 === 0) {
            $count++;
        }
    }

    return $count;
}
