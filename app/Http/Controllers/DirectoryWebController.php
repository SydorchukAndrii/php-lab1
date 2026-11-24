<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Directory\StoreDirectoryRequest;
use App\Http\Requests\Directory\UpdateDirectoryRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DirectoryRecordService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Довідники, користувачі та залишки: однаковий Blade CRUD без дублювання форм. */
class DirectoryWebController extends Controller
{
    public function __construct(private readonly DirectoryRecordService $records) {}

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

    public function store(StoreDirectoryRequest $request): RedirectResponse
    {
        $kind = $this->kind($request);
        $this->records->createRecord($kind, $request->validated());

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

    public function update(UpdateDirectoryRequest $request, int $record): RedirectResponse
    {
        $kind = $this->kind($request);
        $modelClass = $this->records->model($kind);
        $model = $modelClass::findOrFail($record);
        $this->records->updateRecord($kind, $model, $request->validated());

        return redirect()->route('ui.'.$kind.'.index')->with('success', 'Запис оновлено.');
    }

    public function destroy(Request $request, int $record): RedirectResponse
    {
        $kind = $this->kind($request);
        $modelClass = $this->records->model($kind);
        try {
            $this->records->deleteRecord($modelClass::findOrFail($record));
        } catch (QueryException $e) {
            return back()->with('error', 'Видалення неможливе: запис використовується іншими сутностями.');
        }

        return redirect()->route('ui.'.$kind.'.index')->with('success', 'Запис видалено.');
    }
}
