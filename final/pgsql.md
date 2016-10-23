# DBA. Итоговая работа

##Избранная БД - Postgres

###Пояснительная записка.

Примерная структура базы данных интернет магазина. Основные требования к БД:
Позволять регистрировать пользователей,
Распределять товары по брендам и категориям,
Управлять наличием товаров на складе,
Создавать заказы.

Таблица покупателей - users содержит самую основную информацию - поля name, email. Также поле пароль.
Условно всех юзеров можно разделить на 2 категории: А. Покупатели, Б. Пользователи, контакты, которых можно использовать для рассылок. 
Для реализации второй функции достаточно будет данных только таблицы users.
Добавим сюда еще колонку phone, email_receive и sms_receive. Последние 2 поля будут иметь булев тип и означать подписку пользователя на email и sms рассылку о новых товарах и пр.

Кроме того, потребуются другие данные пользователей. Это адрес доставки товара. Т.к. у одного юзера таких адресов может быть не один, эти данные следует вынести в отдельную таблицу addresses.
Данная таблица как минимум должна содержать поля: адрес и id пользователя.
Также создадим таблицу contacts, где могут храниться дополнительные телефоны, skype, icq, telegram и пр, что юзер придпочтет оставить. Данная таблица будет сожержать поля: type, content, users_id.

Кроме того, для забывчивых пользователей необходимо предусмотреть таблицу reset_passwords. Она должна содержать поля email, token, дата создания записи. В таблице users должно быть поле remember_token. Это хеш-строка, которая
будет выслана на email или телефон пользователя. А в поле token таблицы reset_passwords придет то, что пользователь прислал в форме сброса пароля. Затем два значения token и remember_token будут сравнены между собой.
Следует отметить, что в основном это задача на стороне приложения а не БД. Но БД просто должна содержать в себе эти таблицы.

Следующий ключевой субъект это товары. Таблица товаров goods, которая была создана во время курса вполне для этих целей подходит.
В данной таблице должно быть поле "количество на складе" - integer и поле есть в наличии - boolean. Т.к. одно непсредственно связано с другим, необходимо будет создать соответствующий тригер.
С таблицей товаров непосредственно связаны таблицы brands и categories, которые также были созданы на курсе. Здесь есть только один недостаток - не реализована связь многие ко многим. И хоть возможности postgres позволяют это сделать благодаря типу поля
jsonb, мы будем строить классическую модель, т.к. внешние ключи в конкретном случае очень важны. А именно создадим pivot таблицы good_catetegory.

Любой товар, имеет 6 статусов: 1 доступен для покупки, 2 нет в наличии,  3 заказан, 4 оплачен, 5 исполнен, 6 возвращен. Соответственно, пользователи могут заказать только товар, имеющий статус 1.
Однако, на витрине находятся и товары имеющие статус 2, но они не доступны для покупки.  

После заказа товара формируется корзина покупателя. В ней содержатся данные сообщающие нам id товара и его количество, которое заказал посетитель. Существует множество вариантов, как хранить корзину.
Это может быть отдельная таблица basket со множеством записей. Иногда эти данные в виде ключ => значение может храниться в cookie.
Поскольку у нас postgres, считаю что самый уместный вариант хранить эти данные в отдельном поле таблицы заказов в виде json объекта. Поле должно иметь тип jsonb.
и данные хранятся в виде id товара: количество. т.е. "{45: 2, 14: 4}".
Итак, наша таблица orders содержит поля: id (он же номер заказа), дата доставки, basket,а также поле статус. 
Первоначально записи присваивается статус 3. Одновременно с этим товар изымается со склада (количество товаров в таблице goods становится меньше на 1). 
Если товар не будет отплачен в течение определенного времени, то он возвращается на склад (статус в таблице orders меняется на 2, в поле quantity в таблице goods становится +1). 
Если же товар будет оплачен, то статус товара изменяется на 4. После исполнения заказа записи присваивается статус 5.
Заказы со статусом 5 хранятся в таблице orders 15 дней (до истечения срока возврата товара) и затем перемещаются в архив.
Заказы со статусом 2 находяься в таблице orders в течение 1-го месяца в целях работы с покупателями, бросившими корзину, затем перемещаются в архив.
Возвращенные заказы приобретают статус 6. Это особый статус. Он означает что запись остается в базе на неопределенное время (по каждому возвращенному заказу все дальнейшие операции производятся исключительно вручную).
При создании таблиц в БД мы никак не будем использовать дополнитетельные схемы. Все таблицы будут относиться к схеме public.

