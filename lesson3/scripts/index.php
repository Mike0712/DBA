<?php
require __DIR__ . '/autoload.php';
$data = include __DIR__ . '/seeding.php';
?>
    <ul>
        <li></li><a href="/lesson3/scripts?mysql=1">Вставить данные для MySql</a></li>
        <li></li><a href="/lesson3/scripts?pgsql=1">Вставить данные для PostgreSql</a></li>
    </ul>
<?php
if ($_GET['mysql'] == 1) {
    foreach ($data as $item) {
        $query = new \App\Models\Goods('mysql');
        $query->fill($item);
        $query->insert();
    }
}
if ($_GET['pgsql'] == 1) {
    foreach ($data as $item) {
        $query = new \App\Models\Goods('pgsql');
        $query->fill($item);
        $query->insert();
    }
}
