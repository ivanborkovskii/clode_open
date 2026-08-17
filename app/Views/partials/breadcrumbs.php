<?php
/**
 * Хлебные крошки. Последний пункт — текущая страница, без ссылки.
 *
 * Разметка для поиска (BreadcrumbList) выводится в макете страницы:
 * там же, где остальная микроразметка.
 *
 * @var array $crumbs Список: label, href
 */

use App\Core\View;

if (empty($crumbs)) {
    return;
}

$last = array_key_last($crumbs);
?>
<nav class="crumbs" aria-label="Вы находитесь здесь">
    <ol class="crumbs__list">
        <?php foreach ($crumbs as $i => $crumb): ?>
            <li class="crumbs__item">
                <?php if ($i === $last): ?>
                    <span aria-current="page"><?= View::e($crumb['label']) ?></span>
                <?php else: ?>
                    <a href="<?= View::e($crumb['href']) ?>"><?= View::e($crumb['label']) ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
