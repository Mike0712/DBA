# Postgres

## Урок 7.

1. Установить ограничения на таблицу товаров.

Итак наша legacy таблица goods (товары), имеет следующую структуру:

|Колонка         |Тип                                             |
|----------------|------------------------------------------------|
| id             | integer autoincrement [nextval('goods_id_seq')]|
| title          | character varying(255) NULL                    |
| vendor_code    | character(9)                                   |
| image_url      | character varying(255) NULL                    |
| price          | integer                                        |
| old_price      | integer NULL                                   |
| warehouse_date | timestamptz NULL [now()]                       |
| quantity       | smallint NULL                                  |
| category_id    | integer NULL                                   |
| brand_id       | integer NULL                                   |

Проверим, а не установлены ли ранее какие либо ограничения. Стоит отметить, что, первичные и внешние ключи также являются
CONSTRAINT - ограничение. Но для данного задания нас будет интересовать только тип CONSTRAINT - Check.

 Переходим во вкладку "зависимые" и видим что на поля
"price" и "old_price" таблицы уже были установлены ограничения типа Check:

| Тип   | Имя                          | Ограничение|
|-------|------------------------------|------------|
| Check	| public.goods_old_price_check | auto       |
| Check	| public.goods_price_check	   | auto       |
| Check	| public.goods_old_price_check | normal     |
| Check	| public.goods_price_check	   | normal     |

 Для данного задания такой вариант не подойдет, т.к. мы будем как раз устанавливать ограничения типа check на отдельные
 колонки, поэтому перед началом выполнения задания, такого рода ограничения должны быть подчищены:

 ```sql
 ALTER TABLE "goods"
 DROP CONSTRAINT goods_price_check

 ALTER TABLE "goods"
 DROP CONSTRAINT goods_old_price_check
 ```

Все мы избавились от лишенего наследия, можно приступать к выполнению задания:
1)
1 Установим ограничения на цены товаров:

В первую очередь установим ограничения на поле "price". Данное поле может содержать только положительное значение.
Кроме того, можно было бы указать что цена >= 0, но она 0 быть не должна. Сделано это намеренно, так как таблица goods
является витриной магазина и для борьбы с нерадивым товароведом, который выставляя товары и не зная их цены мог бы написать 0.
И кроме того в этом поле у меня не установлено какого бы то ни было значение по умолчанию.
Всё это в совокупности гарантирует, что товары будут выставляться с их действительной ценой или вообще не выставляться.
Итак, запрос:

 ```sql
 ALTER TABLE "goods"
 ADD CONSTRAINT goods_price_check CHECK (price > 0);
 ```

Для старой цены все немного проще, по умолчанию она NULL, поэтому задаём ограничения, что она не должна быть отрицательной
или нулевой.

```sql
 ALTER TABLE "goods"
 ADD CONSTRAINT goods_old_price_check CHECK (old_price > 0);
```

Теперь вставка в любое из этих полей отрицательного или нулевого значения немедленно вызовет ошибку.

Ну и добавим условие о том, что новая цена не должна быть больше старой, так как с точки зрения маркетинга это ошибка.

```sql
 ALTER TABLE "goods"
 ADD CONSTRAINT new_less_old_check CHECK (price <= old_price);
```

Хотелось бы в целях закрепления материала одного из прошлых уроков обратить внимание на то, что этот запрос был выполнен
без ошибок. Дело в том, что в некоторых записях таблицы в поле "old_price" установлено значение NULL. Это значение появилось
там волею PHP скрипта, которым я наполнял таблицу в одной из прошлых домашек. Это поле как и price заполнялось при помощи
функции PHP Rand() и у скрипта была задача проверять, чтобы в это поле не устанавливалась меньшаяя по сравнению с полем
price цена, иначе NULL.
Так вот, Postgress не выдал нам какой-либо ошибки, т.к. значение NULL не подлежит сравнению с чем либо оно всегда <>
всему.

Попробуем поменять значение какой-либо записи таблицы:

```sql
UPDATE "goods"
SET "old_price" = "price" -1
WHERE "id" = 1552
```

