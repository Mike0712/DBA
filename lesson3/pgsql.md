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
Ну и наполняем данными, при помощи скрипта, выбирая пункт меню для наполнения базы PostgreSql.
