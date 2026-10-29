<?php

declare(strict_types=1);

$categories = ['Електроніка', 'Інструменти', 'Канцелярія', 'Меблі', 'Побутова хімія'];
sort($categories);

$product = [
    'id' => 1,
    'sku' => 'SKU-001',
    'name' => 'Ноутбук',
    'unit' => 'шт.',
    'minimumStock' => 5,
];
?>
<h2>1. Категорії товарів</h2>
<ul>
    <?php foreach ($categories as $category) { ?>
        <li><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></li>
    <?php } ?>
</ul>
<h2>2. Характеристики товару</h2>
<ul>
    <?php foreach ($product as $key => $value) { ?>
        <li><?= htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?></li>
    <?php } ?>
</ul>