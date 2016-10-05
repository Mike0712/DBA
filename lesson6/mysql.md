# MySql

## Урок 6.

Создадим таблицу `history`, которая будет впоследствии следить за таблицей goods:

```sql
CREATE TABLE `history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `goods_id` bigint unsigned,
  `action` enum('create', 'update', 'delete'),
  `price` int,
  `old_price` int,
  `datetime` timestamp
) ENGINE='InnoDB';
```
Создадим первый тригер new_row на событие добавление записи в таблицу goods:

```sql
DELIMITER $$
CREATE TRIGGER `new_row` AFTER INSERT ON `goods`
FOR EACH ROW
BEGIN
INSERT INTO `history` SET `goods_id` = NEW.id, `action` = 'create', `price` = NEW.price, `datetime` = NOW();
END;
DELIMITER ;
```

Добавим новую запись в таблицу `goods`:

```sql
INSERT INTO `goods` (`title`, `vendor_code`, `image_url`, `price`, `old_price`, `warehouse_date`, `quantity`, `category_id`, `brand_id`)
VALUES ('Товар 501', '12435363', '/images/good501', '1231214657', NULL, now(), '45', '4', '6');
```
Смотрим в таблицу history и видим там новую запись:
|id |goods_id|action | price     | old_price  |datetime           |
|---|--------|-------|-----------|------------|-------------------|
| 1 | 501    | create| 1231214657|   NULL     |2016-10-05 22:39:31|
|   |        |       |           |            |                   |
|   |        |       |           |            |                   |

Работает!

Создадим еще триггер, на этот раз на событие обновления записи:
```sql
DELIMITER $$
CREATE TRIGGER `edit_row` AFTER UPDATE ON `goods`
FOR EACH ROW
BEGIN
INSERT INTO `history` SET `goods_id` = OLD.id, `action` = 'update', `price` = NEW.price, `old_price` = OLD.price, `datetime` = NOW();
END;
DELIMITER ;
```

```sql
UPDATE `goods` SET
`price` = '62343987'
WHERE `title` = 'Товар25';
```
И видим в нашей таблице "прибавление":

|id |goods_id|action | price     | old_price  |datetime           |
|---|--------|-------|-----------|------------|-------------------|
| 1 | 501    | create| 1231214657|   NULL     |2016-10-05 22:39:31|
| 2 | 25     | update| 62343987  |  67423987  |2016-10-05 23:17:29|
|   |        |       |           |            |                   |
|   |        |       |           |            |                   |

Ну и привяжем действие при событии удаления записи:
```sql
DELIMITER $$
CREATE TRIGGER `delete_row` AFTER DELETE ON `goods`
FOR EACH ROW
BEGIN
INSERT INTO `history` SET `goods_id` = OLD.id, `action` = 'delete', `datetime` = NOW();
END;
DELIMITER ;
```

Дропним запись в таблице goods:
```sql
DELETE FROM `goods`
WHERE ((`id` = '501'));
```
Наша таблица:
|id |goods_id|action | price     | old_price  |datetime           |
|---|--------|-------|-----------|------------|-------------------|
| 1 | 501    | create| 1231214657|  NULL      |2016-10-05 22:39:31|
| 2 | 25     | update| 62343987  |  67423987  |2016-10-05 23:17:29|
| 3 | 501    | delete| NULL      |  NULL      |2016-10-05 23:31:29|
|   |        |       |           |            |                   |
|   |        |       |           |            |                   |

Ну и для закрепления темы изменим последний триггер, так чтобы он выводил и
цену чтобы скучно не было:

```sql
DROP TRIGGER `delete_row`;

DELIMITER $$
CREATE TRIGGER `delete_row` AFTER DELETE ON `goods`
FOR EACH ROW
BEGIN
INSERT INTO `history` SET `goods_id` = OLD.id, `action` = 'delete', `price`= OLD.price, `datetime` = NOW();
END;
DELIMITER ;
```

Удалим из таблицы ещё одну запись
```sql
DELETE FROM `goods`
WHERE ((`id` = '431'));
```
Проверяем таблицу history:
|id |goods_id|action | price     | old_price  |datetime           |
|---|--------|-------|-----------|------------|-------------------|
| 1 | 501    | create| 1231214657|  NULL      |2016-10-05 22:39:31|
| 2 | 25     | update| 62343987  |  67423987  |2016-10-05 23:17:29|
| 3 | 501    | delete| NULL      |  NULL      |2016-10-05 23:31:29|
| 4 | 431    | delete| 57437091  |  NULL      |2016-10-05 23:42:53|
|   |        |       |           |            |                   |
|   |        |       |           |            |                   |