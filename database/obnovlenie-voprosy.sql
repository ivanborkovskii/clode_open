-- Вопросы и ответы под статьёй.
--
-- Выполняется один раз на уже работающей базе: phpMyAdmin → выбрать базу →
-- вкладка «SQL» → вставить это и нажать «Вперёд».
--
-- ЗАЧЕМ. Под статьёй появляется блок «Вопросы и ответы». Вопросы задаются
-- в админке при правке статьи: сколько нужно, столько и добавляется,
-- порядок меняется номером. У статьи без вопросов блок не показывается
-- вовсе — существующие статьи после обновления выглядят как раньше.
--
-- Вопросы уходят и в микроразметку FAQPage: её читает Яндекс.
--
-- Своя таблица, а не поле в статье: у одной статьи вопросов может не быть
-- совсем, а у другой десяток, и каждый нужен отдельной записью.

CREATE TABLE IF NOT EXISTS article_faq (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    article_id INT UNSIGNED NOT NULL,
    -- Порядок вывода. Задаётся в админке, к номеру записи отношения не имеет.
    position   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    question   VARCHAR(300) NOT NULL,
    answer     TEXT         NOT NULL,
    PRIMARY KEY (id),
    KEY article_faq_article (article_id, position),
    -- Статью удалили — её вопросы уходят вместе с ней.
    CONSTRAINT article_faq_article_fk FOREIGN KEY (article_id)
        REFERENCES articles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
