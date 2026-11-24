<?php
/**
 * Ряд ссылок со значками: значок и название.
 *
 * Один блок на весь сайт — подвал, блок «Можно связаться напрямую»
 * и страница «Контакты». Адреса берутся из настроек, а не из текстов
 * страниц: контакт не должен существовать в двух местах.
 *
 * По умолчанию показывает мессенджеры. Страница «Контакты» передаёт
 * сюда же список соцсетей — разметка и вид у них те же, отличается
 * только подпись над рядом и то, что читает экранный диктор.
 *
 * Открываются в новой вкладке: это уход на другой сайт, страница
 * должна остаться на месте.
 *
 * @var App\Core\View $view
 * @var array  $config
 * @var array  $items    Список name/icon/href; пусто — мессенджеры из настроек
 * @var string $aria     Начало подписи для диктора: «<aria> Telegram»
 * @var string $title    Подпись над ссылками; пусто — без подписи
 * @var string $modifier Дополнительный класс блока
 */

use App\Core\View;

$items    ??= $config['company']['messengers'] ?? [];
$aria     ??= 'Написать в';
$title    ??= '';
$modifier ??= '';

if ($items === []) {
    return;
}
?>
<div class="messengers <?= View::e($modifier) ?>">
    <?php if ($title !== ''): ?>
        <p class="messengers__title"><?= View::e($title) ?></p>
    <?php endif; ?>

    <div class="messengers__list">
        <?php foreach ($items as $item): ?>
            <a class="messenger" href="<?= View::e($item['href']) ?>"
               target="_blank" rel="noopener"
               aria-label="<?= View::e($aria . ' ' . $item['name']) ?>">
                <svg class="messenger__icon" width="20" height="20" viewBox="0 0 24 24"
                     aria-hidden="true"><use href="#<?= View::e($item['icon']) ?>"/></svg>
                <span><?= View::e($item['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