###Концепция магазина.
Важный момент - концепция. Допустим у нас будет магазин спорттоваров, в сегменте на товары для профессиональных спортсменов сразу в нескольких видах спорта:
Лыжный спорт, велосипедный спорт, легкая атлетика, хоккей, футбол, волейбол, баскетбол ну и что-нибудь ещё.
Название у магазина тоже будет соответствующим - "Мастер спорта". Правда я сомневаюсь, что оно уникально, скорее всего магазины с таким названием есть на просторах
интернета, но в нашем случае это не столь важно.

###Создание сущностей

Но для начала создадим новую таблицу в базе данных:

```sql
CREATE DATABASE master_of_sport
  WITH OWNER = postgres
       ENCODING = 'UTF8'
       TABLESPACE = pg_default
       LC_COLLATE = 'Russian_Russia.1251'
       LC_CTYPE = 'Russian_Russia.1251'
       CONNECTION LIMIT = -1;
```

####Модуль пользователей

Создадим таблицы, необходимые для работы с пользователями. Сразу с нужными нам constraint.
Обязательно необходимы служебные поля типа дата: created_at, updated_at. К слову такие поля нужны практически в любой таблице, 
за исключением pivot таблиц. Следует отметить, что значения для данных полей должны формироваться исключительно на строне приложения.
Так как у нас пока нет никакого приложения, эти поля пока у нас будут NULL.
 
Users:

```sql
CREATE TABLE users
(
  "id" serial,
  "name" varchar(255) NOT NULL,
  "email" varchar(255) NOT NULL,
  "password" varchar(255) NOT NULL,
  "phone" char(11), 
  "email_receive" boolean,
  "sms_receive" boolean,
  "remember_token" varchar(100),
  "created_at" timestamp,
  "updated_at" timestamp,
  CONSTRAINT "users_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "users_email_unique" UNIQUE ("email")
);
```

reset_passwords:
```sql
CREATE TABLE reset_passwords
(
  "email" varchar(255) NOT NULL,
  "token" varchar(255) NOT NULL,
  "created_at" timestamp
);
```
Создадим таблицу адресов
```sql
CREATE TABLE "addresses"
(
"id" serial,
"address" text,
"user_id" integer
);
```
И таблицу контактов:
```sql
CREATE TABLE "other_contacts"
(
"id" serial,
"type" varchar(500),
"content" text,
"user_id" integer
);
```

####Движение товаров

Создадим таблицу товаров:
```sql
CREATE TABLE "goods" (  "id" serial,
                        "title" varchar(255) NOT NULL,
                        "vendor_code" char(9) NOT NULL,
                        "image_url" varchar(255),
                        "price" integer NOT NULL CHECK("price" >= 0),
                        "old_price" integer NULL CHECK("old_price" > 0),
                        "warehouse_date" timestamptz DEFAULT now(),
                        "quantity" smallint,
                        "presence" boolean NOT NULL DEFAULT false,
                        "brand_id" integer,
                        "created_at" timestamp,
                        "updated_at" timestamp,
                          CONSTRAINT goods_id PRIMARY KEY (id),
                          CONSTRAINT goods_vendor_code UNIQUE (vendor_code),
                          CONSTRAINT goods_old_price_ent CHECK (old_price > 0),
                          CONSTRAINT goods_price_ent CHECK (price > 0),
                          CONSTRAINT inner_url CHECK (image_url::text ~ '^/images/'::text),
                          CONSTRAINT new_less_old_ent CHECK (price <= old_price),
                          CONSTRAINT presence_not_ent CHECK (presence = true OR presence = false),
                          CONSTRAINT quantity_not_subzero CHECK (quantity >= 0)
                        );
```
Обращаю внимание, что колонка category_id в нашей таблице отсутствует. Она нам не потребуется, так как будет создана специальная pivot таблица для связи товаров с категориями

Создадим таблицу брендов:

```sql
CREATE TABLE "brands"
(
  "id" serial,
  "brand" varchar(100) NOT NULL,
  class varchar(100),
  CONSTRAINT brand_id PRIMARY KEY (id)
);
```
Что касается таблицы категорий, то вариант таблицы из курса нам не очень подойдет, т.к. нам потребуется вложенность. Для этой цели будем использовать структуру Nested Sets

