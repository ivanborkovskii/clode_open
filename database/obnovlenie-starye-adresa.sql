-- Прежние адреса статей.
--
-- Выполняется один раз на уже работающей базе: phpMyAdmin → выбрать базу →
-- вкладка «SQL» → вставить это и нажать «Вперёд».
--
-- ЗАЧЕМ. Адрес статьи можно поменять в админке. До этого обновления
-- прежний адрес сразу отвечал «страница не найдена»: люди, пришедшие
-- по старой ссылке из поиска или из закладок, упирались в пустоту,
-- а поисковик выбрасывал страницу из выдачи вместе со всем, что она
-- успела накопить.
--
-- Теперь при смене адреса прежний запоминается здесь и переводит
-- на нынешний. Уже существующие статьи не трогаются: у них смены
-- адреса не было, и записывать нечего.
--
-- Таблица наполняется сама, руками в неё писать не нужно.

CREATE TABLE IF NOT EXISTS article_slugs (
    slug       VARCHAR(160) NOT NULL,
    article_id INT UNSIGNED NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (slug),
    KEY article_slugs_article (article_id),
    CONSTRAINT article_slugs_article_fk FOREIGN KEY (article_id)
        REFERENCES articles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
