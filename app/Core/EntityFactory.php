<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\AbstractEntity;
use App\Models\Product;
use App\Models\User;
use InvalidArgumentException;

final class EntityFactory
{
    public function createEntity(string $type, array $data): AbstractEntity
    {
        return match (strtolower($type)) {
            'product' => new Product((string) $data['id'], (string) $data['createdAt'], (string) $data['sku'], (string) $data['name'], (string) ($data['unit'] ?? 'шт'), (int) ($data['minimumStock'] ?? 0)),
            'user' => new User((string) $data['id'], (string) $data['createdAt'], (string) $data['name'], (string) $data['email'], (string) ($data['role'] ?? 'worker')),
            default => throw new InvalidArgumentException("Невідомий тип сутності: {$type}"),
        };
    }
}