```sql
CREATE TABLE "categories" 
(
"id" serial,
"lft" integer NOT NULL,
"rgt" integer NOT NULL,
"tree" integer NOT NULL,
"level" integer NOT NULL DEFAULT 0,
"parent" integer NOT NULL DEFAULT 0,
"title" varchar(100) NOT NULL,
"created_at" timestamp,
"updated_at" timestamp,
CONSTRAINT category_id PRIMARY KEY (id)
);
```
Основным и главным минусом Nested Sets является сложность управления таблицей, в котором этот метод применяется. Вставка любой записи означает необхдимость изменения всех ключей данного уровня.
Однако, мне удалось найти на хабре решение на уровне базы данных - специальный тригер, решающий данную проблему. Вот ссылка на статью https://habrahabr.ru/post/63416/.
Сделаем так, как там написано. Ничего не добавляю от себя, просто наглый копипаст, даже комментарии не трогаю
Для начала на потребуется добавить 2 новых колонки _trigger_lock_update и _trigger_for_delete:

```sql
ALTER TABLE "categories"
ADD COLUMN "_trigger_lock_update" boolean NOT NULL DEFAULT false,

ALTER TABLE "categories"
ADD COLUMN "_trigger_for_delete" boolean NOT NULL DEFAULT false 
```

Создадим функцию блокирующую дереве на изменение, пока транзакция не закончена:

```sql
CREATE OR REPLACE FUNCTION lock_categories_func(tree_id integer)
    RETURNS boolean AS
$BODY$
DECLARE tree_id ALIAS FOR $1;
    _id INTEGER;
BEGIN
    SELECT id
        INTO _id
        FROM categories
        WHERE tree = tree_id FOR UPDATE;
    RETURN TRUE;
END;
$BODY$
  LANGUAGE 'plpgsql' STABLE
  COST 100;
```


#####Функция для создания записи
```sql
CREATE OR REPLACE FUNCTION categories_before_insert_func()
    RETURNS trigger AS
$BODY$
DECLARE
    _lft            INTEGER;
    _level          INTEGER;
    _tmp_lft        INTEGER;
    _tmp_rgt        INTEGER;
    _tmp_level      INTEGER;
    _tmp_id         INTEGER;
    _tmp_parent     INTEGER;
BEGIN
    PERFORM lock_categories(NEW.tree);
-- Нельзя эти поля ручками ставить:
    NEW._trigger_for_delete := FALSE;
    NEW._trigger_lock_update := FALSE;
    _lft := 0;
    _level := 0;
-- Если мы указали родителя:
    IF NEW.parent IS NOT NULL AND NEW.parent > 0 THEN
        SELECT rgt, "level" + 1
            INTO _lft, _level
            FROM categories
            WHERE id = NEW.parent AND
                  tree = NEW.tree;
    END IF;
-- Если мы указали левый ключ:
    IF NEW.lft IS NOT NULL AND
       NEW.lft > 0 AND 
       (_lft IS NULL OR _lft = 0) THEN
        SELECT id, lft, rgt, "level", parent 
            INTO _tmp_id, _tmp_lft, _tmp_rgt, _tmp_level, _tmp_parent
            FROM categories
            WHERE tree = NEW.tree AND (lft = NEW.lft OR rgt = NEW.lft);
        IF _tmp_lft IS NOT NULL AND _tmp_lft > 0 AND NEW.lft = _tmp_lft THEN
            NEW.parent := _tmp_parent;
            _lft := NEW.lft;
            _level := _tmp_level;
        ELSIF _tmp_lft IS NOT NULL AND _tmp_lft > 0 AND NEW.lft = _tmp_rgt THEN
            NEW.parent := _tmp_id;
            _lft := NEW.lft;
            _level := _tmp_level + 1;
        END IF;
    END IF;
-- Если родитель или левый ключ не указан, или мы ничего не нашли:
    IF _lft IS NULL OR _lft = 0 THEN
        SELECT MAX(rgt) + 1
            INTO _lft
            FROM categories
            WHERE tree = NEW.tree;
        IF _lft IS NULL OR _lft = 0 THEN
            _lft := 1;
        END IF;
        _level := 0;
        NEW.parent := 0; 
    END IF;
-- Устанавливаем полученные ключи для узла:
    NEW.lft := _lft;
    NEW.rgt := _lft + 1;
    NEW."level" := _level;
-- Формируем развыв в дереве на месте вставки:
    UPDATE categories
        SET lft = lft + 
            CASE WHEN lft > lft 
              THEN 2 
              ELSE 0 
            END,
            rgt = rgt + 2,
            _trigger_lock_update = TRUE
        WHERE tree = NEW.tree AND rgt >= _lft;
    RETURN NEW;
END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE
  COST 100;
```

