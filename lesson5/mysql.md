# MySql

## Урок 5.

`1`. Делаем запрос, который выберет категории и среднюю цену товаров в каждой категории, при условии, что эта средняя
цена менее 1000 рублей (выбираем "бюджетные" категории товаров). Честно говоря, не уверен, что у меня есть категории
со средней ценой 1000 рублей, скорее всего придется корректировать это значение.
Для начала выведем все категории с указанием их средней цены - `middle_price`

```sql
SELECT `category`.`title`,
AVG(`goods`.`price`) AS `middle_price`
FROM `category`
INNER JOIN `goods` ON `goods`.`category_id` = `category`.`id`
GROUP BY `category`.`title`
```
Действительно, вевелось 10 категорий со средней ценой от 45390325.5349 - 58460536.6757 копеек. Вот такой у меня элитный
магазин. Значит актуальным для выборки значением будет цифра 50 тыс. рублей, т.е. 50000000 копеек.
Сделаем эту выборку. Здесь есть один нюанс. Мы не можем написать в предикат WHERE сравнение вроде `middle_price` <50000000
или AVG(`goods`.`price`) 50000000, будет ошибка.
Для сравнения строки `middle_price` используем конструкцию HAVING, которая по иерархии в запросе идет после GROUP BY
```sql
SELECT `category`.`title`,
AVG(`goods`.`price`) AS `middle_price`
FROM `category`
INNER JOIN `goods` ON `goods`.`category_id` = `category`.`id`
GROUP BY `category`.`title`
HAVING `middle_price` < 50000000
```
Система выдала 4 категории с указанием их средней цены.
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
У нас осталось только 3 категории и в некоторых из них средняя цена также изменилась, значит наша выборка работает.

`3`. Добавим к таблице брендов класс бренда (A, B, C).
```sql
ALTER TABLE `brands`
ADD `class` varchar(100) NULL;
```

Заполним пустые ячейки:
```sql
UPDATE `brands` SET
`class` = 'A'
WHERE `brand` = 'Shimano';

UPDATE `brands` SET
`class` = 'A'
WHERE `brand` = 'Michelin';

UPDATE `brands` SET
`class` = 'A'
WHERE `brand` = 'Nike';

UPDATE `brands` SET
`class` = 'B'
WHERE `brand` = 'Kona';

UPDATE `brands` SET
`class` = 'B'
WHERE `brand` = 'Mongoose';

UPDATE `brands` SET
`class` = 'C'
WHERE `brand` = 'Author';

UPDATE `brands` SET
`class` = 'C'
WHERE `brand` = 'Hope';

UPDATE `brands` SET
`class` = 'C'
WHERE `brand` = 'Maloja';
```

Напишем запрос, который для каждой категории и класса брендов, представленных в категории выберет среднюю цену товаров.

```sql
SELECT `category`.`title`,
    (SELECT AVG(`goods`.`price`) FROM `goods`, `brands`
         WHERE `goods`.`category_id` = `category`.`id`
         AND `goods`.`brand_id` = `brands`.`id`) AS commom_middle_price,

    (SELECT AVG(`goods`.`price`) FROM `goods`, `brands`
        WHERE `goods`.`category_id` = `category`.`id`
        AND `goods`.`brand_id` = `brands`.`id`
        AND `brands`.`class` = 'A') AS `A`,

    (SELECT AVG(`goods`.`price`) FROM `goods`, `brands`
        WHERE `goods`.`category_id` = `category`.`id`
        AND `goods`.`brand_id` = `brands`.`id`
        AND `brands`.`class` = 'B') AS `B`,

    (SELECT AVG(`goods`.`price`) FROM `goods`, `brands`
        WHERE `goods`.`category_id` = `category`.`id`
        AND `goods`.`brand_id` = `brands`.`id`
        AND `brands`.`class` = 'C') AS `C`
FROM `category`
```
Запрос несколько адовый, но как добиться такого результата без использования подзапросов, я не очень понимаю.

Добавим к БД таблицу заказов:
```sql
CREATE TABLE `orders` (`id` serial, `date` datetime, `good_id` int);
```
Вставим несколько строк в неё:
```sql
INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 14:00', 301);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 14:30', 7);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 14:40', 217);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 14:50', 47);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 15:01', 143);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 15:10', 13);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 15:11', 10);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 15:15', 313);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 15:30', 217);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 15:40', 411);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 15:43', 37);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 15:54', 11);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 15:57', 202);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 16:01', 14);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 16:10', 333);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 16:20', 201);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 16:27', 47);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 17:00', 214);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 17:06', 31);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-03 18:02', 58);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 11:07', 49);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 11:14', 31);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 11:43', 67);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 11:57', 203);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 12:09', 41);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 12:37', 135);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 12:57', 178);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 14:01', 69);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 14:37', 17);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 14:56', 15);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 15:43', 11);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 16:09', 54);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 16:27', 208);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 17:11', 301);

INSERT INTO `orders` (`date`,`good_id`)
VALUES ('2016-10-04 17:43', 29);
```
Напишем запрос, который выведет таблицу с полями "дата", "число заказов за дату", "сумма заказов за дату":
```sql
SELECT DATE_FORMAT(`orders`.`date`, '%Y-%m-%d') AS dat, count(`date`) AS quantity, SUM(`goods`.`price`) AS total
FROM `orders`
INNER JOIN `goods` ON `goods`.`id` = `orders`.`good_id`
GROUP BY dat
```