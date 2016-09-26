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
Итак, 1000 записей было добавлено в таблицу.
Выполняем запросы.

`1.` Выбираем 10 самых новых товаров:
```sql
SELECT * FROM "goods" WHERE "warehouse_date" > ORDER BY "warehouse_date" DESC LIMIT 10
```
Total query runtime: 25 msec
Делаем EPLAIN запроса, получаем:

"Limit  (cost=44.58..44.60 rows=10 width=60)"
"  ->  Sort  (cost=44.58..47.08 rows=999 width=60)"
"        Sort Key: warehouse_date DESC"
"        ->  Seq Scan on goods  (cost=0.00..22.99 rows=999 width=60)"
Вешаем индекс на поле "warehouse_date":
```sql
CREATE INDEX "goods_warehouse_date" ON "goods" ("warehouse_date");
```
Повторяем запрос:
Total query runtime: 100 msec. Т.е. время на выполнение запроса даже увеличилось.
Делаем EXPLAIN и получаем в ответ:
```sql
"Limit  (cost=0.28..1.15 rows=10 width=60)"
"  ->  Index Scan Backward using goods_warehouse_date on goods  (cost=0.28..87.24 rows=999 width=60)"
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
"Limit  (cost=0.28..1.15 rows=10 width=60)"
"  ->  Index Scan using goods_price on goods  (cost=0.28..87.26 rows=999 width=60)"

`3`. Выбираем 10 товаров, цена на которых была максимально снижена (в абсолютном или относительном смысле)
```sql
SELECT *, "old_price"-"price" AS "discount" FROM "goods" ORDER BY "old_price"-"price" DESC LIMIT 10
```
Total query runtime: 15 msec.
Explain:
```sql
"Limit  (cost=47.08..47.10 rows=10 width=60)"
"  ->  Sort  (cost=47.08..49.57 rows=999 width=60)"
"        Sort Key: ((old_price - price)) DESC"
"        ->  Seq Scan on goods  (cost=0.00..25.49 rows=999 width=60)"
```

`4`. Выбираем те товары, чей артикул начинается с символов "test"
```sql
SELECT * FROM "goods" WHERE "vendor_code" LIKE '%test%'
```
Запрос занял (0.003 s)

Делаем EXPLAIN запроса, получаем следующую информацию:

```sql
QUERY PLAN
Seq Scan on goods  (cost=0.00..25.49 rows=61 width=60)
  Filter: (vendor_code ~~ 'test%'::text)
```
