<?php
/**
 * Оглавление статьи.
 *
 * Собрано из заголовков самого текста — см. Text::outline(). Здесь только
 * вывод: разделы верхнего уровня и вложенные в них подразделы.
 *
 * Свёрнуто по умолчанию и раскрывается по нажатию. Это обычный <details>,
 * а не скрипт: он работает и без JavaScript, а на телефоне развёрнутое
 * оглавление занимало бы весь первый экран статьи.
 *
 * @var array $toc   Разделы: id, text и вложенные children
 * @var array $texts Тексты раздела «Статьи»
 */

use App\Core\View;

// Считаем все пункты вместе с подразделами: в статье может быть один
// раздел с четырьмя подразделами — оглавление там нужно.
$count = count($toc);

foreach ($toc as $item) {
    $count += count($item['children']);
}

// Короткой статье оглавление не нужно: два пункта не помогают
// ориентироваться, а место занимают.
if ($count < 3) {
    return;
}
?>
<details class="toc">
    <summary class="toc__head">
        <?= View::e($texts['article']['contents']) ?>
        <svg class="toc__mark" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
            <use href="#i-arrow-up"/>
        </svg>
    </summary>

    <nav aria-label="<?= View::e($texts['article']['contents']) ?>">
        <ol class="toc__list">
            <?php foreach ($toc as $item): ?>
                <li class="toc__item">
                    <a class="toc__link" href="#<?= View::e($item['id']) ?>">
                        <?= View::e($item['text']) ?>
                    </a>

                    <?php if ($item['children'] !== []): ?>
                        <ol class="toc__list toc__list--inner">
                            <?php foreach ($item['children'] as $child): ?>
                                <li class="toc__item">
                                    <a class="toc__link" href="#<?= View::e($child['id']) ?>">
                                        <?= View::e($child['text']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
</details>
