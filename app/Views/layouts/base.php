<?php
/**
 * Общий макет страницы.
 *
 * @var App\Core\View $view
 * @var array $config
 * @var array $seo     title, description, canonical, noindex
 * @var string $content
 */

use App\Core\Schema;
use App\Core\View;

$company = $config['company'];
$title   = ($seo['title'] ?? '') !== ''
    ? $seo['title'] . ' — ' . $company['brand']
    : $company['brand'];

$base = rtrim((string) $config['base_url'], '/');
$url  = (string) ($seo['canonical'] ?? $base . '/');

// Картинка ссылки. У статьи это её обложка, у остальных страниц — общая
// картинка сайта. Без неё ссылка в мессенджере выглядит голой строкой,
// поэтому она есть всегда.
$image = (string) ($seo['og_image'] ?? '');

if ($image === '') {
    $image  = $base . '/assets/img/share.png';
    $imageW = 1200;
    $imageH = 630;
    // У общей картинки своё описание: заголовок страницы описывал бы
    // не её, а страницу.
    $seo['og_image_alt'] ??= 'Внедрение Битрикс24 и amoCRM — ' . $company['brand'];
} else {
    $imageW = (int) ($seo['og_image_width'] ?? 0);
    $imageH = (int) ($seo['og_image_height'] ?? 0);
}

// Разметку страницы собираем здесь, чтобы в шаблоне остался только вывод.
$seo['og_image'] = $image;
?>
<!doctype html>
<?php
// prefix объявляет словари Open Graph. Сам по себе og: валидаторы знают,
// а вот article: (даты, раздел и темы статьи) — нет: Яндекс.Вебмастер
// на него ругается «префикс неизвестен». Объявляем оба явно, тогда
// разметку разбирают и Яндекс, и остальные.
?>
<html lang="ru" prefix="og: https://ogp.me/ns# article: https://ogp.me/ns/article#">
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

    <?php
    // Open Graph: как ссылка на страницу выглядит в мессенджерах, соцсетях
    // и мессенджер-виджетах. Набор одинаковый на всех страницах сайта,
    // различается только содержимое.
    ?>
    <meta property="og:type" content="<?= View::e($seo['og_type'] ?? 'website') ?>">
    <meta property="og:site_name" content="<?= View::e($company['brand']) ?>">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:title" content="<?= View::e($title) ?>">
    <meta property="og:description" content="<?= View::e($seo['description'] ?? '') ?>">
    <meta property="og:url" content="<?= View::e($url) ?>">
    <meta property="og:image" content="<?= View::e($image) ?>">
    <?php if ($imageW > 0): ?>
    <meta property="og:image:width" content="<?= $imageW ?>">
    <meta property="og:image:height" content="<?= $imageH ?>">
    <?php endif; ?>
    <meta property="og:image:alt" content="<?= View::e($seo['og_image_alt'] ?? $title) ?>">

    <?php // Даты и раздел — только у статьи: у остальных страниц их нет. ?>
    <?php if (!empty($seo['article'])): ?>
    <meta property="article:published_time" content="<?= View::e($seo['article']['published']) ?>">
    <meta property="article:modified_time" content="<?= View::e($seo['article']['modified']) ?>">
    <meta property="article:section" content="<?= View::e($seo['article']['section']) ?>">
    <?php foreach ($seo['article']['tags'] ?? [] as $tag): ?>
    <meta property="article:tag" content="<?= View::e($tag) ?>">
    <?php endforeach; ?>
    <?php endif; ?>

    <?php
    // Twitter читает свои теги, но при их отсутствии берёт Open Graph.
    // Указываем только то, чего в Open Graph нет: вид карточки.
    ?>
    <meta name="twitter:card" content="summary_large_image">

    <meta name="theme-color" content="#071739">

    <?php // Подтверждение прав в Вебмастере и Search Console — если оно
          // сделано мета-тегом. Значения задаются в config/app.php. ?>
    <?php if (!empty($config['verification']['yandex'])): ?>
    <meta name="yandex-verification" content="<?= View::e($config['verification']['yandex']) ?>">
    <?php endif; ?>
    <?php if (!empty($config['verification']['google'])): ?>
    <meta name="google-site-verification" content="<?= View::e($config['verification']['google']) ?>">
    <?php endif; ?>

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

    <?php
    // Микроразметка страницы одним графом: компания, сайт, сама страница,
    // хлебные крошки и то, что добавила страница — статья, услуга, список.
    // Узлы связаны через @id, поэтому сведения о компании не повторяются.
    ?>
    <script type="application/ld+json">
    <?= json_encode(
        Schema::graph($config, $seo),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) ?>
    </script>

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

    <?php
    // Счётчики посещаемости — в самом конце страницы, после всего
    // остального: так они не задерживают показ содержимого.
    // Что именно туда вставлено, макет не знает и знать не должен.
    ?>
    <?php $view->partial('partials/counters'); ?>
</body>
</html>
