<?php
/**
 * Оглавление статьи.
 *
 * Собрано из заголовков самого текста — см. Text::outline(). Здесь только
 * вывод: разделы верхнего уровня и вложенные в них подразделы.
 *
 * Показывается открытым. Google собирает переходы к разделам страницы
 * («Что это такое · Как настроить · Сколько стоит» под ссылкой в выдаче)
 * сам, из заголовков с якорями и видимого списка ссылок на них. Спрятать
 * список под раскрывающийся блок можно — индексируется он всё равно
 * полностью, — но собирает такие переходы Google охотнее с того, что
 * видно сразу.
 *
 * Микроразметки у оглавления нет намеренно: способа объявить оглавление,
 * который поисковики читают, не существует. Разметка, которую никто
 * не использует, только засоряет страницу.
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
<nav class="toc" aria-label="<?= View::e($texts['article']['contents']) ?>">
    <p class="toc__head"><?= View::e($texts['article']['contents']) ?></p>

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
