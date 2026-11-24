<?php

declare(strict_types=1);

namespace App\Http\Requests\Movement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Filter completely blank rows in the editable Blade form.
        $items = array_values(array_filter($this->input('items', []),
            fn ($item) => is_array($item) && (($item['product_id'] ?? '') !== '' || ($item['quantity'] ?? '') !== '')));
        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'type' => ['required', 'in:receipt,shipment'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        if ($key !== null) {
            return $data;
        }
        $ids = array_column($data['items'], 'product_id');
        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages(['items' => 'Один товар можна додати лише один раз.']);
        }
        foreach ($data['items'] as &$item) {
            $item['unit_price'] = $item['unit_price'] ?? 0;
        }
        unset($item);

        return $data;
    }
}
