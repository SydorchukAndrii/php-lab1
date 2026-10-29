<?php

declare(strict_types=1);
use Faker\Factory;

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Services/EntityService.php';
require_once __DIR__.'/app/Services/DataProcessor.php';

$quantity = 3;
$minimumStock = 5;
$isLow = isBelowMinimum($quantity, $minimumStock);

$faker = Factory::create();
$stocks = [];
for ($i = 0; $i < 10; $i++) {
    $stocks[] = $faker->numberBetween(0, 30);
}

$minThreshold = 10;
$filteredStocks = array_filter($stocks, function (int $stock) use ($minThreshold): bool {
    return $stock < $minThreshold;
});

try {
    $discountResult = applyDiscount(1000.0, 150.0);
    $discountMessage = 'Ціна зі знижкою: '.$discountResult;
} catch (Exception $e) {
    $discountMessage = 'Помилка: '.$e->getMessage();
} finally {
    $discountMessage .= ' | Завершення спроби розрахунку.';
}

$number = 1234076;
$text = 'Система обліку товарів на складі';
$needle = 'товарів';
$found = findSubstring($text, $needle);
$evenWordsText = 'склад товари база даних';
?>
<!doctype html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <title>Лабораторна робота №3 — PHP</title>
</head>

<body>
    <h1>Лабораторна робота №3 — складський облік</h1>
    <?php require __DIR__.'/app/Models/EntityArray.php'; ?>

    <h2>3. Користувацька функція</h2>
    <p>Залишок: <?= $quantity ?>, мінімум: <?= $minimumStock ?>.
        Нижче мінімуму: <?= $isLow ? 'Так' : 'Ні' ?></p>

    <h2>4. Faker та анонімна функція</h2>
    <p>Згенеровані залишки: <?= implode(', ', $stocks) ?></p>
    <p>Залишки менші за <?= $minThreshold ?>: <?= implode(', ', $filteredStocks) ?></p>

    <h2>5. Обробка винятків</h2>
    <p><?= htmlspecialchars($discountMessage, ENT_QUOTES, 'UTF-8') ?></p>

    <h2>6. Індивідуальний варіант №4</h2>
    <p>Мінімальна цифра числа <?= $number ?>: <?= findMinimumDigit($number) ?></p>
    <p>Знайдений підрядок: <?= htmlspecialchars($found ?? 'Не знайдено', ENT_QUOTES, 'UTF-8') ?></p>
    <p>Рядок: <?= htmlspecialchars($evenWordsText, ENT_QUOTES, 'UTF-8') ?>.
        Слів із парною кількістю символів: <?= countEvenLengthWords($evenWordsText) ?></p>
</body>

</html>