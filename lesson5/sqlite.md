# Sqlite

## Урок 5

Собственно по части SqLite никакого "наследия" с прошлых домашек у нас не осталось, поэтому создадим всё вновь. Используем
запросы из mysql, изменив их слегка под SqlIte:
```sql
CREATE TABLE `brands` (`id` integer NOT NULL PRIMARY KEY AUTOINCREMENT, `brand` varchar(100));

CREATE TABLE `category` (`id` integer NOT NULL PRIMARY KEY AUTOINCREMENT, `title` varchar(100));

CREATE TABLE `goods` (  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT,
                        `title` varchar(255),
                        `vendor_code` char(11) NOT NULL,
                        `image_url` varchar(255),
                        `price` integer unsigned NOT NULL,
                        `old_price` integer unsigned NULL,
                        `warehouse_date` datetime,
                        `quantity` integer,
						`category_id` integer,
						`brand_id` integer,
                        FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
                        FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
                        );
```

Вставим даные в таблицы brands и categories:
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

INSERT INTO `brands` (`brand`) VALUES ('Shimano');
INSERT INTO `brands` (`brand`) VALUES ('Hope');
INSERT INTO `brands` (`brand`) VALUES ('Kona');
INSERT INTO `brands` (`brand`) VALUES ('Author');
INSERT INTO `brands` (`brand`) VALUES ('Maloja');
INSERT INTO `brands` (`brand`) VALUES ('Michelin');
INSERT INTO `brands` (`brand`) VALUES ('Mongoose');
INSERT INTO `brands` (`brand`) VALUES ('Nike');
```
Вставим данные в таблицу goods. На этот раз я импортировал данные в формате csv.

`1`. Делаем запрос, который выберет категории и среднюю цену товаров в каждой категории, при условии, что эта средняя
цена менее 50000 рублей (выбираем "бюджетные" категории товаров).
```sql
SELECT `category`.`title`,
AVG(`goods`.`price`) AS `middle_price`
FROM `category`
INNER JOIN `goods` ON `goods`.`category_id` = `category`.`id`
GROUP BY `category`.`title`
HAVING `middle_price` < 50000000
```
Сработало ожидаемо. 4 категории со средней ценой.
`2`.Улучшим предыдущий запрос таким образом, чтобы в расчет средней цены включались только товары, имеющиеся на складе.
```sql
SELECT `category`.`title`,
AVG(`goods`.`price`) AS `middle_price`
FROM `category`
INNER JOIN `goods` ON `goods`.`category_id` = `category`.`id`
WHERE `goods`.`quantity` > 0
GROUP BY `category`.`title`
HAVING `middle_price` < 50000000
```