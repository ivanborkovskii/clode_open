<?php
/**
 * Ссылки на мессенджеры: значок и название.
 *
 * Один блок на весь сайт — подвал, блок «Можно связаться напрямую»
 * и страница «Контакты». Адреса берутся из настроек, а не из текстов
 * страниц: контакт не должен существовать в двух местах.
 *
 * Открываются в новой вкладке: это уход на другой сайт, страница
 * должна остаться на месте.
 *
 * @var App\Core\View $view
 * @var array  $config
 * @var string $title    Подпись над ссылками; пусто — без подписи
 * @var string $modifier Дополнительный класс блока
 */

use App\Core\View;

$messengers = $config['company']['messengers'] ?? [];
$title    ??= '';
$modifier ??= '';

if ($messengers === []) {
    return;
}
?>
<div class="messengers <?= View::e($modifier) ?>">
    <?php if ($title !== ''): ?>
        <p class="messengers__title"><?= View::e($title) ?></p>
    <?php endif; ?>

    <div class="messengers__list">
        <?php foreach ($messengers as $item): ?>
            <a class="messenger" href="<?= View::e($item['href']) ?>"
               target="_blank" rel="noopener"
               aria-label="Написать в <?= View::e($item['name']) ?>">
                <svg class="messenger__icon" width="20" height="20" viewBox="0 0 24 24"
                     aria-hidden="true"><use href="#<?= View::e($item['icon']) ?>"/></svg>
                <span><?= View::e($item['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
