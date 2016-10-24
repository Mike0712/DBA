<?php
require __DIR__ . '/autoload.php';
$data = include __DIR__ . '/seeding.php';
$dataPivot = include __DIR__ . '/pivot.php';
?>
    <ul>
        <li></li><a href="/final/script?pivot=1">Вставить данные для Pivot</a></li>
        <li></li><a href="/final/script?pgsql=1">Вставить данные для PostgreSql</a></li>
    </ul>
<?php

if ($_GET['pgsql'] == 1) {
    foreach ($data as $item) {
        $query = new \App\Models\Goods('pgsql');
        $query->fill($item);
        $query->insert();
    }
}
if ($_GET['pivot'] == 1) {
    foreach ($data as $item) {
        $query = new \App\Models\GoodsCategory('pgsql');
        $query->fill($item);
        $query->insert();
    }
}
