<?php
/**
 * Принципы работы. Действуют на любой услуге, поэтому вынесены отдельным
 * блоком, а не повторяются в каждой строке перечня.
 *
 * Блок общий: им выводятся и принципы работы, и состав работ по услуге,
 * и её результат, и разбор смежных услуг. Отличается только содержимое.
 *
 * У карточки может быть ссылка — href и label. Нужна там, где карточка
 * отсылает к другой услуге: например, в разборе, какой из аудитов выбрать.
 * Выводится, только если та страница уже разработана, — как и везде
 * на сайте, чтобы ссылка не вела в «страница не найдена».
 *
 * Карточки идут в две колонки. Если их ровно три — например, три тарифа
 * или три вопроса, — ставится cols => 3: иначе третья остаётся одна
 * во втором ряду. Для этого в блоке задаётся cols.
 *
 * @var array $approach label, title, lead, items, cols
 */

use App\Core\View;

$cols = ($approach['cols'] ?? 2) === 3 ? ' approach-grid--three' : '';
?>
<section class="section section--alt approach-block">
    <div class="container">
        <div class="section-head">
            <p class="label"><?= View::e($approach['label'] ?? 'Подход') ?></p>
            <h2><?= View::e($approach['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($approach['lead']) ?></p>
        </div>

        <div class="approach-grid<?= $cols ?>">
            <?php foreach ($approach['items'] as $item): ?>
                <div class="approach-item">
                    <h3><?= View::e($item['title']) ?></h3>
                    <p><?= View::e($item['text']) ?></p>

                    <?php if (!empty($item['href']) && $view->exists($item['href'])): ?>
                        <a class="link-arrow" href="<?= View::e($item['href']) ?>">
                            <?= View::e($item['label']) ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
