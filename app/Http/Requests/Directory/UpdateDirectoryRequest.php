<?php

declare(strict_types=1);

namespace App\Http\Requests\Directory;

class UpdateDirectoryRequest extends StoreDirectoryRequest
{
    public function rules(): array
    {
        return DirectoryRules::for($this, true);
    }
}
