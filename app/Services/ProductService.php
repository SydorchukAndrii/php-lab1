<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendProductCreatedNotificationJob;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getPaginatedList(?string $term = null, int $perPage = 10): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'suppliers', 'stocks'])
            ->when($term !== null && $term !== '', function ($query) use ($term): void {
                $query->where(function ($q) use ($term): void {
                    $q->where('name', 'like', '%'.$term.'%')
                        ->orWhere('sku', 'like', '%'.$term.'%');
                });
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function createProduct(array $data, User $author): Product
    {
        $product = DB::transaction(function () use ($data, $author): Product {
            $product = Product::create(Arr::except($data, ['supplier_ids']) + [
                'user_id' => $author->id,
            ]);
            $product->suppliers()->sync($data['supplier_ids'] ?? []);

            return $product;
        });

        // This executes only after the transaction above completes successfully.
        SendProductCreatedNotificationJob::dispatch($product->id);

        return $product->load(['category', 'suppliers', 'stocks']);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $product->update(Arr::except($data, ['supplier_ids', 'user_id']));
            if (array_key_exists('supplier_ids', $data)) {
                $product->suppliers()->sync($data['supplier_ids'] ?? []);
            }

            return $product->refresh()->load(['category', 'suppliers', 'stocks']);
        });
    }

    public function deleteProduct(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $product->suppliers()->detach();
            $product->delete();
        });
    }

    /** Pure calculation, independent of Eloquent, HTTP and database. */
    public function calculateStockDeficit(int $quantity, int $minimumStock): int
    {
        if ($quantity < 0 || $minimumStock < 0) {
            throw new \InvalidArgumentException('Quantity and minimum stock cannot be negative.');
        }

        return max(0, $minimumStock - $quantity);
    }
}
