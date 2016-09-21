#PostgreSql

## Урок 2.

## Создаём таблицу c названием goods:
`Основные отличия от других БД: Вместо decimal в MySql используем numeric. Хотя в MySql тоже есть numeric синоним decimal,
однако в PostreeSql нет типа decimal - сиснонима numeric. Кроме того, в PostreeSql нет директивы unsigned для ограничения
целочисленных значений, поэтому мы задаём это явно.`
```sql
    CREATE TABLE "goods" ("id" serial, "title" varchar(255),
    "description" text,
    "short_annotation" varchar(127),
    "vendor_code" char(8) NOT NULL,
    "image_url" varchar(255),
    "price" numeric(8,2) NOT NULL CHECK("price" > 0),
    "sales_start_day" timestamp,
    "quantity" smallint CHECK ("quantity" > 0),
    "exists" boolean DEFAULT false);
```
