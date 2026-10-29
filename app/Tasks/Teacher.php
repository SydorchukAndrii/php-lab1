<?php

declare(strict_types=1);

namespace App\Tasks;

class Teacher extends Student
{
    public function getRoleDetails(): string
    {
        return parent::getRoleDetails().'; кваліфікація: викладач';
    }
}
