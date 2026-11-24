<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ProductPolicy checks the owner in both controllers.
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $id = $product instanceof Product ? $product->id : $product;
        $required = $this->is('api/*') ? 'sometimes' : 'required';

        return [
            'category_id' => [$required, 'integer', 'exists:categories,id'],
            'sku' => [$required, 'string', 'min:2', 'max:255', Rule::unique('products', 'sku')->ignore($id)],
            'name' => [$required, 'string', 'min:3', 'max:255'],
            'unit' => [$required, 'string', 'max:20'],
            'minimum_stock' => [$required, 'integer', 'min:0'],
            'is_active' => [$required, 'boolean'],
            'supplier_ids' => ['sometimes', 'array'],
            'supplier_ids.*' => ['integer', 'distinct', 'exists:suppliers,id'],
        ];
    }
}
