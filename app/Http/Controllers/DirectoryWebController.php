<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Довідники, користувачі та залишки: однаковий Blade CRUD без дублювання форм. */
class DirectoryWebController extends Controller
{
    private function kind(Request $request): string
    {
        $name = (string) $request->route()?->getName();
        $kind = explode('.', $name)[1] ?? '';
        abort_unless(in_array($kind, ['categories', 'suppliers', 'warehouses', 'stocks', 'users'], true), 404);

        return $kind;
    }

    private function spec(string $kind): array
    {
        return match ($kind) {
            'categories' => [
                'title' => 'Категорії', 'one' => 'категорію', 'model' => Category::class,
                'columns' => ['name' => 'Назва', 'code' => 'Код', 'is_active' => 'Активна'],
                'fields' => [
                    'name' => ['Назва', 'text'], 'code' => ['Код', 'text'],
                    'description' => ['Опис', 'textarea'], 'is_active' => ['Активна', 'checkbox'],
                ],
            ],
            'suppliers' => [
                'title' => 'Постачальники', 'one' => 'постачальника', 'model' => Supplier::class,
                'columns' => ['name' => 'Назва', 'contact_person' => 'Контакт', 'email' => 'Email'],
                'fields' => [
                    'name' => ['Назва', 'text'], 'contact_person' => ['Контактна особа', 'text'],
                    'phone' => ['Телефон', 'text'], 'email' => ['Email', 'email'],
                ],
            ],
            'warehouses' => [
                'title' => 'Склади', 'one' => 'склад', 'model' => Warehouse::class,
                'columns' => ['name' => 'Назва', 'code' => 'Код', 'address' => 'Адреса'],
                'fields' => [
                    'name' => ['Назва', 'text'], 'code' => ['Код', 'text'],
                    'address' => ['Адреса', 'text'], 'is_active' => ['Активний', 'checkbox'],
                ],
            ],
            'stocks' => [
                'title' => 'Залишки', 'one' => 'залишок', 'model' => Stock::class,
                'columns' => ['product.name' => 'Товар', 'warehouse.name' => 'Склад', 'quantity' => 'Кількість'],
                'fields' => [
                    'product_id' => ['Товар', 'select:products'],
                    'warehouse_id' => ['Склад', 'select:warehouses'],
                    'quantity' => ['Кількість', 'number'],
                ],
            ],
            'users' => [
                'title' => 'Користувачі', 'one' => 'користувача', 'model' => User::class,
                'columns' => ['name' => 'Ім’я', 'email' => 'Email', 'role' => 'Роль'],
                'fields' => [
                    'name' => ['Ім’я', 'text'], 'email' => ['Email', 'email'],
                    'password' => ['Пароль (під час редагування — за потреби)', 'password'],
                    'role' => ['Роль', 'select:roles'], 'is_active' => ['Активний', 'checkbox'],
                    'profile_phone' => ['Телефон профілю (1:1)', 'text'],
                    'profile_position' => ['Посада профілю (1:1)', 'text'],
                ],
            ],
            default => abort(404),
        };
    }

    private function choices(): array
    {
        return [
            'products' => Product::orderBy('name')->pluck('name', 'id')->all(),
            'warehouses' => Warehouse::orderBy('name')->pluck('name', 'id')->all(),
            'roles' => ['worker' => 'Працівник', 'manager' => 'Менеджер', 'admin' => 'Адміністратор'],
        ];
    }

    public function index(Request $request): View
    {
        $kind = $this->kind($request);
        $spec = $this->spec($kind);
        $query = $spec['model']::query();
        if ($kind === 'stocks') {
            $query->with(['product', 'warehouse']);
        } elseif ($kind === 'users') {
            $query->with('profile');
        }

        return view('ui.directory.index', [
            'kind' => $kind, 'spec' => $spec, 'rows' => $query->latest('id')->paginate(10),
        ]);
    }

    public function create(Request $request): View
    {
        $kind = $this->kind($request);
        $spec = $this->spec($kind);
        $model = new $spec['model'];

        return view('ui.directory.form', compact('kind', 'spec', 'model') + ['choices' => $this->choices()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $kind = $this->kind($request);
        $spec = $this->spec($kind);
        $data = $this->validated($request, $kind);
        DB::transaction(function () use ($kind, $spec, $data): void {
            $model = $spec['model']::create(collect($data)->except(['profile_phone', 'profile_position'])->all());
            if ($kind === 'users') {
                $model->profile()->create([
                    'phone' => $data['profile_phone'] ?? null,
                    'position' => $data['profile_position'] ?? null,
                ]);
            }
        });

        return redirect()->route('ui.'.$kind.'.index')->with('success', 'Запис створено.');
    }

    public function show(Request $request, int $record): View
    {
        return $this->edit($request, $record);
    }

    public function edit(Request $request, int $record): View
    {
        $kind = $this->kind($request);
        $spec = $this->spec($kind);
        $model = $spec['model']::findOrFail($record);
        if ($kind === 'users') {
            $model->load('profile');
        }

        return view('ui.directory.form', compact('kind', 'spec', 'model') + ['choices' => $this->choices()]);
    }

    public function update(Request $request, int $record): RedirectResponse
    {
        $kind = $this->kind($request);
        $spec = $this->spec($kind);
        $model = $spec['model']::findOrFail($record);
        $data = $this->validated($request, $kind, $model);
        DB::transaction(function () use ($kind, $model, $data): void {
            $model->update(collect($data)->except(['profile_phone', 'profile_position'])->all());
            if ($kind === 'users') {
                $model->profile()->updateOrCreate(['user_id' => $model->id], [
                    'phone' => $data['profile_phone'] ?? null,
                    'position' => $data['profile_position'] ?? null,
                ]);
            }
        });

        return redirect()->route('ui.'.$kind.'.index')->with('success', 'Запис оновлено.');
    }

    public function destroy(Request $request, int $record): RedirectResponse
    {
        $kind = $this->kind($request);
        $spec = $this->spec($kind);
        try {
            $spec['model']::findOrFail($record)->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Видалення неможливе: запис використовується іншими сутностями.');
        }

        return redirect()->route('ui.'.$kind.'.index')->with('success', 'Запис видалено.');
    }

    private function validated(Request $request, string $kind, ?Model $model = null): array
    {
        $id = $model?->getKey();
        $bool = ['sometimes', 'boolean'];
        if (in_array($kind, ['categories', 'warehouses', 'users'], true)) {
            $request->merge(['is_active' => $request->boolean('is_active') ? 1 : 0]);
        }
        $rules = match ($kind) {
            'categories' => [
                'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($id)],
                'code' => ['required', 'string', 'max:255', Rule::unique('categories', 'code')->ignore($id)],
                'description' => ['nullable', 'string'], 'is_active' => $bool,
            ],
            'suppliers' => [
                'name' => ['required', 'string', 'max:255'],
                'contact_person' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($id)],
            ],
            'warehouses' => [
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:255', Rule::unique('warehouses', 'code')->ignore($id)],
                'address' => ['required', 'string', 'max:255'], 'is_active' => $bool,
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
                'password' => [$id ? 'nullable' : 'required', 'string', 'min:8'],
                'is_active' => $bool,
                'profile_phone' => ['nullable', 'string', 'max:50'],
                'profile_position' => ['nullable', 'string', 'max:255'],
            ],
            default => [],
        };
        $data = $request->validate($rules);
        if ($kind === 'users' && empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
