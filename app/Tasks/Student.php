<?php

declare(strict_types=1);

namespace App\Tasks;

class Student extends Person
{
    public function getRoleDetails(): string
    {
        return "Особа: {$this->name}; роль: студент";
    }
}
