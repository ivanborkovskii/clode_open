-- Схема базы данных.
--
-- Сейчас нужна только для раздела «Статьи»: он рассчитан на сотни и тысячи
-- материалов, поэтому индексы заданы сразу под реальные запросы —
-- список по дате, фильтр по рубрике и выборка по адресу.

CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(160)    NOT NULL,
    title       VARCHAR(255)    NOT NULL,
    description TEXT            NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_categories_slug (slug)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articles (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id      INT UNSIGNED NULL,
    slug             VARCHAR(190) NOT NULL,
    title            VARCHAR(255) NOT NULL,
    excerpt          TEXT         NULL,
    body             MEDIUMTEXT   NOT NULL,

    -- SEO-поля отдельно от заголовка: title страницы и H1 часто различаются.
    meta_title       VARCHAR(255) NULL,
    meta_description VARCHAR(500) NULL,

    cover_path       VARCHAR(255) NULL,
    cover_alt        VARCHAR(255) NULL,

    status           ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at     DATETIME     NULL,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uniq_articles_slug (slug),

    -- Основной запрос списка: опубликованные, свежие сверху.
    KEY idx_articles_status_date (status, published_at),

    -- Список внутри рубрики.
    KEY idx_articles_category (category_id, status, published_at),

    CONSTRAINT fk_articles_category
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON DELETE SET NULL,

    -- Поиск по тексту. При тысячах статей выручает на странице поиска.
    FULLTEXT KEY ft_articles_search (title, excerpt, body)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Заявки с форм. Пока сайт пишет их в файл; таблица — следующий шаг,
-- когда понадобится история обращений.
CREATE TABLE IF NOT EXISTS leads (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    phone      VARCHAR(30)  NOT NULL,
    email      VARCHAR(150) NULL,
    message    TEXT         NULL,
    source     VARCHAR(100) NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_leads_created (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
