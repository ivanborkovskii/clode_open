-- Ответы на комментарии.
--
-- Выполняется один раз на уже работающей базе: phpMyAdmin → выбрать базу →
-- вкладка «SQL» → вставить это и нажать «Вперёд».
--
-- Существующие комментарии не трогаются: у них parent_id остаётся пустым,
-- то есть они становятся началами веток.

ALTER TABLE comments
    -- На какой комментарий это ответ. Пусто — значит, это начало ветки.
    ADD COLUMN parent_id INT UNSIGNED DEFAULT NULL AFTER article_id,
    -- Ответ владельца сайта: помечается на странице и публикуется сразу.
    ADD COLUMN is_author TINYINT(1) NOT NULL DEFAULT 0 AFTER status,
    ADD KEY comments_parent (parent_id),
    ADD CONSTRAINT comments_parent_fk FOREIGN KEY (parent_id)
        REFERENCES comments (id) ON DELETE CASCADE;
