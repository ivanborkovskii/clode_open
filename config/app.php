<?php
/**
 * Основная конфигурация приложения.
 * Значения можно переопределить переменными окружения на сервере.
 */

declare(strict_types=1);

return [
    // Базовый URL без завершающего слэша. Используется в canonical, OG и sitemap.
    'base_url' => rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/'),

    'env'   => getenv('APP_ENV') ?: 'local',
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOL),

    // Данные компании. Используются в шапке, подвале, контактах и микроразметке.
    'company' => [
        'name'       => 'Иван Борковский',
        'brand'      => 'iborkovsky.ru',
        'legal_name' => 'ИП Борковский Иван Даниялович',
        'ogrn'       => '323370000019203',
        'inn'        => '370204310532',
        'phone'      => '+7 (915) 179-68-61',
        'phone_href' => '+79151796861',
        'email'      => 'info@iborkovsky.ru',
        'address'    => 'г. Иваново, микрорайон Московский, д. 19',
        'locality'   => 'Иваново',
    ],

    // Подключение к БД. Понадобится на этапе раздела «Статьи»,
    // на главной странице соединение не открывается.
    'db' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => (int) (getenv('DB_PORT') ?: 3306),
        'database' => getenv('DB_DATABASE') ?: 'iborkovsky',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset'  => 'utf8mb4',
    ],

    // Куда уходят заявки с форм.
    // Письмо отправляется всегда, копия дублируется в storage/logs/leads.log —
    // чтобы заявка не потерялась, если почтовый сервер откажет.
    'leads' => [
        'mail_to'   => getenv('LEADS_MAIL_TO') ?: 'borkovsky.iv@yandex.ru',
        'mail_from' => getenv('LEADS_MAIL_FROM') ?: 'noreply@iborkovsky.ru',
    ],

    // Версия статики для сброса кэша браузера при обновлении CSS/JS.
    'assets_version' => '1.0.0',
];