######И триггер
```sql
CREATE TRIGGER categories_before_insert_tr
    BEFORE INSERT
    ON categories
    FOR EACH ROW
    EXECUTE PROCEDURE categories_before_insert_func();
```

#####Функция на изменение записи:
```sql
CREATE OR REPLACE FUNCTION categories_before_update_func()
  RETURNS trigger AS
$BODY$
DECLARE
    _lft       INTEGER;
    _level          INTEGER;
    _skew_tree      INTEGER;
    _skew_level     INTEGER;
    _skew_edit      INTEGER;
    _tmp_lft   INTEGER;
    _tmp_rgt  INTEGER;
    _tmp_level      INTEGER;
    _tmp_id         INTEGER;
    _tmp_parent  INTEGER;
BEGIN
    PERFORM lock_categories(OLD.tree);
-- А стоит ли нам вообще что либо делать:
    IF NEW._trigger_lock_update = TRUE THEN
        NEW._trigger_lock_update := FALSE;
        IF NEW._trigger_for_delete = TRUE THEN
            NEW = OLD;
            NEW._trigger_for_delete = TRUE;
            RETURN NEW;
        END IF;
        RETURN NEW;
    END IF;
-- Сбрасываем значения полей, которые пользователь менять не может:
    NEW._trigger_for_delete := FALSE;
    NEW.tree := OLD.tree;
    NEW.rgt := OLD.rgt;
    NEW."level" := OLD."level";
    IF NEW.parent IS NULL THEN NEW.parent := 0; END IF;
-- Проверяем, а есть ли изменения связанные со структурой дерева
    IF NEW.parent = OLD.parent AND NEW.lft = OLD.lft
    THEN
        RETURN NEW;
    END IF;
-- Дерево таки перестраиваем, что ж, приступим:
    _lft := 0;
    _level := 0;
    _skew_tree := OLD.rgt - OLD.lft + 1;
-- Определяем куда мы его переносим:
-- Если сменен parent:
    IF NEW.parent <> OLD.parent THEN
-- Если в подчинение другому злу:
        IF NEW.parent > 0 THEN
            SELECT rgt, level + 1
                INTO _lft, _level
                FROM categories
                WHERE id = NEW.parent AND tree = NEW.tree;
-- Иначе в корень дерева переносим:
        ELSE
            SELECT MAX(rgt) + 1 
                INTO _lft
                FROM categories
                WHERE tree = NEW.tree;
            _level := 0;
        END IF;
-- Если вдруг родитель в диапазоне перемещаемого узла, проверка:
        IF _lft IS NOT NULL AND 
           _lft > 0 AND
           _lft > OLD.lft AND
           _lft <= OLD.rgt 
        THEN
           NEW.parent := OLD.parent;
           NEW.lft := OLD.lft;
           RETURN NEW;
        END IF;
    END IF;
-- Если же указан lft, а parent - нет
    IF _lft IS NULL OR _lft = 0 THEN
        SELECT id, lft, rgt, "level", parent 
            INTO _tmp_id, _tmp_lft, _tmp_rgt, _tmp_level, _tmp_parent
            FROM categories
            WHERE tree = NEW.tree AND (rgt = NEW.lft OR rgt = NEW.lft - 1)
            LIMIT 1;
        IF _tmp_lft IS NOT NULL AND _tmp_lft > 0 AND NEW.lft - 1 = _tmp_rgt THEN
            NEW.parent := _tmp_parent;
            _lft := NEW.lft;
            _level := _tmp_level;
        ELSIF _tmp_lft IS NOT NULL AND _tmp_lft > 0 AND NEW.lft = _tmp_rgt THEN
            NEW.parent := _tmp_id;
            _lft := NEW.lft;
            _level := _tmp_level + 1;
        ELSIF NEW.lft = 1 THEN
            NEW.parent := 0;
            _lft := NEW.lft;
            _level := 0;
        ELSE
           NEW.parent := OLD.parent;
           NEW.lft := OLD.lft;
           RETURN NEW;
        END IF;
    END IF;
-- Теперь мы знаем куда мы перемещаем дерево
        _skew_level := _level - OLD."level";
    IF _lft > OLD.lft THEN
-- Перемещение вверх по дереву
        _skew_edit := _lft - OLD.lft - _skew_tree;
        UPDATE categories
            SET lft =  CASE WHEN rgt <= OLD.rgt
                                 THEN lft + _skew_edit
                                 ELSE CASE WHEN lft > OLD.rgt
                                           THEN lft - _skew_tree
                                           ELSE lft
                                      END
                            END,
                "level" =   CASE WHEN rgt <= OLD.rgt 
                                 THEN "level" + _skew_level
                                 ELSE "level"
                            END,
                rgt = CASE WHEN rgt <= OLD.rgt 
                                 THEN rgt + _skew_edit
                                 ELSE CASE WHEN rgt < _lft
                                           THEN rgt - _skew_tree
                                           ELSE rgt
                                      END
                            END,
                _trigger_lock_update = TRUE
            WHERE tree = OLD.tree AND
                  rgt > OLD.lft AND
                  lft < _lft AND
                  id <> OLD.id;
        _lft := _lft - _skew_tree;
    ELSE
-- Перемещение вниз по дереву:
        _skew_edit := _lft - OLD.lft;
        UPDATE categories
            SET
                rgt = CASE WHEN lft >= OLD.lft
                                 THEN rgt + _skew_edit
                                 ELSE CASE WHEN rgt < OLD.lft
                                           THEN rgt + _skew_tree
                                           ELSE rgt
                                      END
                            END,
                "level" =   CASE WHEN lft >= OLD.lft
                                 THEN "level" + _skew_level
                                 ELSE "level"
                            END,
                lft =  CASE WHEN lft >= OLD.lft
                                 THEN lft + _skew_edit
                                 ELSE CASE WHEN lft >= _lft
                                           THEN lft + _skew_tree
                                           ELSE lft
                                      END
                            END,
                 _trigger_lock_update = TRUE
            WHERE tree = OLD.tree AND
                  rgt >= _lft AND
                  lft < OLD.rgt AND
                  id <> OLD.id;
    END IF;
-- Дерево перестроили, остался только наш текущий узел
    NEW.lft := _lft;
    NEW."level" := _level;
    NEW.rgt := _lft + _skew_tree - 1;
    RETURN NEW;
END;
$BODY$
    LANGUAGE 'plpgsql' VOLATILE
    COST 100;
```
###### Триггер

