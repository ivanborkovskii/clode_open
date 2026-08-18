<?php
/**
 * Общий макет страницы.
 *
 * @var App\Core\View $view
 * @var array $config
 * @var array $seo     title, description, canonical, noindex
 * @var string $content
 */

use App\Core\View;

$company = $config['company'];
$title   = ($seo['title'] ?? '') !== ''
    ? $seo['title'] . ' — ' . $company['brand']
    : $company['brand'];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= View::e($title) ?></title>
    <meta name="description" content="<?= View::e($seo['description'] ?? '') ?>">
    <?php if (!empty($seo['canonical'])): ?>
    <link rel="canonical" href="<?= View::e($seo['canonical']) ?>">
    <?php endif; ?>
    <?php if (!empty($seo['noindex'])): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>

    <meta property="og:type" content="<?= View::e($seo['og_type'] ?? 'website') ?>">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:title" content="<?= View::e($title) ?>">
    <meta property="og:description" content="<?= View::e($seo['description'] ?? '') ?>">
    <?php if (!empty($seo['canonical'])): ?>
    <meta property="og:url" content="<?= View::e($seo['canonical']) ?>">
    <?php endif; ?>
    <?php // Картинка для ссылки в мессенджерах и соцсетях — обложка статьи. ?>
    <?php if (!empty($seo['og_image'])): ?>
    <meta property="og:image" content="<?= View::e($seo['og_image']) ?>">
    <?php endif; ?>

    <meta name="theme-color" content="#071739">

    <!-- Шрифт грузится с этого же домена, поэтому предзагрузка даёт заметный выигрыш. -->
    <link rel="preload" href="/assets/fonts/manrope-cyrillic-wght-normal.woff2" as="font" type="font/woff2" crossorigin>

    <?php
    // Дизайн-система нужна на каждой странице, поверх неё подключаются
    // стили конкретного раздела. Страница передаёт их списком $styles —
    // так внутренние страницы не тянут разметку главной, и наоборот.
    ?>
    <link rel="stylesheet" href="<?= View::e($view->asset('css/app.css')) ?>">
    <?php foreach ($styles ?? [] as $stylesheet): ?>
    <link rel="stylesheet" href="<?= View::e($view->asset($stylesheet)) ?>">
    <?php endforeach; ?>

    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">

    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'ProfessionalService',
        'name'     => $company['brand'],
        'legalName' => $company['legal_name'],
        'description' => $seo['description'] ?? '',
        'url'      => $config['base_url'],
        'telephone' => $company['phone'],
        'email'    => $company['email'],
        'address'  => [
            '@type'           => 'PostalAddress',
            'addressLocality' => $company['locality'],
            'streetAddress'   => $company['address'],
            'addressCountry'  => 'RU',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>

    <?php
    // Хлебные крошки для поиска. Выводятся, только если страница их передала:
    // на главной цепочки нет.
    ?>
    <?php if (!empty($seo['breadcrumbs'])): ?>
    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => array_map(
            static fn (int $i, array $crumb): array => [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $crumb['label'],
                'item'     => $config['base_url'] . $crumb['href'],
            ],
            array_keys($seo['breadcrumbs']),
            $seo['breadcrumbs'],
        ),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
    <?php endif; ?>

    <?php
    // Дополнительная микроразметка страницы — например, разметка статьи.
    // Передаётся страницей списком, чтобы макет ничего не знал о разделах.
    ?>
    <?php foreach ($seo['jsonld'] ?? [] as $schema): ?>
    <script type="application/ld+json">
    <?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
    <?php endforeach; ?>
</head>
<body>
    <a class="skip-link" href="#main">Перейти к содержимому</a>

    <?php // Спрайт иконок: подключается один раз, дальше только <use href="#i-...">. ?>
    <?php $view->partial('partials/icons'); ?>

    <?php $view->partial('partials/header'); ?>

    <main id="main">
        <?= $content ?>
    </main>

    <?php $view->partial('partials/footer'); ?>

    <?php
    // Кнопка «Наверх». Это обычная ссылка на начало документа: она работает
    // и без JavaScript, а скрипт только показывает её после прокрутки —
    // на первом экране она не нужна и закрывала бы содержимое.
    //
    // Стоит в конце страницы намеренно: при переходе с клавиатуры она
    // оказывается последней, а не влезает в середину навигации.
    ?>
    <a class="to-top" href="#top" aria-label="Наверх" data-to-top>
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow-up"/></svg>
        <span>Наверх</span>
    </a>

    <?php // Без скрипта показать кнопку по прокрутке нечем — показываем всегда. ?>
    <noscript>
        <style>.to-top { opacity: 1; visibility: visible; transform: none; }</style>
    </noscript>

    <script src="<?= View::e($view->asset('js/main.js')) ?>" defer></script>

    <?php // Скрипты раздела: подключаются только там, где нужны. ?>
    <?php foreach ($scripts ?? [] as $script): ?>
    <script src="<?= View::e($view->asset($script)) ?>" defer></script>
    <?php endforeach; ?>
</body>
</html>
