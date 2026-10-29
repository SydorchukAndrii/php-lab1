<?php

declare(strict_types=1);

namespace App\Tasks;

final class HeadOfDepartment extends Teacher
{
    public function getRoleDetails(): string
    {
        return parent::getRoleDetails().'; посада: завідувач кафедри';
    }
}
