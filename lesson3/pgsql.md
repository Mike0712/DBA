# MySql

## Урок 3.

## Создаём таблицу c названием goods, также без индексов:

```sql
CREATE TABLE "goods" (  "id" serial,
                        "title" varchar(255),
                        "vendor_code" char(9) NOT NULL,
                        "image_url" varchar(255),
                        "price" integer NOT NULL CHECK("price" >= 0),
                        "old_price" integer NULL CHECK("old_price" > 0),
                        "warehouse_date" timestamptz DEFAULT now(),
                        "quantity" smallint);
```
Как видим, тип serial не создает каких либо ключей по умолчанию, как в mysql. Последовательность + автоинкремент
уже гарантрует уникальность значения этого поля.

Создаём первичный ключ для поля id:
```sql
ALTER TABLE "goods"
ADD CONSTRAINT "goods_id" PRIMARY KEY ("id");
```
и уникальный для артикула:
```sql
ALTER TABLE "goods"
ADD CONSTRAINT "goods_vendor_code" UNIQUE ("vendor_code");
```
Ну и наполняем данными, при помощи скрипта, выбирая пункт 'Вставить данные для PostgreSql'.
Итак, 1000 записей было добавлено в таблицу.
Выполняем запросы.

`1.` Выбираем 10 самых новых товаров:
```sql
SELECT * FROM "goods" ORDER BY "warehouse_date" DESC LIMIT 10
```
Запрос занял (0.003 s)
`2`. Выбираем 10 самых дешевых товаров
```sql
SELECT * FROM "goods" ORDER BY "price" LIMIT 10
```
Запрос занял (0.003 s)
`3`. Выбираем 10 товаров, цена на которых была максимально снижена (в абсолютном или относительном смысле)

`4`. Выбираем те товары, чей артикул начинается с символов "test"