```sql
CREATE TRIGGER categories_before_update_tr
    BEFORE UPDATE
    ON categories
    FOR EACH ROW
    EXECUTE PROCEDURE categories_before_update_func()
```

#####Функция удаления записи

```sql
CREATE OR REPLACE FUNCTION categories_after_delete_func()
    RETURNS trigger AS
$BODY$
DECLARE
    _skew_tree INTEGER;
BEGIN
    PERFORM lock_categories(OLD.tree);
-- Проверяем, стоит ли выполнять триггер:
    IF OLD._trigger_for_delete = TRUE THEN RETURN OLD; END IF;
-- Помечаем на удаление дочерние узлы:
    UPDATE categories
        SET _trigger_for_delete = TRUE,
            _trigger_lock_update = TRUE
        WHERE
            tree = OLD.tree AND
            lft > OLD.lft AND
            rgt < OLD.rgt;
-- Удаляем помеченные узлы:
    DELETE FROM categories
        WHERE
            tree = OLD.tree AND
            lft > OLD.lft AND
            rgt < OLD.rgt;
-- Убираем разрыв в ключах:
    _skew_tree := OLD.rgt - OLD.lft + 1;
    UPDATE categories
        SET lft = CASE WHEN lft > OLD.lft
                            THEN lft - _skew_tree
                            ELSE lft
                       END,
            rgt = rgt - _skew_tree,
            _trigger_lock_update = TRUE
        WHERE rgt > OLD.rgt AND
            tree = OLD.tree;
    RETURN OLD;
END;
$BODY$
    LANGUAGE 'plpgsql' VOLATILE
    COST 100;
```

