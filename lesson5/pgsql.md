# PostgreSql

## Урок 5.

`1`. Делаем запрос, который выберет категории и среднюю цену товаров в каждой категории, при условии, что эта средняя
цена менее 50000 рублей (выбираем "бюджетные" категории товаров).
Собственно говоря, здесь всё также, как и в mysql
    ```sql
    SELECT "category"."title",
    AVG("goods"."price") AS "middle_price"
    FROM "category"
    INNER JOIN "goods" ON "goods"."category_id" = "category"."id"
    GROUP BY "category"."title"
    HAVING AVG("goods"."price") < 50000000
    ```
Разница здесь лишь в том, что в последнией строчке запроса в конструкцию HAVING не удалось подсунуть имя нашей колонки
"middle_price", пришлось снова вызывать эту агрегатную функцию.

`2`. Улучшим предыдущий запрос таким образом, чтобы в расчет средней цены включались только товары, имеющиеся на складе.
    ```sql
    SELECT "category"."title",
    AVG("goods"."price") AS "middle_price"
    FROM "category"
    INNER JOIN "goods" ON "goods"."category_id" = "category"."id"
    WHERE "goods"."quantity" IS NOT NULL
    GROUP BY "category"."title"
    HAVING AVG("goods"."price") < 50000000
    ```
3.