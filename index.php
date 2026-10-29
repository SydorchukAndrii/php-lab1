<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use App\Core\Database;
use App\Core\EntityFactory;
use App\Models\Product;
use App\Services\ConsoleLogger;
use App\Services\EntityService;
use App\Tasks\Book;
use App\Tasks\Category;
use App\Tasks\HeadOfDepartment;
use App\Tasks\Triangle;

echo '=== Лабораторна робота №4 ==='.PHP_EOL;
$db1 = Database::getInstance();
$db2 = Database::getInstance();
echo 'Singleton: '.(($db1 === $db2) ? 'той самий екземпляр' : 'помилка').PHP_EOL;

$factory = new EntityFactory;
$product = $factory->createEntity('product', ['id' => '1', 'createdAt' => '2026-10-29', 'sku' => 'SKU-001', 'name' => 'Ноутбук', 'unit' => 'шт', 'minimumStock' => 5]);
$user = $factory->createEntity('user', ['id' => '2', 'createdAt' => '2026-10-29', 'name' => 'Адміністратор', 'email' => 'admin@example.test', 'role' => 'admin']);
$service = new EntityService(new ConsoleLogger);
echo $service->processEntity($product).PHP_EOL;
echo $service->processEntity($user).PHP_EOL;
if ($product instanceof Product) {
    echo 'JSON товару:'.PHP_EOL.$product->exportToJson().PHP_EOL;
}

$books = new Category(1, 'Програмування');
$books->addBook(new Book(1, 'PHP 8', ['Автор 1'], 2024));
$books->addBook(new Book(2, 'Python', ['Автор 2'], 2025));
echo 'Пошук книг PHP: '.count($books->searchBooks('PHP')).PHP_EOL;
$books->removeBook(1);
echo 'Після видалення: '.count($books->searchBooks('PHP')).PHP_EOL;
echo (new HeadOfDepartment('Іван Петренко'))->getRoleDetails().PHP_EOL;
$triangle = new Triangle(3, 4, 5);
echo 'Трикутник: периметр='.$triangle->perimeter().', площа='.$triangle->area().PHP_EOL;
