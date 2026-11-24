<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DirectoryRecordService
{
    public function model(string $kind): string
    {
        return match ($kind) {
            'categories' => Category::class,
            'suppliers' => Supplier::class,
            'warehouses' => Warehouse::class,
            'stocks' => Stock::class,
            'users' => User::class,
            default => abort(404),
        };
    }

    public function createRecord(string $kind, array $data): Model
    {
        return DB::transaction(function () use ($kind, $data): Model {
            $class = $this->model($kind);
            $payload = Arr::except($data, ['profile_phone', 'profile_position']);
            $model = $class::create($payload);
            if ($kind === 'users') {
                $model->profile()->create([
                    'phone' => $data['profile_phone'] ?? null,
                    'position' => $data['profile_position'] ?? null,
                ]);
            }

            return $model;
        });
    }

    public function updateRecord(string $kind, Model $model, array $data): Model
    {
        return DB::transaction(function () use ($kind, $model, $data): Model {
            $payload = Arr::except($data, ['profile_phone', 'profile_position']);
            if ($kind === 'users' && empty($payload['password'])) {
                unset($payload['password']);
            }
            $model->update($payload);
            if ($kind === 'users') {
                $model->profile()->updateOrCreate(['user_id' => $model->id], [
                    'phone' => $data['profile_phone'] ?? null,
                    'position' => $data['profile_position'] ?? null,
                ]);
            }

            return $model->refresh();
        });
    }

    public function deleteRecord(Model $model): void
    {
        DB::transaction(fn () => $model->delete());
    }
}
