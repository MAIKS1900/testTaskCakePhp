
Сборка фронта не требуется.

Таблица с продуктами.
```Clickhouse
CREATE TABLE db.wbProducts
(
    request_date DateTime NOT NULL,
    query  String NOT NULL,
    position  UInt32 NOT NULL,
    name  String NOT NULL,
    brand_name  String NOT NULL
)
ENGINE = MergeTree()
ORDER BY (request_date, query, position);
```
