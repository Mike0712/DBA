# Postgres

## Урок 8.

1 В таблице товаров создадим колонку типа jsonb.

```sql
ALTER TABLE "goods"
    ADD COLUMN "characteristic" jsonb;
```

В таблице у нас 500 записей (причем волею PHP скрипта самая ранняя запись имеет айдишник 1502). Заполним лишь некоторые
поля "characteristic".
О каком размере идет речь (одежды, обуви) понятия не имею, просто некая абстракция. Вес указан в граммах, специально под
условия задания.

 ```sql
 UPDATE goods
 SET
 characteristic = '{"color": "blue", "size":38, "weight":900}'::json
 WHERE "id" = 1502;

 UPDATE goods
 SET
 characteristic = '{"color": "green", "size":17, "weight":600}'::json
 WHERE "id" = 1503;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":45, "weight":2000}'::json
 WHERE "id" = 1504;

 UPDATE goods
 SET
 characteristic = '{"color": "yellow", "size":48, "weight":2200}'::json
 WHERE "id" = 1505;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":48, "weight":2200}'::json
 WHERE "id" = 1506;

 UPDATE goods
 SET
 characteristic = '{"color": "blue", "size":"XXL", "weight":1800}'::json
 WHERE "id" = 1507;

 UPDATE goods
 SET
 characteristic = '{"color": "green", "weight":3000}'::json
 WHERE "id" = 1508;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":45, "weight":2000}'::json
 WHERE "id" = 1509;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":"XL", "weight":1100}'::json
 WHERE "id" = 1510;

 UPDATE goods
 SET
 characteristic = '{"color": "green", "size":"X", "weight":800}'::json
 WHERE "id" = 1511;

 UPDATE goods
 SET
 characteristic = '{"color": "yellow", "weight":900}'::json
 WHERE "id" = 1512;

 UPDATE goods
 SET
 characteristic = '{"color": "blue", "size":"XXL", "weight":2400}'::json
 WHERE "id" = 1513;

 UPDATE goods
 SET
 characteristic = '{"color": "green", "size":"L", "weight":400}'::json
 WHERE "id" = 1514;

 UPDATE goods
 SET
 characteristic = '{"color": "blue", "size":"XXL", "weight":3400}'::json
 WHERE "id" = 1515;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":"XXL", "weight":2300}'::json
 WHERE "id" = 1516;

 UPDATE goods
 SET
 characteristic = '{"color": "yellow", "size":"LX", "weight":1001}'::json
 WHERE "id" = 1517;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":"XXL", "weight":1890}'::json
 WHERE "id" = 1518;

 UPDATE goods
 SET
 characteristic = '{"color": "blue", "size":"XXL", "weight":1200}'::json
 WHERE "id" = 1519;


 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":"XL", "weight":1100}'::json
 WHERE "id" = 1520;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":"XXL", "weight":2500}'::json
 WHERE "id" = 1521;

 UPDATE goods
 SET
 characteristic = '{"color": "yellow", "weight":2000}'::json
 WHERE "id" = 1522;

 UPDATE goods
 SET
 characteristic = '{"color": "blue", "weight":450}'::json
 WHERE "id" = 1523;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":"XXL", "weight":3000}'::json
 WHERE "id" = 1534;

 UPDATE goods
 SET
 characteristic = '{"color": "blue", "weight":1500}'::json
 WHERE "id" = 1535;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":"XL", "weight":2500}'::json
 WHERE "id" = 1536;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":"XXL", "weight":2400}'::json
 WHERE "id" = 1537;

 UPDATE goods
 SET
 characteristic = '{"color": "green", "weight":300}'::json
 WHERE "id" = 1538;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":"LLX", "weight":700}'::json
 WHERE "id" = 1539;

 UPDATE goods
 SET
 characteristic = '{"color": "blue", "size":"BIG", "weight":5000}'::json
 WHERE "id" = 1540;

 UPDATE goods
 SET
 characteristic = '{"color": "red", "size":"XXL", "weight":2050}'::json
 WHERE "id" = 1544;
 ```
Всего получилось 30 записей с заполненным полем "characteristic".


 2 Найдём товары:
    1 У которых есть характеристика цвет, но нет размера:
 ```sql
 SELECT * FROM goods
 WHERE characteristic ? 'color' AND characteristic ? 'size' = false;
 ```
 Нашлось 6 таких товаров.
 К сожалению, мне не удалось найти в документации оператор, который проверяет тип jsonb на 'несуществование' ключа.
 Возможно такого  оператора не существует. Во всяком случае, поскольку мы имеем дело с булевым типом, и легко можем
 проверить равентсов выражения true или false, то без такого оператора вполне можно обойтись.

    2 У которых вес не более килограмма:
 ```sql
    SELECT * FROM goods
        WHERE characteristic -> 'weight' < '1000';
 ```
    Получаем 8 записей.
    Важный момент, правый операнд(число) в сравнении обязательно должен быть в кавычках. Postgres строгий язык, и он не
    будет заниматься приведением типов при сравнении, как это делает MySql.


    3 Красного цвета и размера XXL
 ```sql
 SELECT * FROM goods
  WHERE characteristic::jsonb @> '{"color":"red", "size":"XXL"}'::jsonb;
 ```
 Получили 5 товаров с такими характеристиками.