Триггер

```sql
CREATE TRIGGER categories_after_delete_tr
    AFTER DELETE
    ON categories
    FOR EACH ROW
    EXECUTE PROCEDURE categories_after_delete_func()
```


Проверим как работает. Обнаружил пару ошибок, теперь тригеррные функции работают как часы. 
Создадим заглавную категорию:

```sql
INSERT INTO "categories" ("tree", "title")
VALUES (1, 'Лыжи (crosscontry)');
```
Поскольку триггеры у меня заработали не сразу, были некоторые ошибки, и мне пришлось несколько раз очищать таблицу от записей, система присвоила id для нашей записи сразу 32.
Таким образом, id родителя для подкатегории будет 32.
```sql
INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 32, 'Палки');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 32, 'Лыжи');

-- Полученый id = 34, создадим подкатегорию:

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 34, 'Классика');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 34, 'Конёк');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES ('1', '32', 'Ботинки');
-- id 37
INSERT INTO "categories" ("tree", "parent", "title")
VALUES ('1', '37', 'Ботинки NNN');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES ('1', '37', 'Ботинки SNS');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 32, 'Крепления');
-- id 38

INSERT INTO "categories" ("tree", "parent", "title")
VALUES ('1', '38', 'система NNN');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES ('1', '38', 'система SNS');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 32, 'Комбинезоны');

-- id 43

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 43, 'Раздельные');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 43, 'Слитные');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 32, 'Лыжные костюмы');
```

Ещё категорию с подкатегорими:

```sql
INSERT INTO "categories" ("tree", "title")
VALUES (1, 'Велоспорт');
-- id 47

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 47, 'Велосипеды');
-- id 48
INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 48, 'Велосипеды BMX');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 48, 'Велосипеды Горные');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 48, 'Велосипеды Шоссейные');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 48, 'Велосипеды Складные');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 48, 'Велосипеды FixedGear');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 47, 'Одежда');
-- id 50

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 50, 'Брюки');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 50, 'Куртики');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 50, 'Жилетки');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 50, 'Шорты');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 50, 'Джерси');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 47, 'Обувь');
-- id 51

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 51, 'Велотуфли');
-- id 60

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 60, 'MTB');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 60, 'Шоссейные');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 60, 'Кроссовки');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 47, 'Оптика');
-- id 52

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 52, 'Фонари');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 52, 'Адаптеры');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 52, 'Линзы');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 52, 'Линзы');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 47, 'Защита');
-- id 53

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 53, 'Жилеты защитные');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 53, 'Защита голени');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 53, 'Защита колена');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 53, 'Шлемы велосипедные');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 47, 'Аксессуары');
-- id 54

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 54, 'Аптечки');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 54, 'Велобагажники');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 54, 'Багажники авто');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 54, 'Выжимки для цепи');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 54, 'Герметики для колёс');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 54, 'Ключи');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 54, 'Велозамки');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 47, 'Запчасти');
-- id 55

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 55, 'Втулки');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 55, 'Колёса');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 55, 'Вилки');

INSERT INTO "categories" ("tree", "parent", "title")
VALUES (1, 55, 'Трансмиссия');
```

Уфф! Пожалуй достаточно категорий. Всё таки добавление данных в таблицу не задача этой домашки, но очень уж увлекательная вещь Nested Sets. Не пожалел, что потратил время на то, чтоы разобраться с этим.
Выведем наше дерево катгорий
```sql
SELECT id, lft, rgt, tree, level, parent, title FROM "categories" ORDER BY lft
```

