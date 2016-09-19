#PostgreSql

## Урок 1.

###Создаём базу данных:
```sql
CREATE DATABASE postgres
  WITH OWNER = postgres
       ENCODING = 'UTF8'
       TABLESPACE = pg_default
       LC_COLLATE = 'Russian_Russia.1251'
       LC_CTYPE = 'Russian_Russia.1251'
       CONNECTION LIMIT = -1;
```
###Создаём таблицы
```sql
CREATE TABLE "public"."books"
(
  "id" serial,
  "title" character varying(500),
  "year_published" integer,
  "year_written" integer,
  "author" character varying(255),
  "price" integer
);
```
```sql
CREATE TABLE "public"."publishers"
(
  "id" serial,
  "name" character varying(255) NOT NULL,
  "city" character varying(255) NOT NULL
);
```
###Наполняем таблицы данными

```sql
INSERT INTO "books" ("title", "year_published", "year_written", "author", "price")
VALUES ('Война и Мир', 1996, 1867, 'Лев Толстой', 1200);

INSERT INTO "books" ("title", "year_published", "year_written", "author", "price")
VALUES ('Преступление и наказание', 2000, 1886, 'Федор Достоевский', 900);

INSERT INTO "books" ("title", "year_published", "year_written", "author", "price")
VALUES ('Белая гвардия', 1987, 1925, 'Михаил Булгаков', 860);

INSERT INTO "books" ("title", "year_published", "year_written", "author", "price")
VALUES ('Воскресенье', 1995, 1891, 'Лев Толстой', 880);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Лампа Мафусаила, или Крайняя битва чекистов с масонами', 2016, 'Виктор Пелевин', 649);

INSERT INTO "books" ("title", "year_published", "year_written", "author", "price")
VALUES ('Из третьего мира - в первый. История Сингапура 1965-2000', 2013, 2001, 'Ли Куан Ю', 1186);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Pro et contra. В 2 томах. ', 2013, 'Борис Пастернак', 880);

INSERT INTO "books" ("title", "year_published", "year_written", "author", "price")
VALUES ('Вспомнить все. Моя невероятно правдивая история', 2013, 2012, 'Арнольд Шварценнегер', 781);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Собрание сочинений в 12 томах (комплект из 11 книг)', 1975, 'Иван Тургенев', 1800);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Два Гусара', 1991, 'Лев Толстой', 880);

INSERT INTO "books" ("title", "year_published", "year_written", "author", "price")
VALUES ('Анна Каренина', 1991, 1888, 'Лев Толстой', 640);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Исповедь', 1991, 'Лев Толстой', 450);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Странная история', 1991, 'Иван Тургенев', 450);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Отцы и дети', 1992, 'Иван Тургенев', 245);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Анна Каренина', 1994, 'Лев Толстой', 500);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Дворянское гнездо. Накануне', 1994, 'Иван Тургенев', 190);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Рудин. Вешние воды. Критические статьи', 1995, 'Иван Тургенев', 245);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Записки на манжетах', 1988, 'Михаил Булгаков', 160);

INSERT INTO "books" ("title", "year_published", "author", "price")
VALUES ('Собачье сердце', 1999, 'Михаил Булгаков', 250);
```
### Вставляем информацию об издательствах
```sql
INSERT INTO "publishers" ("name", "city")
VALUES ('Питер', 'СПб.');

INSERT INTO "publishers" ("name", "city")
VALUES ('Знание', 'М.');

INSERT INTO "publishers" ("name", "city")
VALUES ('Советская Сибирь', 'Новосибирск');

INSERT INTO "publishers" ("name", "city")
VALUES ('ЭКСМО', 'М.');

INSERT INTO "publishers" ("name", "city")
VALUES ('Манн, Иванов и Фербер', 'М.');

INSERT INTO "publishers" ("name", "city")
VALUES ('Советская Россия', 'М.');

INSERT INTO "publishers" ("name", "city")
VALUES ('Правда', 'М.');

INSERT INTO "publishers" ("name", "city")
VALUES ('Новое время', 'М.');

INSERT INTO "publishers" ("name", "city")
VALUES ('Современный писатель', 'М.');

INSERT INTO "publishers" ("name", "city")
VALUES ('Радуга', 'М.');

INSERT INTO "publishers" ("name", "city")
VALUES ('Художественная литература. Москва', 'М.');
```

### Выполняем запросы на выборку (Задание 3)
 `1.` Все книги автора - Михаил Булгаков
```sql 
SELECT * FROM "books" WHERE "author" = 'Михаил Булгаков';
```
 `2.` Все книги ценой не более 500 рублей
```sql
SELECT * FROM "books" WHERE "price" <= 500;
```
 `3.` Заглавия книг и год издания автора - Иван Тургенев, отсортированные по дате публикации, в двух вариантах - по возрастанию(по умолчанию)
```sql
SELECT "books"."title", "books"."year_published" FROM "books" WHERE "author" = 'Иван Тургенев';
```
 `4.` Имена авторов книг вышедших в с 1990 по 1999 годы (включительно). Применена группировка, чтобы получить список без повторов

```sql
SELECT "books"."author" FROM "books" WHERE "year_published" >= 1990 AND "year_published" < 2000 GROUP BY "author";
```
