<?php
/**
 * Макет админки.
 *
 * Отдельный от сайта: здесь не нужны ни шапка с меню, ни микроразметка,
 * ни шрифт для посетителей. Зато нужно, чтобы страница не попала в поиск, —
 * это отдельная строка в head, а не расчёт на закрытость адреса.
 *
 * @var App\Core\View $view
 * @var string $content
 * @var array  $counts
 * @var bool   $auth
 */

use App\Core\View;
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Статьи — управление</title>

    <link rel="stylesheet" href="<?= View::e($view->asset('css/admin.css')) ?>">
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
</head>
<body>
    <?php if ($auth ?? false): ?>
        <header class="abar">
            <a class="abar__logo" href="/admin">Статьи</a>

            <nav class="abar__nav">
                <a href="/admin">Список</a>
                <a href="/admin/statya">Новая статья</a>
                <a href="/admin/temy">Темы</a>
                <a href="/admin/kommentarii">
                    Комментарии
                    <?php if (($counts['comments'] ?? 0) > 0): ?>
                        <span class="abar__badge"><?= (int) $counts['comments'] ?></span>
                    <?php endif; ?>
                </a>
            </nav>

            <div class="abar__side">
                <a href="/stati" target="_blank" rel="noopener">Раздел на сайте</a>
                <a href="/admin/parol">Пароль</a>
                <a href="/admin/vyhod">Выйти</a>
            </div>
        </header>
    <?php endif; ?>

    <main class="awrap">
        <?= $content ?>
    </main>

    <script src="<?= View::e($view->asset('js/admin.js')) ?>" defer></script>
</body>
</html>
