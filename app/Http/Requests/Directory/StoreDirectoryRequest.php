<?php

declare(strict_types=1);

namespace App\Http\Requests\Directory;

use Illuminate\Foundation\Http\FormRequest;

class StoreDirectoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->is('api/*') && in_array(DirectoryRules::kind($this), ['categories', 'warehouses', 'users'], true)) {
            $this->merge(['is_active' => $this->boolean('is_active') ? 1 : 0]);
        }
    }

    public function rules(): array
    {
        return DirectoryRules::for($this);
    }
}
