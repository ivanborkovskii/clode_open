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

    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:title" content="<?= View::e($title) ?>">
    <meta property="og:description" content="<?= View::e($seo['description'] ?? '') ?>">
    <?php if (!empty($seo['canonical'])): ?>
    <meta property="og:url" content="<?= View::e($seo['canonical']) ?>">
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

    <script src="<?= View::e($view->asset('js/main.js')) ?>" defer></script>
</body>
</html>
