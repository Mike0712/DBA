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
Обратим внимание, что PostgreSql обрабатывает запрос несколько дольше, чем Mysql
Итак, более 33000 записей было добавлено в таблицу.
Выполняем запросы.

`1.` Выбираем 10 самых новых товаров:
```sql
SELECT * FROM "goods" ORDER BY "warehouse_date" DESC LIMIT 10
```
Total query runtime: 13 msec
Делаем EXPLAIN запроса, получаем:
```sql
Limit  (cost=1455.18..1455.21 rows=10 width=64)
  ->  Sort  (cost=1455.18..1537.93 rows=33097 width=64)
        Sort Key: warehouse_date DESC
        ->  Seq Scan on goods  (cost=0.00..739.97 rows=33097 width=64)
      ```  
Вешаем индекс на поле "warehouse_date":
```sql
CREATE INDEX "goods_warehouse_date" ON "goods" ("warehouse_date");
```
Повторяем запрос:
Total query runtime: 12 msec
```sql
Limit  (cost=0.29..1.05 rows=10 width=64)
  ->  Index Scan Backward using goods_warehouse_date on goods  (cost=0.29..2504.74 rows=33097 width=64)
```
Меняем наш индекс, делая сортировку по убыванию:
```sql
DROP INDEX "goods_warehouse_date";
CREATE INDEX "goods_warehouse_date" ON "goods" ("warehouse_date" DESC);
```
Повторяем запрос и получаем результат:
Total query runtime: 12 msec.
```sql
EXPLAIN SELECT * FROM "goods" ORDER BY "warehouse_date" DESC LIMIT 10

"Limit  (cost=0.28..1.15 rows=10 width=60)"
"  ->  Index Scan using goods_warehouse_date on goods  (cost=0.28..87.24 rows=999 width=60)"
```
Таким образом, для данного запроса оказалось важным чтобы направление сортировки в индексе совпадало с направлением
сортировки в запросе, тогда это даёт профит.
`2`. Выбираем 10 самых дешевых товаров
```sql
SELECT * FROM "goods" ORDER BY "price" LIMIT 10
```
Total query runtime: 13 msec

Вешаем индекс на поле price:
```sql
CREATE INDEX "goods_price" ON "goods" ("price");
```
Повторяем запрос:
Total query runtime: 12 msec
Эксплейним:
```sql
Limit  (cost=0.29..1.05 rows=10 width=64)
  ->  Index Scan using goods_price on goods  (cost=0.29..2504.74 rows=33097 width=64)
```

`3`. Выбираем 10 товаров, цена на которых была максимально снижена (в абсолютном или относительном смысле)
```sql
SELECT *, "old_price"-"price" AS "discount" FROM "goods" ORDER BY "old_price"-"price" DESC LIMIT 10
```
Total query runtime: 22 msec.
Explain:
```sql
QUERY PLAN
Limit  (cost=1537.93..1537.95 rows=10 width=64)
  ->  Sort  (cost=1537.93..1620.67 rows=33097 width=64)
        Sort Key: ((old_price - price)) DESC
        ->  Seq Scan on goods  (cost=0.00..822.71 rows=33097 width=64
```
Создаём индекс:
```sql
CREATE INDEX "goods_old_price" ON "goods" ("old_price");
```
Total query runtime: 22 msec.
Смотрим explain:

QUERY PLAN
Limit  (cost=1537.93..1537.95 rows=10 width=64)
  ->  Sort  (cost=1537.93..1620.67 rows=33097 width=64)
        Sort Key: ((old_price - price)) DESC
        ->  Seq Scan on goods  (cost=0.00..822.71 rows=33097 width=64

К сожалению данный индекс не сработал.


`4`. Выбираем те товары, чей артикул начинается с символов "test"

```sql
SELECT * FROM "goods" WHERE "vendor_code" LIKE 'test%'
```
Запрос занял (85 msec)

Делаем EXPLAIN запроса, получаем следующую информацию:

```sql
QUERY PLAN
Seq Scan on goods  (cost=0.00..25.49 rows=61 width=60)
  Filter: (vendor_code ~~ 'test%'::text)
```
Вешаем индекс:
```sql
CREATE INDEX "goods_vaendor_idx" ON "goods" ("vendor_code")
```

Повторяем запрос: 
Запрос занял (85 msec)
Смотрим EXPLAIN: 
```sql 
QUERY PLAN
Seq Scan on goods  (cost=0.00..822.71 rows=1003 width=64)
  Filter: (vendor_code ~~ 'test%'::text)
```


Признаюсь честно, тему работы индексов на PgSql этой домашкой я сам для себя не раскрыл. Полагаю, что виной тому
малое количество записей в таблице. К сожалению, пока не придумал способа быстро забить БД PostgreSql большим количеством
записей.
