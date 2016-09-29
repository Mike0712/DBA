# PostgreSql

## Урок 4.

Очистим таблицу "goods", поскольку в ней появятся новые поля, а мы будем использовать таблицы с помощью скрипта
```sql
TRUNCATE TABLE "goods"
```
Создадим в таблице новые поля:
```sql
ALTER TABLE "goods"
ADD "category_id" integer NULL;
```

```sql
ALTER TABLE "goods"
ADD "brand_id" integer NULL;
```

Далее создаём таблицы
```sql
CREATE TABLE "category" ("id" serial, "title" varchar(100), CONSTRAINT "category_id" PRIMARY KEY ("id"));
```


```sql
CREATE TABLE "brands" ("id" serial, "brand" varchar(100), CONSTRAINT "brand_id" PRIMARY KEY ("id"));
```

Добавим внешние ключи в таблицу "goods"
```sql
ALTER TABLE "goods"
ADD FOREIGN KEY ("category_id") REFERENCES "category" ("id")
```
```sql
ALTER TABLE "goods"
ADD FOREIGN KEY ("brand_id") REFERENCES "brands" ("id")
```
Теперь попробуем вставить какую-нибудь запись в таблицу goods.
```sql
 INSERT INTO "goods" ("title", "vendor_code", "image_url", "price", "old_price", "warehouse_date", "quantity", "category_id", "brand_id")
 VALUES ('rgrtg', '53636', 'dfbdnhg', '36377', '33333', 'now()', '1', '1', '1');
```
И получаем ошибку:

ERROR: insert or update on table "goods" violates foreign key constraint "goods_category_id_fkey"
DETAIL: Key (category_id)=(1) is not present in table "category"

Как видим, сработало ограничение. Мы не можем добавлять что-либо в поля таблицы, если они связаны внешними ключами с
другими таблицами, до тех пор пока в этих таблицах не появятся соответствующие записи.

Вставим в таблицу "category" данными:
```sql
INSERT INTO "category" ("title") VALUES ('Велосипеды');
INSERT INTO "category" ("title") VALUES ('Покрышки');
INSERT INTO "category" ("title") VALUES ('Камеры');
INSERT INTO "category" ("title") VALUES ('Шлемы');
INSERT INTO "category" ("title") VALUES ('Световые приборы');
INSERT INTO "category" ("title") VALUES ('Бутылки и питьевые системы');
INSERT INTO "category" ("title") VALUES ('Сумки');
INSERT INTO "category" ("title") VALUES ('Куртки');
INSERT INTO "category" ("title") VALUES ('Джерси');
INSERT INTO "category" ("title") VALUES ('Шорты');

```

Наполним таблицу "brands" данными:

```sql
INSERT INTO "brands" ("brand") VALUES ('Shimano');
INSERT INTO "brands" ("brand") VALUES ('Hope');
INSERT INTO "brands" ("brand") VALUES ('Kona');
INSERT INTO "brands" ("brand") VALUES ('Author');
INSERT INTO "brands" ("brand") VALUES ('Maloja');
INSERT INTO "brands" ("brand") VALUES ('Michelin');
INSERT INTO "brands" ("brand") VALUES ('Mongoose');
INSERT INTO "brands" ("brand") VALUES ('Nike');
```

Запустим скрипт для заполнения таблицы goods.

Проверим работу внешних ключей:
Попробуем удалить запись в таблице brands
```sql
DELETE FROM "brands"
WHERE (("id" = '8'));
```
В ответ плучаем:
Cannot delete or update a parent row: a foreign key constraint fails (`dba`.`goods`, CONSTRAINT `goods_ibfk_3`
FOREIGN KEY (`brand_id`) REFERENCES `category` (`id`) ON UPDATE CASCADE).

Пробуем изменить
```sql
UPDATE "brand" SET
"id" = '8'
WHERE "id" = '9';
```
И получаем:
 ERROR: update or delete on table "brands" violates foreign key constraint "goods_brand_id_fkey" on table "goods"
 DETAIL: Key (id)=(8) is still referenced from table "goods".

Внешние ключи работают.

Но необходимо добавить экшны - запрет на удаление и каскад на обновление
```sql
ALTER TABLE "goods"
DROP CONSTRAINT "goods_brand_id_fkey",
ADD FOREIGN KEY ("brand_id") REFERENCES "brands" ("id") ON DELETE RESTRICT ON UPDATE CASCADE
```
```sql
ALTER TABLE "goods"
DROP CONSTRAINT "goods_category_id_fkey",
ADD FOREIGN KEY ("category_id") REFERENCES "category" ("id") ON DELETE RESTRICT ON UPDATE CASCADE
```

Выполняем запросы:
1. Выберем все товары с указанием их категории и бренда.
```sql
SELECT * FROM "goods" INNER JOIN "category" ON "goods"."category_id" = "category"."id"
                      INNER JOIN "brands" ON "goods"."brand_id" = "brands"."id"  ;
```

2. Выберем все товары, бренд которых начинается на букву "А"
```sql
SELECT * FROM "goods" INNER JOIN "category" ON "goods"."category_id" = "category"."id"
                      INNER JOIN "brands" ON "goods"."brand_id" = "brands"."id"
                      WHERE "brands"."brand" LIKE 'A%'
                      ;
```

3.Выведем список категорий и число товаров в каждой (используйте подзапросы и функцию COUNT(), использовать группировку нельзя)
```sql
SELECT "category"."title",
    (
        SELECT count(*) FROM "goods"
        WHERE "goods"."category_id" = "category"."id"
    ) AS "count"
FROM "category";
```

Получили следующий результат

title	count
Шлемы	42
Световые приборы	56
Бутылки и питьевые системы	52
Сумки	56
Куртки	46
Джерси	48
Шорты	48
Велосипеды	47
Покрышки	56
Камеры	50

4. Выберем для каждой категории список брендов товаров, входящих в нее:
```sql

```