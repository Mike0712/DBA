#PostgreSql

## Урок 2.

## Создаём таблицу c названием goods:

```sql
    CREATE TABLE "goods" ("id" serial, "title" varchar(255),
    "description" text,
    "short_annotation" varchar(127),
    "vendor_code" char(8) NOT NULL,
    "image_url" varchar(255),
    "price" numeric(8,2) NOT NULL CHECK("price" > 0),
    "sales_start_day" timestamp,
    "quantity" smallint CHECK ("quantity" >= 0),
    "exists" boolean DEFAULT false);
```
Основные отличия от других БД: Вместо decimal в MySql используем numeric. Хотя в MySql тоже есть numeric синоним decimal,
однако в PostreeSql нет типа decimal - синонима numeric. Кроме того, в PostreeSql нет директивы unsigned для ограничения
целочисленных значений, поэтому мы задаём это явно. При этом у нас значение не может быть равно и 0, что в данном случае
оправданно. Например владелец магазина решил бороться с разгильдяйством, например когда ответственные зы выгрузку информации
о товарах люди не заморачиваются и пишут вместо цены 0. Напротив в колонке количество может быть 0, что вполне логично,
поэтому >=, т.е. аналог unsigned.
Для поля short_annotation аналога tinytext в Pgsql нет, поэтому будем использовать тип varchar(127), т.е такая же длина как
и у tinytext (для кириллицы).