|id | lft| rgt | tree | level | parent | title               |
|---|----|-----|------|-------|--------|---------------------|
|32 | 1  | 30  | 1 	  | 0 	  | 0      | Лыжи (crosscountry) |
|33 | 2  | 3   | 1    | 1 	  | 32     | Палки               |
|34 | 4  | 9   | 1    | 1 	  | 32     | Лыжи                |
|35 | 5  | 6   | 1    | 2 	  | 34     | Классика            |
|36 | 7  | 8   | 1    | 2     | 34     | Конёк               |
|37 | 10 | 15  | 1    | 1     | 32     | Ботинки             |
|39 | 11 | 12  | 1    | 2     | 37     | Ботинки NNN         |
|40 | 13 | 14  | 1    | 2     | 37     | Ботинки SNS         |
|38 | 16 | 21  | 1    | 1     | 32     | Крепления           |
|41 | 17 | 18  | 1    | 2     | 38     | система NNN         |
|42 | 19 | 20  | 1    | 2     | 38     | система SNS         |
|43 | 22 | 27  | 1    | 1     | 32     | Комбинезоны         |
|44 | 23 | 24  | 1    | 2     | 43     | Раздельные          |
|45 | 25 | 26  | 1    | 2     | 43     | Слитные             |
|46 | 28 | 29  | 1    | 1     | 32     | Лыжные костюмы      |
|47 | 31 | 102 | 1    | 0     | 0      | Велоспорт           |
|48 | 32 | 43  | 1    | 1     | 47     | Велосипеды          |
|49 | 33 | 34  | 1    | 2     | 48     | Велосипеды BMX      |
|56 | 35 | 36  | 1    | 2     | 48     | Велосипеды Горные   |
|57 | 37 | 38  | 1    | 2     | 48     | Велосипеды Шоссейные|
|58 | 39 | 40  | 1    | 2     | 48     | Велосипеды Складные |
|59 | 41 | 42  | 1    | 2     | 48     | Велосипеды FixedGear|
|50 | 44 | 45  | 1    | 1     | 47     | Одежда              |
|51 | 46 | 55  | 1    | 1     | 47     | Обувь               |
|60 | 47 | 54  | 1    | 2     | 51     | Велотуфли           |
|61 | 48 | 49  | 1    | 3     | 60     | MTB                 |
|62 | 50 | 51  | 1    | 3     | 60     | Шоссейные           |
|63 | 52 | 53  | 1    | 3     | 60     | Кроссовки           |
|52 | 56 | 65  | 1    | 1     | 47     | Оптика              |
|64 | 57 | 58  | 1    | 2     | 52     | Фонари              |
|65 | 59 | 60  | 1    | 2     | 52     | Адаптеры            |
|66 | 61 | 62  | 1    | 2     | 52     | Линзы               |
|67 | 63 | 64  | 1    | 2     | 52     | Линзы               |
|53 | 66 | 75  | 1    | 1     | 47     | Защита              |
|68 | 67 | 68  | 1    | 2     | 53     | Жилеты защитные     |
|69 | 69 | 70  | 1    | 2     | 53     | Защита голени       |
|70 | 71 | 72  | 1    | 2     | 53     | Защита колена       |
|71 | 73 | 74  | 1    | 2     | 53     | Шлемы велосипедные  |
|54 | 76 | 91  | 1    | 1     | 47     | Аксессуары          |
|72 | 77 | 78  | 1    | 2     | 54     | Аптечки             |
|73 | 79 | 80  | 1    | 2     | 54     | Велобагажники       |
|74 | 81 | 82  | 1    | 2     | 54     | Багажники авто      |
|75 | 83 | 84  | 1    | 2     | 54     | Выжимки для цепи    |
|76 | 85 | 86  | 1    | 2     | 54     | Герметики для колёс |
|77 | 87 | 88  | 1    | 2     | 54     | Ключи               |
|78 | 89 | 90  | 1    | 2     | 54     | Велозамки           |
|55 | 92 | 101 | 1    | 1     | 47     | Запчасти            |
|79 | 93 | 94  | 1    | 2     | 55     | Втулки              |   
|80 | 95 | 96  | 1    | 2     | 55     | Колёса              |
|81 | 97 | 98  | 1    | 2     | 55     | Вилки               |
|82 | 99 | 100 | 1    | 2     | 55     | Трансмиссия         |


Итак у нас две заглавных категории и куча подкатегорий.
Теперь создадим талицу goods_category. Это pivot таблица для создания связей Many-To-Many.
```sql
CREATE TABLE "qoods_category"
(
"good_id" integer, 
"category_id" integer
);
```

####Оформление заказа

Перед тем как создать таблицу заказов, объявим новый тип для поля статус.
```sql
CREATE TYPE statlist
AS ENUM('req', 'redj', 'paid', 'exec', 'ret');
```
То есть - запрошен, отказ, оплачен, исполнен, возвращён.

Создадим таблицу orders
```sql
CREATE TABLE "orders"
(
"id" serial,
"user_id" integer NOT NULL,
"date" timestamp,
"basket" jsonb NOT NULL,
"status" statlist NOT NULL
);
```

