# MySql

## Урок 4.

Итак, у нас уже имеется таблица `goods`. Создадим ещё одну таблицу `category`. Она будет содержать всего 2 столбца, айдишник
и собственно саму категорию

```sql
CREATE TABLE `category` (`id` serial, `title` varchar(100));
```
Не забудем навесить первичный ключ на поле `id`
```sql
ALTER TABLE `category`
ADD PRIMARY KEY `id` (`id`);
```

И ещё одну таблицу `brands`:

```sql
CREATE TABLE `brands` (`id` serial, `brand` varchar(100), PRIMARY KEY `id` (`id`));
```

Пофиксим нашу таблицу `goods`, добавив в неё два поля для связи с брендами и категориями.

```sql
ALTER TABLE `goods`
ADD `category_id` bigint(20) unsigned NULL;
```
и бренды
```sql
ALTER TABLE `goods`
ADD `brand_id` bigint(20) unsigned NULL
```
И добавим внешние ключи
```sql
ALTER TABLE `goods`
ADD FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
```
```sql
ALTER TABLE `goods`
ADD FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
```

Наполним таблицу "category" данными:
```sql
INSERT INTO `category` (`title`) VALUES ('Велосипеды');
INSERT INTO `category` (`title`) VALUES ('Покрышки');
INSERT INTO `category` (`title`) VALUES ('Камеры');
INSERT INTO `category` (`title`) VALUES ('Шлемы');
INSERT INTO `category` (`title`) VALUES ('Световые приборы');
INSERT INTO `category` (`title`) VALUES ('Бутылки и питьевые системы');
INSERT INTO `category` (`title`) VALUES ('Сумки');
INSERT INTO `category` (`title`) VALUES ('Куртки');
INSERT INTO `category` (`title`) VALUES ('Джерси');
INSERT INTO `category` (`title`) VALUES ('Шорты');

```

Наполним таблицу "brands" данными:

```sql
INSERT INTO `brands` (`brand`) VALUES ('Shimano');
INSERT INTO `brands` (`brand`) VALUES ('Hope');
INSERT INTO `brands` (`brand`) VALUES ('Kona');
INSERT INTO `brands` (`brand`) VALUES ('Author');
INSERT INTO `brands` (`brand`) VALUES ('Maloja');
INSERT INTO `brands` (`brand`) VALUES ('Michelin');
INSERT INTO `brands` (`brand`) VALUES ('Mongoose');
INSERT INTO `brands` (`brand`) VALUES ('Nike');
```

Как видно, речь идет о велосипедной тематике. Однако, нет никакого желания заниматься подбором каких то рельных данных,
для наполнения основной таблицы `goods`. Задача ведь не в этом. Вновь воспользуемся нашим скриптом, который нагенерирует
нам данные в случайном порядке. Добавим в скрипт новые поля, ну и уточним лишь цену на велосипеды должна генерироваться
более высокая цена, чем на комплектующие и аксессуары.
Всего у нас в таблице категории 10 записей, а в брендах 8. Ну и в таблицу товаров добавим 500 товаров. По прежнему ключевой
будет функция rand().

Проверим работу внешних ключей:
Попробуем удалить запись в таблице category
```sql
DELETE FROM `category`
WHERE ((`id` = '4'));
```
В ответ плучаем:
Cannot delete or update a parent row: a foreign key constraint fails (`dba`.`goods`, CONSTRAINT `goods_ibfk_3`
FOREIGN KEY (`brand_id`) REFERENCES `category` (`id`) ON UPDATE CASCADE).


```sql
UPDATE `brands` SET
`id` = '10'
WHERE `id` = '1';
```
Смотрим в таблицу `goods` о видим, что значение 1 в поле brand_id было заменено на 10.
Вернем обратно.
```sql
UPDATE `brands` SET
`id` = '1'
WHERE `id` = '10';
```

Внешние ключи работают.

Выполняем запросы:
1. Выберем все товары с указанием их категории и бренда.
```sql
SELECT * FROM `goods` INNER JOIN `category` ON `goods`.`category_id` = `category`.`id`
                      INNER JOIN `brands` ON `goods`.`brand_id` = `brands`.`id`  ;
```
Всё получилось, была сформирована единая таблица, где к товарам вывелись все категории и бренды. Всего 500 записей.
Ну и с LEFT JOIN, хотя в нашем случае получиться ровно такой же результат, но в большинстве случаев он здорово выручает
```sql
SELECT * FROM `goods` LEFT JOIN `category` ON `goods`.`category_id` = `category`.`id`
                      INNER JOIN `brands` ON `goods`.`brand_id` = `brands`.`id`  ;
```
Всё ожидаемо.

2. Выберем все товары, бренд которых начинается на букву "А"
```sql
SELECT * FROM `goods` INNER JOIN `category` ON `goods`.`category_id` = `category`.`id`
                      INNER JOIN `brands` ON `goods`.`brand_id` = `brands`.`id`
                      WHERE `brands`.`brand` LIKE 'A%'
                      ;
```
Получили 71 товар с брендом Author. Собственно у меня в наличии только один бренд, начинающийся с буквы A.

3.Выведем список категорий и число товаров в каждой (используйте подзапросы и функцию COUNT(), использовать группировку нельзя)
```sql
SELECT `category`.`title`,
    (
        SELECT count(*) FROM `goods`
        WHERE `goods`.`category_id` = `category`.`id`
    ) AS `count`
FROM `category`;
```
4. Выберем для каждой категории список брендов товаров, входящих в нее:
```sql

```