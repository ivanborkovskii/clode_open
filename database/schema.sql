-- Раздел «Статьи»: структура базы данных.
--
-- Импортируется один раз через phpMyAdmin (вкладка «Импорт») или командой
--   mysql -u ПОЛЬЗОВАТЕЛЬ -p БАЗА < schema.sql
--
-- Все таблицы InnoDB и utf8mb4: нужны внешние ключи и полноценная кириллица
-- вместе с эмодзи в комментариях посетителей.
--
-- Учётная запись для входа в админку здесь не создаётся. Её заводит
-- страница /admin/ustanovka — она работает только пока в таблице admins
-- нет ни одной записи.

SET NAMES utf8mb4;

-- Категория у статьи всегда одна. Список закрытый: Битрикс24, amoCRM
-- и общие статьи. Новые добавляются через админку.
CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(80)  NOT NULL,
    name        VARCHAR(120) NOT NULL,
    title       VARCHAR(180) NOT NULL DEFAULT '',
    description VARCHAR(300) NOT NULL DEFAULT '',
    position    SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Теги уже категорий. У статьи их может быть сколько угодно.
CREATE TABLE IF NOT EXISTS tags (
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug     VARCHAR(80)  NOT NULL,
    name     VARCHAR(120) NOT NULL,
    position SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articles (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug         VARCHAR(160) NOT NULL,
    category_id  INT UNSIGNED NOT NULL,
    title        VARCHAR(250) NOT NULL,
    -- Анонс: показывается в списке статей и уходит в описание страницы,
    -- если отдельное описание не заполнено.
    excerpt      VARCHAR(600) NOT NULL DEFAULT '',
    body         MEDIUMTEXT   NOT NULL,
    -- Текст статьи без разметки. Заполняется при сохранении и нужен только
    -- поиску: искать по body значило бы находить совпадения внутри тегов.
    search_text  MEDIUMTEXT   NOT NULL,
    cover        VARCHAR(255) NOT NULL DEFAULT '',
    cover_alt    VARCHAR(250) NOT NULL DEFAULT '',
    cover_width  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    cover_height SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    author       VARCHAR(160) NOT NULL DEFAULT '',
    reading_time SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    published_at DATE         NOT NULL,
    status       ENUM('draft','published') NOT NULL DEFAULT 'draft',
    meta_title       VARCHAR(250) NOT NULL DEFAULT '',
    meta_description VARCHAR(400) NOT NULL DEFAULT '',
    -- Тема статьи так, как она звучит в форме заявки под ней:
    -- «по интеграции Битрикс24 и Телеграм». Пусто — подставится
    -- автоматически из заголовка.
    form_topic       VARCHAR(200) NOT NULL DEFAULT '',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY articles_slug (slug),
    -- Основная выборка списка: опубликованные, свежие сверху.
    KEY articles_feed (status, published_at, id),
    KEY articles_category (category_id, status, published_at),
    CONSTRAINT articles_category_fk FOREIGN KEY (category_id)
        REFERENCES categories (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS article_tag (
    article_id INT UNSIGNED NOT NULL,
    tag_id     INT UNSIGNED NOT NULL,
    PRIMARY KEY (article_id, tag_id),
    -- Обратный порядок: по тегу быстро находятся статьи.
    KEY article_tag_tag (tag_id, article_id),
    CONSTRAINT article_tag_article_fk FOREIGN KEY (article_id)
        REFERENCES articles (id) ON DELETE CASCADE,
    CONSTRAINT article_tag_tag_fk FOREIGN KEY (tag_id)
        REFERENCES tags (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Комментарии появляются на сайте после проверки в админке.
-- Почта не публикуется — она нужна, чтобы можно было ответить автору.
CREATE TABLE IF NOT EXISTS comments (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    article_id INT UNSIGNED NOT NULL,
    -- На какой комментарий это ответ. Пусто — начало ветки.
    -- Глубина ровно одна: ответ на ответ прикрепляется к тому же началу,
    -- иначе на телефоне ветка уезжает за край экрана.
    parent_id  INT UNSIGNED DEFAULT NULL,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(190) NOT NULL DEFAULT '',
    body       TEXT         NOT NULL,
    status     ENUM('new','approved','rejected') NOT NULL DEFAULT 'new',
    -- Ответ владельца сайта: помечается на странице и публикуется сразу.
    is_author  TINYINT(1)   NOT NULL DEFAULT 0,
    ip         VARBINARY(16) DEFAULT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY comments_article (article_id, status, created_at),
    KEY comments_moderation (status, created_at),
    KEY comments_parent (parent_id),
    CONSTRAINT comments_article_fk FOREIGN KEY (article_id)
        REFERENCES articles (id) ON DELETE CASCADE,
    CONSTRAINT comments_parent_fk FOREIGN KEY (parent_id)
        REFERENCES comments (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Оценка статьи: от 1 до 5.
--
-- voter — отпечаток посетителя: хэш от адреса, браузера и случайной метки
-- в куке. Он же уникальный ключ вместе со статьёй, поэтому один посетитель
-- не может накрутить оценку простым обновлением страницы.
CREATE TABLE IF NOT EXISTS ratings (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    article_id INT UNSIGNED NOT NULL,
    value      TINYINT UNSIGNED NOT NULL,
    voter      CHAR(64)     NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY ratings_once (article_id, voter),
    CONSTRAINT ratings_article_fk FOREIGN KEY (article_id)
        REFERENCES articles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    login         VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name          VARCHAR(160) NOT NULL DEFAULT '',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME     DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY admins_login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Категории и теги из технического задания.
INSERT INTO categories (slug, name, title, description, position) VALUES
    ('bitrix24', 'Битрикс24',
     'Статьи о Битрикс24',
     'Разборы возможностей Битрикс24: сделки, задачи, воронки, автоматизация и отчёты.', 1),
    ('amocrm', 'AmoCRM',
     'Статьи об amoCRM',
     'Разборы возможностей amoCRM: воронки продаж, каналы связи, работа менеджеров и аналитика.', 2),
    ('crm-obshchaya', 'CRM общая',
     'Общие статьи о CRM',
     'Как выбрать и внедрить CRM, из чего она состоит и что даёт бизнесу — без привязки к системе.', 3)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO tags (slug, name, position) VALUES
    ('sdelki',          'Сделки',          1),
    ('zadachi',         'Задачи',          2),
    ('sotrudniki',      'Сотрудники',      3),
    ('administratory',  'Администраторы',  4),
    ('voronka-prodazh', 'Воронка продаж',  5),
    ('nedvizhimost',    'Недвижимость',    6)
ON DUPLICATE KEY UPDATE name = VALUES(name);
