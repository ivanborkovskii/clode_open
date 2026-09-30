<?php
/**
 * Что происходит после запуска — переход к смежным услугам.
 *
 * Ссылок может быть одна или несколько. Одна задаётся парой href и label,
 * несколько — списком links из таких же пар. Так сделано ради совместимости:
 * страницы, написанные раньше, указывают одну ссылку и переписывать их
 * не пришлось.
 *
 * Ссылка выводится, только когда та страница уже разработана: иначе она
 * вела бы в «страница не найдена».
 *
 * @var array $next title, text, href, label, links
 */

use App\Core\View;

$links = $next['links'] ?? [];

if ($links === [] && !empty($next['href'])) {
    $links = [['href' => $next['href'], 'label' => $next['label']]];
}

$links = array_values(array_filter(
    $links,
    static fn (array $link): bool => $view->exists($link['href']),
));
?>
<section class="section section--tight next-step">
    <div class="container">
        <div class="next-step__inner">
            <h2><?= View::e($next['title']) ?></h2>
            <p><?= View::e($next['text']) ?></p>

            <?php if ($links !== []): ?>
                <div class="next-step__links">
                    <?php foreach ($links as $link): ?>
                        <a class="link-arrow" href="<?= View::e($link['href']) ?>">
                            <?= View::e($link['label']) ?>
                            <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
