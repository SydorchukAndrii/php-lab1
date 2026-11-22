<?php

declare(strict_types=1);

namespace App\Core;

use App\Legacy\Models\LegacyProduct;
use App\Legacy\Models\LegacyUser;
use App\Models\AbstractEntity;
use InvalidArgumentException;

final class EntityFactory
{
    public function createEntity(string $type, array $data): AbstractEntity
    {
        return match (strtolower($type)) {
            'product' => new LegacyProduct((string) $data['id'], (string) $data['createdAt'], (string) $data['sku'], (string) $data['name'], (string) ($data['unit'] ?? 'шт'), (int) ($data['minimumStock'] ?? 0)),
            'user' => new LegacyUser((string) $data['id'], (string) $data['createdAt'], (string) $data['name'], (string) $data['email'], (string) ($data['role'] ?? 'worker')),
            default => throw new InvalidArgumentException("Невідомий тип сутності: {$type}"),
        };
    }
}
