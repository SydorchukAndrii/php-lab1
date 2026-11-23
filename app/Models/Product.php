<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category_id', 'sku', 'name', 'unit', 'minimum_stock', 'is_active'];

    protected $casts = ['minimum_stock' => 'integer', 'is_active' => 'boolean'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class)->withTimestamps();
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function movementItems(): HasMany
    {
        return $this->hasMany(MovementItem::class);
    }
}
