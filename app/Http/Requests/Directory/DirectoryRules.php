<?php

declare(strict_types=1);

namespace App\Http\Requests\Directory;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DirectoryRules
{
    public static function kind(Request $request): string
    {
        if ($request->is('api/*')) {
            return (string) $request->segment(3);
        }

        return explode('.', (string) $request->route()?->getName())[1] ?? '';
    }

    public static function for(Request $request, bool $update = false): array
    {
        $kind = self::kind($request);
        $id = $request->route('record');
        $required = $request->is('api/*') && $update ? 'sometimes' : 'required';
        $bool = ['sometimes', 'boolean'];

        return match ($kind) {
            'categories' => [
                'name' => [$required, 'string', 'max:255', Rule::unique('categories', 'name')->ignore($id)],
                'code' => [$required, 'string', 'max:255', Rule::unique('categories', 'code')->ignore($id)],
                'description' => ['nullable', 'string'], 'is_active' => $bool,
            ],
            'suppliers' => [
                'name' => [$required, 'string', 'max:255'],
                'contact_person' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($id)],
            ],
            'warehouses' => [
                'name' => [$required, 'string', 'max:255'],
                'code' => [$required, 'string', 'max:255', Rule::unique('warehouses', 'code')->ignore($id)],
                'address' => [$required, 'string', 'max:255'], 'is_active' => $bool,
            ],
            'stocks' => [
                'product_id' => ['required', 'integer', 'exists:products,id',
                    Rule::unique('stocks', 'product_id')->where('warehouse_id', $request->input('warehouse_id'))->ignore($id)],
                'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
                'quantity' => ['required', 'integer', 'min:0'],
            ],
            'users' => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
                'role' => ['required', Rule::in(['worker', 'manager', 'admin'])],
                'password' => [$update ? 'nullable' : 'required', 'string', 'min:8'],
                'is_active' => $bool,
                'profile_phone' => ['nullable', 'string', 'max:50'],
                'profile_position' => ['nullable', 'string', 'max:255'],
            ],
            default => [],
        };
    }
}
