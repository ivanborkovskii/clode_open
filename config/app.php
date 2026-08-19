<?php
/**
 * Основная конфигурация приложения.
 * Значения можно переопределить переменными окружения на сервере.
 */

declare(strict_types=1);

return [
    // Базовый URL без завершающего слэша. Используется в canonical, OG и sitemap.
    // Основной домен компании — ivanborkovsky.ru, этот сайт живёт на поддомене.
    'base_url' => rtrim(getenv('APP_URL') ?: 'https://crm.ivanborkovsky.ru', '/'),

    'env'   => getenv('APP_ENV') ?: 'production',
    // По умолчанию выключена: если про эту настройку забыть при выкладке,
    // посетитель не увидит текст ошибки с путями к файлам.
    // Для локальной разработки поставьте APP_DEBUG=true.
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOL),

    // Данные компании. Используются в шапке, подвале, контактах и микроразметке.
    'company' => [
        'name'       => 'Иван Борковский',
        'brand'      => 'crm.ivanborkovsky.ru',
        'site'       => 'ivanborkovsky.ru',
        'legal_name' => 'ИП Борковский Иван Даниялович',
        'ogrn'       => '323370000019203',
        'inn'        => '370204310532',
        'phone'      => '+7 (915) 179-68-61',
        'phone_href' => '+79151796861',
        // Короткий адрес на отдельном домене от Яндекса. Ведёт в тот же ящик,
        // что и borkovsky.iv@yandex.ru.
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
        // Тот же ящик, что и borkovsky.iv@yandex.ru — заявки и контакты
        // на сайте ведут в одно место.
        'mail_to'   => getenv('LEADS_MAIL_TO') ?: 'info@iborkovsky.ru',
        'mail_from' => getenv('LEADS_MAIL_FROM') ?: 'noreply@crm.ivanborkovsky.ru',
    ],

    // Подтверждение прав на сайт в Яндекс Вебмастере и Google Search Console.
    //
    // Нужно, только если выбран способ «мета-тег». Вписывается одно значение —
    // то, что стоит в content="...", без самого тега. Например, из
    // <meta name="yandex-verification" content="a1b2c3d4e5"> сюда идёт
    // a1b2c3d4e5. Пока пусто, никаких лишних тегов на страницах нет.
    //
    // Способ с файлом-подтверждением тоже работает: полученный от Яндекса
    // файл кладётся в public_html и открывается по своему адресу напрямую.
    'verification' => [
        'yandex' => getenv('VERIFY_YANDEX') ?: '',
        'google' => getenv('VERIFY_GOOGLE') ?: '',
    ],

    // Версия статики для сброса кэша браузера при обновлении CSS/JS.
    // ОБЯЗАТЕЛЬНО увеличивать при любой правке файлов в assets/css и assets/js:
    // они кэшируются на год, и без смены версии посетители продолжат
    // получать старую копию из кэша браузера.
    'assets_version' => '1.2.6',
];
