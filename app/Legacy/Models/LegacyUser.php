<?php

declare(strict_types=1);

namespace App\Legacy\Models;

use App\Models\AbstractEntity;

class LegacyUser extends AbstractEntity
{
    public function __construct(string $id, string $createdAt, protected string $name, protected string $email, protected string $role = 'worker')
    {
        parent::__construct($id, $createdAt);
    }

    public function getDetails(): string
    {
        return "Користувач {$this->name} ({$this->email}), роль: {$this->role}";
    }
}