Получаем ошибку:
```
ERROR:  new row for relation "goods" violates check constraint "new_less_old_check"
DETAIL:  Failing row contains (1552, Товар51, 00357    , /images/good51, 63716915, 63716914, 2016-08-18 05:39:12+00, 38, 3, 7)
```
И ещё запросы:
```sql
UPDATE "goods"
SET "price" = -1
WHERE "id" = 1670
```

```
ERROR:  new row for relation "goods" violates check constraint "goods_price_check"
DETAIL:  Failing row contains (1670, Товар169, test01183, /images/good169, -1, null, 2016-09-11 09:16:07+00, 25, 6, 2).
```

```sql
UPDATE "goods"
SET "old_price" = 0
WHERE "id" = 1789
```

```
ERROR:  new row for relation "goods" violates check constraint "goods_old_price_check"
DETAIL:  Failing row contains (1789, Товар288, 02016    , /images/good288, 36353306, 0, 2016-07-05 06:40:12+00, 12, 9, 6).
```

2 Ограничение на артикулы.
В принципе на поле vendor_code (артикул) уже стоит одно ограничение типа Unique. Ну и кроме того есть ограничение для по
количеству символов - char(9). Пожалуй мы ещё можем ограничить в добавлении символов. Сделаем чтобы в это поле было разрешено 
добавлять только числовые значения, буквенные значения на латинице и нижнее подчеркивание.
Для этого нам потребуется составить регулярное выражение и добавить его в запрос.

 ```sql
 ALTER TABLE "goods"
  ADD CONSTRAINT "vendor_validator" CHECK ("vendor_code" ~ '[A-Za-z0-9_]');
 ```
Теперь попытка ввести запрос вроде:
```sql
UPDATE "goods"
SET "vendor_code" = 'Вася.\-'
WHERE "id" =1689
```
Вызовет вот такую вот ошибку:
```
ERROR:  new row for relation "goods" violates check constraint "vendor_validator"
DETAIL:  Failing row contains (1689, Товар1689, Вася.\-  , /images/good1689, 84180320, null, 2016-07-06 06:58:39+00, 7).
```


3 Ограничение на поле "есть на складе".
Собственно говоря в моей таблице соответствующей колонки нет. Но это легко исправить.
```sql
ALTER TABLE "goods"
ADD "presence" boolean DEFAULT 'false';
```
Мы вынуждены были задать значение по умолчанию, чтобы не получить ошибку, т.к. записи в таблице у нас уже есть.
Данное поле напрямую зависит от значения поля quantity, и наверное здесь неплохо было бы написать тригер. Но для начала
хотя бы нужно просто "синхронизировать" это поле с полем quantity:

```sql
UPDATE "goods"
SET "presence" = true
WHERE "quantity" > 0
```

Несмотря на булев тип, на данный момент в поле presence можно записать 3 значения - true, false и NULL.
Исправим это:

```sql
 ALTER TABLE "goods"
 ALTER "presence" SET NOT NULL;
```
Теперь в поле "presence" можно добавить либо true либо false

2) Установим ещё несколько ограничений:
1 Ограничение на количество товара, оно так же не может быть отрицательным, но тем не менее может быть равным 0.

 ```sql
 ALTER TABLE "goods"
  ADD CONSTRAINT "quantity_not_subzero" CHECK ("quantity" >= 0);
 ```

 Введём запрос:
 ```sql
 UPDATE "goods"
 SET "quantity" = -1
 WHERE "id" = 1557
 ```
Текст ошибки:
```
ERROR:  new row for relation "goods" violates check constraint "quantity_not_subzero"
DETAIL:  Failing row contains (1557, Товар56, 00392    , /images/good56, 89632970, null, 2016-07-16 01:04:06+00, -1, 3, f, null)
```
2 Введем ограничение на поле image_url. Картинки могут браться только из папки /image. Допустим мы не хотим чтобы в это поле
мог прийти внешний url, все картинки, используемые в приложении должны храниться на сервере.
```sql
 ALTER TABLE "goods"
  ADD CONSTRAINT "inner_url" CHECK ("image_url" ~ '^/images/');
```
Проеверяем:

