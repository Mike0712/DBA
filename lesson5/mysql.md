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