```sql
 UPDATE "goods"
 SET "image_url" = 'https://www.instagram.com/user1231/images/superfoto.png'
 WHERE "id" = 1557
```
Наше ограничение отлично сработало:

```
ERROR:  new row for relation "goods" violates check constraint "inner_url"
DETAIL:  Failing row contains (1557, Товар56, 00392    , https://www.instagram.com/user1231/images/superfoto.png, 89632970, null, 2016-07-16 01:04:06+00, 0, 3, f, null)
```

3)
Перепроектируем таблицу товаров, используя поле categories bigint[]:

Просто преобразовать тип integer на bigint[] не удалось т.к. данное поле таблицы не пустое. Тогда удалим эту колонку
и создадим новую уже с нужным нам значением

```sql
ALTER TABLE goods DROP COLUMN category_id;

ALTER TABLE "goods"
ADD COLUMN "category_id" bigint[];
```
Данная структура нарушает не только первую нормальную форму, но и всю логику нашего скрипта, который наполняет таблицу
данными.
Но мы не будем заполнять данное поле в каждой записи, а сделаем это только для 10 записей:

```sql
UPDATE "goods"
SET "category_id" = '{1,5,7,9}'
WHERE id = 1528;

UPDATE "goods"
SET "category_id" = '{3,4}'
WHERE id = 1672;

UPDATE "goods"
SET "category_id" = '{7,8,9,10}'
WHERE id = 1824;

UPDATE "goods"
SET "category_id" = '{1,7,10}'
WHERE id = 1707;

UPDATE "goods"
SET "category_id" = '{1,5,6,8}'
WHERE id = 1522;

UPDATE "goods"
SET "category_id" = '{1,7,9}'
WHERE id = 1544;

UPDATE "goods"
SET "category_id" = '{3,10}'
WHERE id = 1605;

UPDATE "goods"
SET "category_id" = '{3,5}'
WHERE id = 1597;

UPDATE "goods"
SET "category_id" = '{4,7,9}'
WHERE id = 1537;

UPDATE "goods"
SET "category_id" = '{7,8,9,10}'
WHERE id = 1824;
```

1 Строим запрос, выбирающий все товары из заданной категории. Например для категории под номером 3:
```sql
SELECT "goods"."title"
FROM "goods"
WHERE 3 = ANY("category_id")
```
Получаем 3 записи, что вполне логично.

2 Запрос, выбирающий все категории и количество товаров в каждой из них
```sql
SELECT "category"."title", COUNT("goods"."title") AS "quantity"
FROM "category"
LEFT JOIN "goods" ON "category"."id" = ANY ("goods"."category_id")
GROUP BY "category"."title"
```
Получаем вот такую симпотичную таблицу:


| title                     |	quantity |
|---------------------------|------------|
| Джерси                    |	   1     |
| Световые приборы	        |      0     |
| Куртки	                |      3     |
| Шорты	                    |      5     |
| Велосипеды	            |      2     |
| Шлемы	                    |      4     |
| Сумки	                    |      2     |
| Камеры	                |      3     |
| Покрышки	                |      4     |
| Бутылки и питьевые системы|	   3     |

Всего одна категоря не имеет товаров.
Правда никак не укладывается в голове, как какой-либо товар может относиться более чем к одной категории из представленных.
Видимо у нас магазин экзотических товаров. К примеру товар - велосипед-шорты.


3 Запрос, добавляющий определенный товар в определенную категорию.
Например, у нас есть товар с id = 1544. О уже относится к категориям 1,7,9.
Допустим нам поступила задача добавить этот товар ещё и к категории под номером 4.
Самый очевидный вариант просто переписать это поле, добавив в фигурные скобки через запятую ещё один адишник категории.
Но это и самый громоздкий способ. Ведь у насв поле с [] - образным типом  может быть сколько угодно значений, и что каждый
раз переписывать.
Гораздо логичнее воспользоваться функцией array_append(), которая добавит нам новое значение в конец массива
```sql
UPDATE "goods"
SET "category_id" = array_cat("category_id", '{4}')
WHERE id = 1544;
```
Проверяем результат
```sql
SELECT "category_id" FROM "goods" WHERE "id" = '1544';
```
Получаем значение: {1,7,9,4}.

